<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\TeacherSlot;
use App\Models\CallSession;
use App\Models\SlotBooking;
use App\Services\AgoraService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherBookingController extends Controller
{
    protected $agoraService;
    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }
    public function getSoonestBooking()
    {
        try {
            $user = auth()->user();
            $teacher = $user->teacher;

            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'ملف المعلم غير موجود.'], 404);
            }

            $now = Carbon::now();

            $soonestSlot = TeacherSlot::with(['booking.user'])
                ->where('teacher_id', $teacher->id)
                ->where('is_booked', true)
                // المواعيد التي لم تنتهِ بعد (تاريخ اليوم وقته أو تاريخ مستقبلي)
                ->where(function ($query) use ($now) {
                    $query->where('date', '>', $now->toDateString())
                        ->orWhere(function ($q) use ($now) {
                            $q->where('date', $now->toDateString())
                                ->where('end_time', '>', $now->toTimeString());
                        });
                })
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->first();

            if (!$soonestSlot) {
                return response()->json([
                    'status' => true,
                    'message' => 'لا توجد مواعيد محجوزة قريباً.',
                    'data' => null
                ]);
            }

            return response()->json([
                'status' => true,
                'data' => $this->formatSlotData($soonestSlot, $teacher, $now)
            ]);
        } catch (\Throwable $e) {
            Log::error('Soonest Booking Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ في جلب أقرب موعد.'], 500);
        }
    }
    public function getTeacherBookings(Request $request)
    {
        try {
            $user = auth()->user();
            $teacher = $user->teacher;

            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'حساب المعلم غير موجود.'], 404);
            }

            $now = Carbon::now();

            // 1️⃣ جلب كافة الحجوزات (غير الملغاة) المرتبطة بهذا المعلم
            // استخدمنا get() لتمكين التقسيم اليدوي، إذا كانت البيانات ضخمة جداً يفضل استخدام الـ pagination لكل قسم على حدة
            $allSlots = TeacherSlot::with(['booking.user'])
                ->where('teacher_id', $teacher->id)
                ->where('is_booked', true)
                ->whereHas('booking', function ($query) {
                    $query->where('status', '!=', 'cancelled');
                })
                ->orderBy('date', 'desc')
                ->orderBy('start_time', 'desc')
                ->get();

            // 2️⃣ معالجة البيانات وتنسيقها
            $processedSlots = $allSlots->map(function ($slot) use ($teacher, $now) {
                $booking = $slot->booking;
                $student = optional($booking)->user;

                if (!$booking || !$student) return null;

                $slotStartDateTime = Carbon::parse($slot->date . ' ' . $slot->start_time);
                $slotEndDateTime = Carbon::parse($slot->date . ' ' . $slot->end_time);

                // جلب جلسة المكالمة المرتبطة بالمعد
                $callSession = CallSession::where('teacher_id', $teacher->id)
                    ->where('student_id', $student->id)
                    ->where('started_at', $slotStartDateTime->toDateTimeString())
                    ->first();

                // المنطق الزمني: هل الجلسة انتهت؟
                $isPast = $slotEndDateTime->isPast() || optional($callSession)->status === 'ended' || $booking->status === 'completed';

                // هل يمكن للمعلم بدء الجلسة الآن؟ (5 دقائق قبل الموعد)
                $canStart = !$isPast && $now->copy()->addMinutes(5)->greaterThanOrEqualTo($slotStartDateTime)
                    && $now->lessThanOrEqualTo($slotEndDateTime);

                return [
                    'booking_id'      => $booking->id,
                    'slot_id'         => $slot->id,
                    'date'            => $slot->date,
                    'start_time'      => $slot->start_time,
                    'end_time'        => $slot->end_time,
                    'status'          => $booking->status,
                    'is_past'         => $isPast,
                    'student' => [
                        'id'    => $student->id,
                        'name'  => $student->name,
                        // جلب صورة الطالب إذا كانت مخزنة في بروفايل الطالب
                        'photo' => optional($student->studentProfile)->profile_photo_path ? asset('storage/' . $student->studentProfile->profile_photo_path) : null,
                    ],
                    'call_details' => [
                        'id'           => optional($callSession)->id,
                        'channel_name' => optional($callSession)->channel_name,
                        'status'       => optional($callSession)->status ?? 'not_started',
                        'can_start'    => $canStart,
                    ]
                ];
            })->filter()->values();

            // 3️⃣ تقسيم البيانات إلى "القادم" و "السابق"
            // Upcoming: المواعيد التي لم تنتهِ بعد
            $upcoming = $processedSlots->where('is_past', false)->values();

            // History: المواعيد التي انتهت أو اكتملت
            $history = $processedSlots->where('is_past', true)->values();

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع حجوزات المعلم بنجاح.',
                'data'    => [
                    'upcoming' => $upcoming,
                    'history'  => $history
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Teacher Bookings Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ تقني: ' . $e->getMessage()
            ], 500);
        }
    }
    private function formatSlotData($slot, $teacher, $now)
    {
        $student = optional($slot->booking)->user;
        $slotStartDateTime = Carbon::parse($slot->date . ' ' . $slot->start_time);
        $slotEndDateTime = Carbon::parse($slot->date . ' ' . $slot->end_time);

        // جلب جلسة المكالمة مع التقييمات المرتبطة بها (Polymorphic Relation)
        $callSession = CallSession::where('teacher_id', $teacher->id)
            ->where('student_id', optional($student)->id)
            ->where('started_at', $slotStartDateTime)
            ->with('ratings')
            ->first();

        $rating = optional($callSession)->ratings ? $callSession->ratings->first() : null;

        // حالة السماح ببدء المكالمة
        $canStart = $now->copy()->addMinutes(5)->greaterThanOrEqualTo($slotStartDateTime)
            && $now->lessThanOrEqualTo($slotEndDateTime)
            && optional($callSession)->status !== 'ended';

        return [
            'slot_id'         => $slot->id,
            'date'            => $slot->date,
            'start_time'      => $slot->start_time,
            'end_time'        => $slot->end_time,
            'booking_status'  => optional($slot->booking)->status, // scheduled, completed, cancelled
            'student' => [
                'id'    => optional($student)->id,
                'name'  => optional($student)->name,
                'photo' => optional($student)->profile_photo_url, // إذا وجد
            ],
            'call_session' => [
                'id'             => optional($callSession)->id,
                'status'         => optional($callSession)->status ?? 'not_started',
                'channel_name'   => optional($callSession)->channel_name,
                'can_start_now'  => $canStart,
                'duration'       => optional($callSession)->duration_minutes,
            ],
            // إرجاع التقييم إذا كانت الجلسة منتهية ويوجد تقييم
            'rating' => $rating ? [
                'stars'   => $rating->rating,
                'comment' => $rating->comment,
                'date'    => $rating->created_at->format('Y-m-d')
            ] : null
        ];
    }

public function startBookedSession(Request $request)
    {
        $request->validate(['call_session_id' => 'required|exists:slot_bookings,id']);
        $user = auth()->user();

        try {
            $booking = \App\Models\SlotBooking::with('slot.teacher')->findOrFail($request->call_session_id);
            $slot = $booking->slot;

            if ($slot->teacher->user_id !== $user->id) {
                return response()->json(['status' => false, 'message' => 'غير مصرح لك.'], 403);
            }

            if (in_array($booking->status, ['completed', 'cancelled', 'missed'])) {
                return response()->json(['status' => false, 'message' => 'هذه المكالمة انتهت بالفعل.'], 400);
            }

            $startTime = \Carbon\Carbon::parse($slot->date . ' ' . $slot->start_time);
            if (\Carbon\Carbon::now()->addMinutes(5)->lessThan($startTime)) {
                return response()->json(['status' => false, 'message' => 'لا يمكنك بدء الجلسة الآن، انتظر حتى يحين الموعد.'], 400);
            }

            $token = $this->agoraService->generateToken($booking->channel_name, $user->id, 'publisher');
            $resourceId = null;
            $sid = null;

            if (empty($booking->agora_sid)) {
                $recorderUid = 999999;
                $recorderToken = $this->agoraService->generateToken(
                    $booking->channel_name,
                    $recorderUid,
                    'publisher'
                );
                $resourceId = $this->agoraService->acquire($booking->channel_name, $recorderUid);
                if ($resourceId) {
                    $sid = $this->agoraService->start($resourceId, $booking->channel_name, $recorderToken, $recorderUid);
                    if (!$sid) {
                        \Illuminate\Support\Facades\Log::error("Agora Recording Start Failed for Scheduled Call ID: {$booking->id}");
                    }
                } else {
                    \Illuminate\Support\Facades\Log::error("Agora Recording Acquire Failed for Scheduled Call ID: {$booking->id}");
                }
            }

            $updateData = [
                'status' => 'ongoing',
                'agora_resource_id' => $resourceId ?? $booking->agora_resource_id,
                'agora_sid' => $sid ?? $booking->agora_sid,
            ];

            // تسجيل دخول المعلم
            if (is_null($booking->teacher_joined_at)) {
                $updateData['teacher_joined_at'] = \Carbon\Carbon::now();
            }

            $booking->update($updateData);

            try {
                $student = \App\Models\User::find($booking->user_id);
                if ($student) {
                    $notificationData = [
                        'call_session_id' => $booking->id,
                        'channel_name'    => $booking->channel_name,
                        'teacher_name'    => $user->name,
                    ];

                    broadcast(new \App\Events\TeacherStartedSession($student->id, $notificationData));

                    $student->notify(new \App\Notifications\DynamicNotification(
                        'المعلم بانتظارك 🎥',
                        "المعلم {$user->name} بدأ الجلسة المجدولة وهو بانتظار انضمامك الآن.",
                        'teacher_started_session',
                        $notificationData
                    ));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Student Notify Error (Start Session): ' . $e->getMessage());
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'token' => $token,
                    'channel' => $booking->channel_name,
                    'uid' => $user->id,
                    'is_recording' => !empty($sid)
                ]
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Start Booked Session Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'خطأ في بدء الجلسة.'], 500);
        }
    }
public function endBookedSession(Request $request)
    {
        $request->validate(['call_session_id' => 'required|exists:slot_bookings,id'], [
            'call_session_id.required' => 'معرف الجلسة مطلوب.',
            'call_session_id.exists'   => 'الجلسة غير موجودة.'
        ]);

        $user = auth()->user();

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $booking = \App\Models\SlotBooking::with('slot.teacher')->lockForUpdate()->findOrFail($request->call_session_id);
            $slot = $booking->slot;

            if ($slot->teacher->user_id !== $user->id) {
                return response()->json(['status' => false, 'message' => 'غير مصرح لك بإنهاء هذه الجلسة.'], 403);
            }

            if ($booking->status === 'completed') {
                return response()->json(['status' => true, 'message' => 'هذه الجلسة منتهية مسبقاً.']);
            }

            $now = \Carbon\Carbon::now();
            $startTime = \Carbon\Carbon::parse($booking->started_at ?? $slot->date . ' ' . $slot->start_time);
            $actualDuration = (int) ceil($startTime->diffInMinutes($now));

            $recordingUrl = $booking->recording_url;

            if (!empty($booking->agora_sid) && !empty($booking->agora_resource_id)) {
                try {
                    $recorderUid = 999999;
                    $this->agoraService->stop(
                        $booking->agora_resource_id,
                        $booking->agora_sid,
                        $booking->channel_name,
                        $recorderUid
                    );

                    $expectedFileName = "records/sessions/{$booking->agora_sid}_{$booking->channel_name}.m3u8";
                    $publicUrl = env('CLOUDFLARE_R2_PUBLIC_URL');

                    if (!empty($publicUrl)) {
                        $recordingUrl = rtrim($publicUrl, '/') . '/' . $expectedFileName;
                    } else {
                        $endpoint = env('AGORA_STORAGE_ENDPOINT');
                        $bucket   = env('AGORA_STORAGE_BUCKET');
                        $cleanEndpoint = preg_replace('#^https?://#', '', $endpoint);
                        $recordingUrl = "https://{$cleanEndpoint}/{$bucket}/{$expectedFileName}";
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Agora Stop Recording Error: " . $e->getMessage());
                }
            }

            $studentRegion = null;
            $teacherEarnings = 0;

            $slot->teacher->increment('minutes', $booking->deducted_minutes);
            $student = \App\Models\Student::where('user_id', $booking->user_id)->first();
            if ($student) {
                $earningsData = app(\App\Services\PricingEngineService::class)->processSessionEarnings(
                    $slot->teacher,
                    $student,
                    $booking->deducted_minutes
                );
                $studentRegion = $earningsData['region'];
                $teacherEarnings = $earningsData['earnings'];
            }
            \Illuminate\Support\Facades\Log::info("Session ended: {$booking->deducted_minutes} minutes added to teacher {$slot->teacher_id}");

            $booking->update([
                'ended_at' => $now,
                'actual_duration' => $actualDuration,
                'status' => 'completed',
                'recording_url' => $recordingUrl,
                'student_region' => $studentRegion,
                'teacher_earnings' => $teacherEarnings,
            ]);

            \Illuminate\Support\Facades\DB::commit();

            $this->notifyStudentSessionEnded($booking, $user->name, $actualDuration);

            return response()->json([
                'status' => true,
                'message' => 'تم إنهاء الجلسة بنجاح وتوثيق أرباحك.',
                'data' => [
                    'duration' => $actualDuration,
                    'recording_url' => $recordingUrl
                ]
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('End Session Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ تقني أثناء إنهاء الجلسة.'], 500);
        }
    }

    private function notifyStudentSessionEnded($booking, $teacherName, $duration)
    {
        try {
            $student = \App\Models\User::find($booking->user_id);
            if ($student) {
                $data = [
                    'call_session_id' => $booking->id,
                    'duration' => $duration,
                    'teacher_name' => $teacherName,
                ];
                broadcast(new \App\Events\TeacherEndedSession($student->id, $data));
                $student->notify(new \App\Notifications\DynamicNotification(
                    'انتهت الحصة القرآنية ✅',
                    "أنهى المعلم {$teacherName} الجلسة. نرجو أن تكون قد استفدت، يمكنك تقييم المعلم الآن.",
                    'session_ended',
                    $data
                ));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notification Error: ' . $e->getMessage());
        }
    }
}
