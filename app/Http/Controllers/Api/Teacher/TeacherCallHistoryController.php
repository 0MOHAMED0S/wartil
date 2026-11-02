<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use Illuminate\Http\Request;
use App\Models\TeacherReport;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class TeacherCallHistoryController extends Controller
{
    /**
     * Get all call history for the authenticated teacher
     */
public function index()
{
    try {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json([
                'status' => false,
                'message' => 'عذراً، لم يتم العثور على ملف معلم لهذا الحساب.'
            ], 404);
        }

        $calls = CallSession::where('teacher_id', $teacher->id)
            ->with([
                'student',
                'ratings'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        if ($calls->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'لا يوجد سجل مكالمات حالياً.',
                'data' => $calls
            ]);
        }

        // 🔍 كود الفحص (Debug) لمعرفة أين المشكلة بالضبط
        $firstCall = $calls->first();
        dd([
            '1_call_data'              => $firstCall ? $firstCall->toArray() : null,
            '2_student_relation'       => $firstCall?->student ? $firstCall->student->toArray() : 'Student relation is NULL',
            '3_student_by_user_id'     => $firstCall ? \App\Models\Student::where('user_id', $firstCall->student_id)->first()?->toArray() : null,
            '4_student_by_id'          => $firstCall ? \App\Models\Student::where('id', $firstCall->student_id)->first()?->toArray() : null,
            '5_sample_students_photos' => \App\Models\Student::whereNotNull('profile_photo_path')->take(3)->get(['id', 'user_id', 'profile_photo_path'])->toArray(),
        ]);

        // 1. جمع معرفات المكالمات الظاهرة في الصفحة الحالية
        $callIds = $calls->pluck('id');

        // 2. جلب الـ IDs المُبلغ عنها دفعة واحدة
        $reportedCallIds = TeacherReport::whereIn('reportable_id', $callIds)
            ->where(function ($query) {
                $query->where('reportable_type', CallSession::class)
                      ->orWhere('reportable_type', 'App\Models\CallSession')
                      ->orWhere('reportable_type', 'CallSession');
            })
            ->pluck('reportable_id')
            ->toArray();

        // 3. جمع كل معرّفات الطلاب في الصفحة لجلب صورهم دفعة واحدة
        $studentIds = $calls->pluck('student_id')->filter()->unique();
        
        $studentsData = \App\Models\Student::whereIn('user_id', $studentIds)
            ->orWhereIn('id', $studentIds)
            ->get();

        // Transform list for a cleaner overview
        $calls->getCollection()->transform(function ($call) use ($reportedCallIds, $studentsData) {
            $rating = $call->ratings->first();

            $studentRecord = $studentsData->first(function ($st) use ($call) {
                return $st->user_id == $call->student_id || $st->id == $call->student_id;
            });

            $photoPath = $studentRecord?->profile_photo_path 
                      ?? $call->student?->profile_photo_path;

            $imageUrl = null;
            if (!empty($photoPath)) {
                $imageUrl = filter_var($photoPath, FILTER_VALIDATE_URL) 
                    ? $photoPath 
                    : asset('storage/' . ltrim($photoPath, '/'));
            }

            return [
                'id'                => $call->id,
                'student_name'      => optional($call->student)->name ?? 'طالب',
                'student_image_url' => $imageUrl,
                'status'            => $call->status,
                'duration_minutes'  => $call->duration_minutes,
                'date'              => $call->created_at->format('Y-m-d'),
                'time'              => $call->created_at->format('H:i:s'),
                'recording_url'     => $call->recording_url,
                'rating'            => $rating ? $rating->rating : null,
                'is_reported'       => in_array($call->id, $reportedCallIds),
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $calls
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء جلب سجل المكالمات.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Get specific details of a call session
     */
    public function show($id)
    {
        try {
            $teacher = auth()->user()->teacher;

            if (!$teacher) {
                return response()->json([
                    'status' => false,
                    'message' => 'غير مسموح لك بالوصول لهذا المورد.'
                ], 403);
            }

            // Find call ensuring it belongs to THIS teacher
            $call = CallSession::where('teacher_id', $teacher->id)
                ->with([
                    'student:id,name,email',
                    'ratings'
                ])
                ->where('id', $id)
                ->first();

            if (!$call) {
                return response()->json([
                    'status' => false,
                    'message' => 'المكالمة غير موجودة أو لا تملك صلاحية عرضها.'
                ], 404);
            }

            $rating = $call->ratings->first();

            return response()->json([
                'status' => true,
                'data'   => [
                    'call_id' => $call->id,
                    'status'  => $call->status,
                    'student' => [
                        'id'    => $call->student_id,
                        'name'  => $call->student->name ?? 'N/A',
                        'email' => $call->student->email ?? 'N/A',
                    ],
                    'session_info' => [
                        'channel'                 => $call->channel_name,
                        'duration_minutes'        => $call->duration_minutes,
                        'minutes_added_to_wallet' => $call->duration_minutes,
                        'recording_url'           => $call->recording_url, // 🎥 إضافة رابط التسجيل هنا
                        'timing' => [
                            'date'       => $call->created_at->format('Y-m-d'),
                            'started_at' => $call->started_at ? $call->started_at->format('H:i:s') : null,
                            'ended_at'   => $call->ended_at ? $call->ended_at->format('H:i:s') : null,
                        ]
                    ],
                    'feedback' => $rating ? [
                        'stars'    => $rating->rating,
                        'comment'  => $rating->comment,
                        'rated_at' => $rating->created_at->format('Y-m-d H:i:s'),
                    ] : null,
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ غير متوقع.'
            ], 500);
        }
    }
}
