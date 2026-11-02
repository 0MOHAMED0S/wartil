<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\RecitationSession;
use App\Models\Session_student;
use App\Services\AgoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TeacherReport;

class TeacherSessionController extends Controller
{
    protected $agoraService;
    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }
    public function startSession(Request $request, $sessionId)
    {
        try {
            $userId = auth()->id();

            $session = RecitationSession::with('teacher')->findOrFail($sessionId);

            if ($userId !== $session->teacher->user_id) {
                return response()->json([
                    'status'  => false,
                    'message' => 'غير مصرح لك ببدء هذه الحصة.'
                ], 403);
            }

            if ($session->status === 'ended') {
                return response()->json([
                    'status'  => false,
                    'message' => 'هذه الحصة منتهية بالفعل.'
                ], 400);
            }

            // 1. توليد توكن للمعلم لكي يدخل الغرفة
            $token = $this->agoraService->generateToken(
                $session->channel_name,
                $userId,
                'host'
            );

            $resourceId = null;
            $sid = null;

            if ($session->is_recorded && empty($session->agora_sid)) {

                $recorderUid = 999999;
                $recorderToken = $this->agoraService->generateToken(
                    $session->channel_name,
                    $recorderUid,
                    'publisher'
                );

                $resourceId = $this->agoraService->acquire($session->channel_name, $recorderUid);

                if ($resourceId) {
                    $sid = $this->agoraService->start($resourceId, $session->channel_name, $recorderToken, $recorderUid);

                    if (!$sid) {
                        \Illuminate\Support\Facades\Log::error("Agora Recording Start Failed for Session ID: {$sessionId}");
                    }
                } else {
                    \Illuminate\Support\Facades\Log::error("Agora Recording Acquire Failed for Session ID: {$sessionId}");
                }
            }

            $session->update([
                'status'            => 'live',
                'actual_started_at' => now(),
                'agora_resource_id' => $resourceId ?? $session->agora_resource_id,
                'agora_sid'         => $sid ?? $session->agora_sid,
            ]);
            return response()->json([
                'status'  => true,
                'message' => 'تم بدء الحصة بنجاح.',
                'data'    => [
                    'agora_token'  => $token,
                    'channel_name' => $session->channel_name,
                    'app_id'       => config('services.agora.app_id'),
                    'uid'          => $userId,
                    'role'         => 'host',
                    'is_recording' => !empty($sid)
                ]
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Start Session Error: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'فشل بدء الحصة.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function joinSession(Request $request, $sessionId)
    {
        try {
            $userId = auth()->id();
            $now = now();
            $session = RecitationSession::with(['teacher.user'])->findOrFail($sessionId);
            if ($now->gt($session->end_at)) {
                $session->update(['status' => 'ended']);
                return response()->json([
                    'status' => false,
                    'message' => 'هذه الحصة انتهت زمنياً.'
                ], 400);
            }
            if ($session->status !== 'live') {
                return response()->json([
                    'status' => false,
                    'message' => 'الحصة لم تبدأ بعد.'
                ], 403);
            }
            $token = $this->agoraService->generateToken(
                $session->channel_name,
                $userId,
                'subscriber'
            );
            Session_student::updateOrCreate(
                ['recitation_session_id' => $session->id, 'user_id' => $userId],
                ['joined_at' => $now, 'left_at' => null]
            );

            $teacher = $session->teacher;
            $teacherUser = $teacher->user ?? null;

            return response()->json([
                'status' => true,
                'message' => 'تم الانضمام للحصة بنجاح.',
                'data' => [
                    'agora_token'         => $token,
                    'channel_name'        => $session->channel_name,
                    'app_id'              => config('services.agora.app_id'),
                    'uid'                 => (int)$userId,
                    'teacher_name'        => $teacherUser->name ?? 'غير متوفر',
                    'teacher_image'       => ($teacher && $teacher->profile_photo_path)
                        ? url('storage/' . $teacher->profile_photo_path)
                        : url('assets/images/default-avatar.png'),
                    'max_minutes_allowed' => (int)$session->duration_minutes,
                    'is_recording'        => (bool)$session->is_recorded
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل الانضمام للحصة.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function leaveSession(Request $request, $sessionId)
    {
        try {
            $userId = auth()->id();
            $now = now();

            $attendance = Session_student::where('recitation_session_id', $sessionId)
                ->where('user_id', $userId)
                ->whereNull('left_at')
                ->latest()
                ->first();

            if (!$attendance) {
                return response()->json([
                    'status' => false,
                    'message' => 'لا يوجد سجل حضور نشط.'
                ], 404);
            }

            $attendance->update([
                'left_at' => $now
            ]);

            $duration = $attendance->joined_at->diffInMinutes($now);

            return response()->json([
                'status' => true,
                'message' => 'تم تسجيل المغادرة بنجاح.',
                'data' => [
                    'duration_minutes' => $duration
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل تسجيل المغادرة.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function endSession(Request $request, $sessionId)
    {
        try {
            $userId = auth()->id();

            $session = RecitationSession::with('teacher')->findOrFail($sessionId);

            if ($userId !== $session->teacher->user_id) {
                return response()->json([
                    'status'  => false,
                    'message' => 'غير مصرح لك بإنهاء هذه الحصة.'
                ], 403);
            }

            if ($session->status === 'ended') {
                return response()->json([
                    'status'  => false,
                    'message' => 'الحصة مغلقة مسبقاً.'
                ], 400);
            }

            $recordingUrl = $session->recording_url;

            if ($session->is_recorded && !empty($session->agora_sid) && !empty($session->agora_resource_id)) {

                $recorderUid = 999999;
                $fileName = $this->agoraService->stop(
                    $session->agora_resource_id,
                    $session->agora_sid,
                    $session->channel_name,
                    $recorderUid
                );

                if ($fileName) {
                    // 🚀 الإصلاح هنا: استخدام الرابط العام لكي يعمل الفيديو في المتصفح والموبايل فوراً
                    $publicUrl = env('CLOUDFLARE_R2_PUBLIC_URL');

                    if ($publicUrl) {
                        // دمج الرابط العام مع مسار الملف
                        $recordingUrl = rtrim($publicUrl, '/') . '/' . ltrim($fileName, '/');
                    } else {
                        // في حالة نسيان وضع الرابط العام في الـ env
                        $endpoint = env('AGORA_STORAGE_ENDPOINT');
                        $bucket   = env('AGORA_STORAGE_BUCKET');
                        $recordingUrl = "https://{$endpoint}/{$bucket}/{$fileName}";
                    }
                } else {
                    \Illuminate\Support\Facades\Log::error("Failed to stop Agora recording for session: {$sessionId}");
                }
            }

            return DB::transaction(function () use ($session, $sessionId, $recordingUrl) {

                $now = now();
                $session->update([
                    'status'        => 'ended',
                    'recording_url' => $recordingUrl
                ]);

                $affected = Session_student::where('recitation_session_id', $sessionId)
                    ->whereNull('left_at')
                    ->update(['left_at' => $now]);

                // الاستجابة لم تتغير كما طلبت
                return response()->json([
                    'status'  => true,
                    'message' => 'تم إنهاء الحصة بنجاح.',
                    'summary' => [
                        'force_logged_out' => $affected,
                        'ended_at'         => $now->format('H:i:s'),
                        'recording_url'    => $recordingUrl // 🚀 سيرجع الآن الرابط العام الجاهز للتشغيل
                    ]
                ]);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("End Session Error: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'فشل إنهاء الحصة.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function getAttendance($sessionId)
    {
        try {
            $userId = auth()->id();

            $session = RecitationSession::with('teacher')
                ->findOrFail($sessionId);

            if ($userId !== $session->teacher->user_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'غير مصرح لك بعرض كشف الحضور.'
                ], 403);
            }

            $attendees = Session_student::with('student:id,name,email')
                ->where('recitation_session_id', $sessionId)
                ->get()
                ->map(function ($record) {

                    $joined = $record->joined_at;
                    $left = $record->left_at ?? now();

                    $duration = $joined
                        ? $joined->diffInSeconds($left)
                        : 0;

                    return [
                        'id' => $record->id,
                        'student_name' => $record->student->name ?? 'N/A',
                        'student_email' => $record->student->email ?? null,
                        'joined_at' => $joined?->format('H:i:s'),
                        'left_at' => $record->left_at
                            ? $record->left_at->format('H:i:s')
                            : 'متصل حالياً',
                        'duration_minutes' => round($duration / 60, 2),
                        'is_present' => is_null($record->left_at),
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'تم جلب كشف الحضور بنجاح.',
                'session_title' => $session->title,
                'summary' => [
                    'total_students' => $attendees->count(),
                    'active_now' => $attendees->where('is_present', true)->count(),
                ],
                'data' => $attendees
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل جلب كشف الحضور.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function getAllSessionsForStudent(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);

            // 1️⃣ جلب الجلسات المباشرة والقادمة (المجدولة) التي لم ينتهِ وقتها بعد
            $sessions = RecitationSession::with(['teacher.user'])
                ->whereIn('status', ['live', 'upcoming', 'scheduled'])
                ->where('end_at', '>', now()) // استبعاد الجلسات التي انتهى وقتها الزمني
                ->orderBy('start_at', 'asc') // الترتيب تصاعدياً (الأقرب أولاً)
                ->paginate($perPage);

            // 2️⃣ تعديل شكل البيانات لتكون جاهزة لتطبيق الموبايل
            $sessions->getCollection()->transform(function ($session) {

                // الطالب يقدر ينضم إذا كانت الحصة "لايف"
                // أو إذا اقترب وقتها (قبل 10 دقائق من البداية وحتى النهاية)
                $isJoinable = $session->status === 'live' ||
                    (now()->between(
                        $session->start_at->copy()->subMinutes(10), // استخدام copy() ضروري لمنع تغيير الوقت الأصلي للحصة
                        $session->end_at
                    ));

                $teacherName = $session->teacher->user->name ?? 'معلم';

                return [
                    'id'               => $session->id,
                    'title'            => $session->title,
                    'teacher_name'     => $teacherName,
                    // إضافة صورة المعلم لتطبيق الموبايل
                    'teacher_avatar'   => 'https://ui-avatars.com/api/?name=' . urlencode($teacherName) . '&background=0d9488&color=fff',
                    'status'           => $session->status,
                    'start_at'         => $session->start_at->format('Y-m-d H:i:s'),
                    'end_at'           => $session->end_at->format('Y-m-d H:i:s'),
                    'duration_minutes' => $session->duration_minutes ?? $session->start_at->diffInMinutes($session->end_at),
                    'is_joinable'      => $isJoinable, // true or false (تستخدم لفتح/إغلاق زر الانضمام في الموبايل)
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم جلب الحصص المباشرة والقادمة بنجاح.',
                'data'    => $sessions
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'فشل جلب الحصص.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function getTeacherSessions(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user->teacherProfile) {
                return response()->json([
                    'status' => false,
                    'message' => 'الحساب غير مرتبط بملف معلم.'
                ], 404);
            }

            $perPage = $request->query('per_page', 10);

            $sessions = RecitationSession::where(
                'teacher_id',
                $user->teacherProfile->id
            )
                ->with('teacher.user')
                ->orderBy('start_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'تم جلب حصص المعلم بنجاح.',
                'data' => $sessions
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل جلب الحصص.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getStudentSessionHistory(Request $request)
    {
        try {
            $userId = auth()->id();
            $perPage = $request->query('per_page', 10);
            $history = RecitationSession::where('status', 'ended')
                ->whereHas('attendees', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->with(['teacher.user', 'attendees' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }])
                ->orderBy('start_at', 'desc') // الأحدث أولاً
                ->paginate($perPage);
            $history->getCollection()->transform(function ($session) {
                $attendance = $session->attendees->first();
                $teacher = $session->teacher;
                $teacherUser = optional($teacher)->user;
                $teacherName = $teacherUser->name ?? 'معلم ورتل';

                return [
                    'id'                => $session->id,
                    'title'             => $session->title,
                    'teacher_name'      => $teacherName,
                    'teacher_avatar'    => ($teacher && $teacher->profile_photo_path)
                        ? asset('storage/' . $teacher->profile_photo_path)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($teacherName) . '&background=0d9488&color=fff',
                    'start_at'          => $session->start_at->format('Y-m-d H:i:s'),
                    'end_at'            => $session->end_at->format('Y-m-d H:i:s'),
                    'recording_url'     => $session->recording_url,
                    'is_reported'      => TeacherReport::where('reportable_type', RecitationSession::class)->where('reportable_id', $session->id)->exists(),
                    'my_attendance'     => [
                        'joined_at'        => optional($attendance)->joined_at ? $attendance->joined_at->format('H:i:s') : null,
                        'left_at'          => optional($attendance)->left_at ? $attendance->left_at->format('H:i:s') : null,
                        'duration_minutes' => (optional($attendance)->joined_at && optional($attendance)->left_at)
                            ? round($attendance->joined_at->diffInMinutes($attendance->left_at), 2)
                            : 0,
                    ]
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع سجل الحصص بنجاح.',
                'data'    => $history
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Get Student History Error: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'فشل جلب سجل الحصص.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
