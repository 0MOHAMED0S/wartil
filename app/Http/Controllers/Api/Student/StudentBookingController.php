<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SlotBooking;
use App\Models\CallSession;
use App\Services\AgoraService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\TeacherReport;

class StudentBookingController extends Controller
{
    protected $agoraService;

    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }
    public function getStudentBookings(Request $request)
    {
        try {
            $user = auth()->user();
            $now = Carbon::now();

            $allBookings = SlotBooking::with(['slot.teacher.user'])
                ->where('user_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->get();

            $processedBookings = $allBookings->map(function ($booking) use ($user, $now) {
                $slot = $booking->slot;
                if (!$slot) return null;

                $teacher = $slot->teacher;
                $slotStartDateTime = Carbon::parse($slot->date . ' ' . $slot->start_time);
                $slotEndDateTime = Carbon::parse($slot->date . ' ' . $slot->end_time);

                $callSession = CallSession::where('student_id', $user->id)
                    ->where('teacher_id', $slot->teacher_id)
                    ->where('started_at', $slotStartDateTime->toDateTimeString())
                    ->first();

                $isPast = $slotEndDateTime->isPast() || $booking->status === 'completed' || optional($callSession)->status === 'ended';

                return [
                    'booking_id'     => $booking->id,
                    'status'         => $booking->status,
                    'slot_details'   => [
                        'id'         => $slot->id,
                        'date'       => $slot->date,
                        'start_time' => $slot->start_time,
                        'end_time'   => $slot->end_time,
                    ],
                    'teacher' => [
                        'name'  => optional($teacher->user)->name ?? 'معلم ورتل',
                        'photo' => $teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : null,
                    ],
                    'session_record' => [
                        'recording_url' => optional($callSession)->recording_url,
                        'is_available'  => !empty(optional($callSession)->recording_url),
                        'is_reported'   => TeacherReport::where('reportable_id', $booking->id)->exists(),
                    ],
                    'is_past' => $isPast
                ];
            })->filter()->values();

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع سجل الحجوزات بنجاح.',
                'data'    => [
                    'upcoming' => $processedBookings->where('is_past', false)->values(),
                    'history'  => $processedBookings->where('is_past', true)->values(),
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Bookings Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ في الخادم.'], 500);
        }
    }
    public function getUpcomingBookings(Request $request)
    {
        try {
            $user = auth()->user();
            $now = Carbon::now();
            $perPage = $request->get('per_page', 10);

            $bookingsPaginator = SlotBooking::with(['slot.teacher.user'])
                ->join('teacher_slots', 'slot_bookings.teacher_slot_id', '=', 'teacher_slots.id')
                ->where('slot_bookings.user_id', $user->id)
                ->where('slot_bookings.status', 'scheduled')
                ->where(function ($query) use ($now) {
                    $query->where('teacher_slots.date', '>', $now->toDateString())
                        ->orWhere(function ($q) use ($now) {
                            $q->where('teacher_slots.date', $now->toDateString())
                                ->where('teacher_slots.end_time', '>', $now->toTimeString());
                        });
                })
                ->orderBy('teacher_slots.date', 'asc')
                ->orderBy('teacher_slots.start_time', 'asc')
                ->select('slot_bookings.*')
                ->paginate($perPage);

            $bookingsPaginator->getCollection()->transform(function ($booking) use ($user, $now) {
                $slot = $booking->slot;
                $teacher = $slot->teacher;
                $slotStart = Carbon::parse($slot->date . ' ' . $slot->start_time);
                $slotEnd = Carbon::parse($slot->date . ' ' . $slot->end_time);

                $callSession = CallSession::where('student_id', $user->id)
                    ->where('teacher_id', $teacher->id)
                    ->where('started_at', $slotStart->toDateTimeString())
                    ->first();

                $canJoin = $now->copy()->addMinutes(5)->greaterThanOrEqualTo($slotStart)
                    && $now->lessThanOrEqualTo($slotEnd);

                return [
                    'booking_id'    => $booking->id,
                    'slot_id'       => $slot->id,
                    'date'          => $slot->date,
                    'start_time'    => $slot->start_time,
                    'end_time'      => $slot->end_time,
                    'teacher_name'  => optional($teacher->user)->name ?? 'معلم ورتل',
                    'teacher_photo' => $teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : null,
                    'call_id'       => optional($callSession)->id,
                    'channel_name'  => optional($callSession)->channel_name,
                    'can_join'      => $canJoin,
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع الحجوزات القادمة بنجاح.',
                'data'    => $bookingsPaginator
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Upcoming Bookings Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء جلب البيانات.'], 500);
        }
    }
    public function getBookingsHistory(Request $request)
    {
        try {
            $user = auth()->user();
            $perPage = $request->get('per_page', 10);

            $history = SlotBooking::with(['slot.teacher.user'])
                ->join('teacher_slots', 'slot_bookings.teacher_slot_id', '=', 'teacher_slots.id')
                ->where('slot_bookings.user_id', $user->id)
                ->where(function ($query) {
                    $query->where('slot_bookings.status', 'completed')
                        ->orWhere('teacher_slots.date', '<', now()->toDateString());
                })
                ->orderBy('teacher_slots.date', 'desc')
                ->orderBy('teacher_slots.start_time', 'desc')
                ->select('slot_bookings.*')
                ->paginate($perPage);

            $history->getCollection()->transform(function ($booking) use ($user) {
                $slot = $booking->slot;
                $teacher = $slot->teacher;
                $slotStart = Carbon::parse($slot->date . ' ' . $slot->start_time);

                $callSession = CallSession::where('student_id', $user->id)
                    ->where('teacher_id', $teacher->id)
                    ->where('started_at', $slotStart->toDateTimeString())
                    ->first();

                return [
                    'booking_id'    => $booking->id,
                    'slot_id'       => $slot->id,
                    'date'          => $slot->date,
                    'time'          => $slot->start_time . ' - ' . $slot->end_time,
                    'teacher_name'  => optional($teacher->user)->name ?? 'معلم ورتل',
                    'status'        => $booking->status,
                    'recording_url' => optional($callSession)->recording_url, // 🚀 رابط التسجيل
                    'is_reported' => TeacherReport::where('reportable_type', SlotBooking::class)
    ->where(function ($q) use ($booking, $slot) {
        $q->where('reportable_id', $slot->id)
          ->orWhere('reportable_id', $booking->id);
    })->exists(),
                    'duration'      => optional($callSession)->duration_minutes,
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع سجل الحجوزات بنجاح.',
                'data'    => $history
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Bookings History Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء جلب السجل.'], 500);
        }
    }

    public function joinBookedSession(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'call_session_id' => 'required|exists:slot_bookings,id'
        ], [
            'call_session_id.required' => 'معرف الجلسة مطلوب.',
            'call_session_id.exists'   => 'عذراً، هذه الجلسة غير موجودة في سجلاتنا.'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = auth()->user();

        try {
            $booking = SlotBooking::with('slot.teacher.user')->findOrFail($request->call_session_id);
            $slot = $booking->slot;

            if ($booking->user_id !== $user->id) {
                return response()->json(['status' => false, 'message' => 'غير مصرح لك بالدخول إلى هذه الجلسة.'], 403);
            }

            if (in_array($booking->status, ['completed', 'cancelled', 'missed'])) {
                return response()->json(['status' => false, 'message' => 'انتهت هذه الجلسة بالفعل ولا يمكن الانضمام إليها.'], 400);
            }

            $now = Carbon::now();
            $startTime = Carbon::parse($slot->date . ' ' . $slot->start_time);

            if ($now->lessThan($startTime->copy()->subMinutes(5))) {
                $waitMinutes = $now->diffInMinutes($startTime);
                return response()->json([
                    'status'  => false,
                    'message' => "موعد الحصة لم يحن بعد. يرجى الانتظار، سيبدأ الرابط بالعمل خلال " . ($waitMinutes - 5) . " دقيقة.",
                    'data'    => [
                        'start_time' => $startTime->format('h:i A'),
                        'wait_minutes' => $waitMinutes - 5
                    ]
                ], 425);
            }
            if (is_null($booking->student_joined_at)) {
                $booking->update(['student_joined_at' => $now]);
            }

            if ($booking->status === 'scheduled') {
                $booking->update(['status' => 'ongoing']);
            }
            $token = $this->agoraService->generateToken($booking->channel_name, $user->id, 'publisher');
            $this->notifyTeacherOfJoin($booking, $user->name);
            return response()->json([
                'status'  => true,
                'message' => 'تم الاتصال بالخادم، جاري دخول الغرفة..',
                'data'    => [
                    'call_session_id' => $booking->id,
                    'channel_name'    => $booking->channel_name,
                    'agora_token'     => $token,
                    'uid'             => (int) $user->id,
                    'teacher_name'    => optional(optional($slot->teacher)->user)->name
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Professional Join Session Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ تقني أثناء محاولة الانضمام، يرجى المحاولة مرة أخرى.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function notifyTeacherOfJoin($booking, $studentName)
    {
        try {
            $teacherUser = optional($booking->slot->teacher)->user;
            if ($teacherUser) {
                $notificationData = [
                    'call_session_id' => $booking->id,
                    'channel_name'    => $booking->channel_name,
                    'student_name'    => $studentName,
                ];

                broadcast(new \App\Events\StudentJoinedSession($booking->slot->teacher_id, $notificationData));

                $teacherUser->notify(new \App\Notifications\DynamicNotification(
                    'الطالب في انتظارك ⏳',
                    "انضم الطالب {$studentName} الآن إلى الحصة المجدولة، يرجى البدء.",
                    'student_joined',
                    $notificationData
                ));
            }
        } catch (\Exception $e) {
            Log::warning('Silent Notification Error: ' . $e->getMessage());
        }
    }

    public function leaveBookedSession(Request $request)
    {
        $request->validate([
            'call_session_id' => 'required|exists:slot_bookings,id'
        ]);

        $user = auth()->user();

        try {
            $booking = SlotBooking::with('slot.teacher.user')->findOrFail($request->call_session_id);

            if ($booking->user_id !== $user->id) {
                return response()->json(['status' => false, 'message' => 'غير مصرح لك.'], 403);
            }
            if (in_array($booking->status, ['completed', 'cancelled', 'missed'])) {
                return response()->json(['status' => true, 'message' => 'الجلسة منتهية بالفعل.']);
            }

            try {
                $teacherUser = optional($booking->slot->teacher)->user;
                if ($teacherUser) {
                    $notificationData = [
                        'call_session_id' => $booking->id,
                        'student_name'    => $user->name,
                        'event'           => 'student_left'
                    ];
                    broadcast(new \App\Events\StudentLeftSession($booking->slot->teacher_id, $notificationData));
                }
            } catch (\Exception $e) {
                Log::error('Teacher Leave Notification Error: ' . $e->getMessage());
            }

            return response()->json([
                'status'  => true,
                'message' => 'تمت المغادرة بنجاح. يمكنك العودة للجلسة طالما أنها مستمرة.',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Leave Booked Session Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء محاولة المغادرة.'], 500);
        }
    }
    /**
     * Get report details for a specific booking by slot ID.
     */
    public function getByBooking(Request $request, $slotId)
    {
        try {
            $user = $request->user();

            // Find the booking that belongs to the student and matches the slot ID
            $booking = SlotBooking::with(['slot.teacher.user', 'reports'])
                ->where('slot_id', $slotId)
                ->where('user_id', $user->id)
                ->first();

            if (! $booking) {
                return response()->json([
                    'status' => false,
                    'message' => 'الحجز غير موجود أو لا يخص الطالب.',
                ], 404);
            }

            // Fetch the associated report (if any) using the booking's ID
            $report = $booking->reports()
                ->where('reportable_type', SlotBooking::class)
                ->where('reportable_id', $booking->id)
                ->first();

            if (! $report) {
                return response()->json([
                    'status' => true,
                    'data' => [
                        'booking_id'   => $booking->id,
                        'slot_id'      => $slotId,
                        'is_reported' => false,
                        'report'       => null,
                    ],
                ], 200);
            }

            // Build detailed report response (mirroring StudentReportController format)
            $reportData = [
                'id'               => $report->id,
                'teacher_name'     => optional($report->teacher->user)->name ?? 'Unknown',
                'teacher_id'       => $report->teacher_id,
                'attendance_status'=> $report->attendance_status,
                'reception_level'  => $report->reception_level,
                'notes'            => $report->notes,
                'session_content'  => $report->session_content,
                'report'           => $report->report,
                'reportable_type'  => $report->reportable_type,
                'reportable_id'    => $report->reportable_id,
                'created_at'       => $report->created_at ? $report->created_at->format('Y-m-d H:i:s') : null,
                'is_reported'      => true,
            ];

            return response()->json([
                'status' => true,
                'data'   => $reportData,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Booking Report Retrieval Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء جلب تقرير الحجز.',
            ], 500);
        }
    }
    }

