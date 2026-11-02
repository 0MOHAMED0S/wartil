<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use Illuminate\Http\Request;
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
                    'student:id,name',
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

            // Transform list for a cleaner overview
            $calls->getCollection()->transform(function ($call) {
                $rating = $call->ratings->first();
                return [
                    'id'               => $call->id,
                    'student_name'     => $call->student->name ?? 'طالب',
                    'status'           => $call->status,
                    'duration_minutes' => $call->duration_minutes,
                    'date'             => $call->created_at->format('Y-m-d'),
                    'time'             => $call->created_at->format('H:i:s'),
                    'recording_url'    => $call->recording_url, // 🎥 إضافة رابط التسجيل هنا
                    'rating'           => $rating ? $rating->rating : null,
                    'is_reported'      => TeacherReport::where('reportable_type', CallSession::class)
                                                ->where('reportable_id', $call->id)
                                                ->exists(),
                ];
            });

            return response()->json([
                'status' => true,
                'data'   => $calls
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب سجل المكالمات.'
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
