<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\TeacherReport;

class StudentCallHistoryController extends Controller
{
public function index()
{
    $studentId = auth()->id();

    $calls = CallSession::where('student_id', $studentId)
        ->with([
            'teacher.user:id,name',
            'teacher.application', // لجلب صورة المعلم إذا كانت مخزنة في جدول طلبات المعلمين
            'ratings' => function ($query) use ($studentId) {
                $query->where('user_id', $studentId);
            }
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(15);

    // 1. جلب صورة الطالب المسجل دخوله مرة واحدة فقط
    $studentPhotoPath = \App\Models\Student::where('user_id', $studentId)->value('profile_photo_path');
    $studentImageUrl = $studentPhotoPath 
        ? (filter_var($studentPhotoPath, FILTER_VALIDATE_URL) ? $studentPhotoPath : asset('storage/' . ltrim($studentPhotoPath, '/'))) 
        : null;

    // 2. جلب معرفات المكالمات المُبلغ عنها دفعة واحدة
    $callIds = $calls->pluck('id');
    $reportedCallIds = TeacherReport::whereIn('reportable_id', $callIds)
        ->where(function ($query) {
            $query->where('reportable_type', CallSession::class)
                  ->orWhere('reportable_type', 'App\Models\CallSession')
                  ->orWhere('reportable_type', 'CallSession');
        })
        ->pluck('reportable_id')
        ->toArray();

    $calls->getCollection()->transform(function ($call) use ($studentImageUrl, $reportedCallIds) {
        $rating = $call->ratings->first();

        // استخراج مسار صورة المعلم من الحقول المحتملة في جدول المعلم أو طلب التقديم
        $teacherPhotoPath = $call->teacher?->profile_photo_path 
                         ?? $call->teacher?->photo 
                         ?? $call->teacher?->image 
                         ?? $call->teacher?->application?->profile_photo_path 
                         ?? $call->teacher?->application?->photo 
                         ?? $call->teacher?->application?->personal_photo;

        $teacherImageUrl = null;
        if (!empty($teacherPhotoPath)) {
            $teacherImageUrl = filter_var($teacherPhotoPath, FILTER_VALIDATE_URL)
                ? $teacherPhotoPath
                : asset('storage/' . ltrim($teacherPhotoPath, '/'));
        }

        return [
            'id'                => $call->id,
            'teacher_name'      => $call->teacher?->user?->name ?? 'Unknown Teacher',
            'teacher_id'        => $call->teacher_id,
            'teacher_image_url' => $teacherImageUrl, // 👨‍🏫 رابط صورة المعلم
            'student_image_url' => $studentImageUrl, // 🧑‍🎓 رابط صورة الطالب
            'status'            => $call->status,
            'duration_minutes'  => $call->duration_minutes,
            'started_at'        => $call->started_at ? $call->started_at->format('Y-m-d H:i:s') : null,
            'created_at'        => $call->created_at->format('Y-m-d H:i:s'),
            'recording_url'     => $call->recording_url,
            'is_rated'          => (bool) $rating,
            'rating_stars'      => $rating ? $rating->rating : null,
            'is_reported'       => in_array($call->id, $reportedCallIds),
        ];
    });

    return response()->json([
        'status' => true,
        'data'   => $calls
    ]);
}

public function show($id)
{
    $studentId = auth()->id();

    $call = CallSession::where('student_id', $studentId)
        ->with([
            'teacher.user:id,name',
            'teacher.application', // جلب كامل علاقة الطلب لتجنب فشل الربط بسبب نقص المفاتيح الأجنبية
            'ratings' => function ($query) use ($studentId) {
                $query->where('user_id', $studentId);
            }
        ])
        ->findOrFail($id);

    $rating = $call->ratings->first();

    // 1. جلب صورة الطالب المسجل دخوله
    $studentPhotoPath = \App\Models\Student::where('user_id', $studentId)->value('profile_photo_path');
    $studentImageUrl = $studentPhotoPath 
        ? (filter_var($studentPhotoPath, FILTER_VALIDATE_URL) ? $studentPhotoPath : asset('storage/' . ltrim($studentPhotoPath, '/'))) 
        : null;

    // 2. جلب صورة المعلم بأمان من جدول المعلم أو طلب الانضمام
    $teacherPhotoPath = $call->teacher?->application?->profile_photo_path 
                     ?? $call->teacher?->profile_photo_path;
    $teacherImageUrl = $teacherPhotoPath 
        ? (filter_var($teacherPhotoPath, FILTER_VALIDATE_URL) ? $teacherPhotoPath : asset('storage/' . ltrim($teacherPhotoPath, '/'))) 
        : null;

    // 3. جلب التقرير الخاص بهذه المكالمة (إن وُجد)
    $report = \App\Models\TeacherReport::where('reportable_id', $call->id)
        ->where(function ($query) {
            $query->where('reportable_type', CallSession::class)
                  ->orWhere('reportable_type', 'App\Models\CallSession')
                  ->orWhere('reportable_type', 'CallSession');
        })
        ->first();

    return response()->json([
        'status' => true,
        'data'   => [
            'call_id'           => $call->id,
            'status'            => $call->status,
            'student_image_url' => $studentImageUrl, // 🧑‍🎓 رابط صورة الطالب

            'teacher' => [
                'id'            => $call->teacher_id,
                'name'          => $call->teacher?->user?->name ?? $call->teacher?->application?->full_name ?? 'N/A',
                'photo'         => $teacherImageUrl, // 👨‍🏫 رابط صورة المعلم
                'qualification' => $call->teacher?->application?->qualification ?? null,
            ],

            'session_details' => [
                'channel_name'     => $call->channel_name,
                'duration_minutes' => $call->duration_minutes,
                'recording_url'    => $call->recording_url,
                'is_reported'      => (bool) $report,
                'timeline' => [
                    'requested_at' => $call->created_at->format('Y-m-d H:i:s'),
                    'started_at'   => $call->started_at ? $call->started_at->format('H:i:s') : null,
                    'ended_at'     => $call->ended_at ? $call->ended_at->format('H:i:s') : null,
                    'date'         => $call->created_at->format('Y-m-d'),
                ]
            ],

            // 📄 بيانات التقرير (تظهر تفاصيله إذا كان موجوداً، أو null إذا لم يُرفع تقرير بعد)
            'report' => $report ? [
                'id'                => $report->id,
                'attendance_status' => $report->attendance_status,
                'reception_level'   => $report->reception_level,
                'notes'             => $report->notes,
                'session_content'   => $report->session_content,
                'report_text'       => $report->report,
                'created_at'        => $report->created_at ? $report->created_at->format('Y-m-d H:i:s') : null,
            ] : null,

            'rating' => $rating ? [
                'id'         => $rating->id,
                'stars'      => $rating->rating,
                'comment'    => $rating->comment,
                'created_at' => $rating->created_at->format('Y-m-d H:i:s'),
            ] : null,
        ]
    ]);
}
}
