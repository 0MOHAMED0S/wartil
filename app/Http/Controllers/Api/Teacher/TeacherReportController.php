<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherReport;
use App\Models\CallSession;
use App\Models\SlotBooking;
use App\Models\Session_student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class TeacherReportController extends Controller
{
    /**
     * Submit a report for a student after a session/call
     */
public function store(Request $request)
{
    try {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['status' => false, 'message' => 'Teacher profile not found.'], 404);
        }

        $validated = $request->validate([
            'student_id'        => 'required|integer',
            'reportable_id'     => 'required|integer', // إجباري لضمان ربط التقرير بمكالمة أو حصة
            'reportable_type'   => 'required|string|in:call,slot,session',
            'attendance_status' => 'required|string|in:present,absent',
            'reception_level'   => 'nullable|string|in:excellent,very_good,good,needs_follow_up',
            'notes'             => 'nullable|string',
            'session_content'   => 'nullable|string',
        ]);

        // التأكد من أن student_id هو user_id الصحيح في جدول users
        $studentUserId = \App\Models\User::where('id', $validated['student_id'])->value('id')
                      ?? \App\Models\Student::where('id', $validated['student_id'])->value('user_id');

        if (!$studentUserId) {
            return response()->json([
                'status'  => false,
                'message' => 'الطالب غير موجود.'
            ], 422);
        }

        $typeMap = [
            'call'    => \App\Models\CallSession::class,
            'slot'    => \App\Models\SlotBooking::class,
            'session' => \App\Models\Session_student::class,
        ];

        $reportableType = $typeMap[$validated['reportable_type']] ?? null;
        $reportableId   = (int) $validated['reportable_id'];

        // جمع المعرفات المحتملة للحصة (سواء أرسل الفرونت booking_id أو teacher_slot_id) لمنع التكرار بأي شكل
        $idsToCheck = [$reportableId];
        if ($validated['reportable_type'] === 'slot') {
            $booking = \App\Models\SlotBooking::where('id', $reportableId)
                ->orWhere('teacher_slot_id', $reportableId)
                ->first();

            if ($booking) {
                $idsToCheck = array_unique([$booking->id, $booking->teacher_slot_id]);
            }
        }

        // التحقق مما إذا كان هناك تقرير مرفوع مسبقاً لنفس المكالمة أو الحصة
        $alreadyReported = \App\Models\TeacherReport::where('reportable_type', $reportableType)
            ->whereIn('reportable_id', $idsToCheck)
            ->exists();

        if ($alreadyReported) {
            return response()->json([
                'status'  => false,
                'message' => 'تم رفع تقرير لهذه الجلسة مسبقاً ولا يمكن إرسال أكثر من تقرير.'
            ], 422); // يمكنك تغييره إلى 409 أو 400 حسب ما يتوقعه الفرونت إند لديك
        }

        // Backward compatibility: set report text
        $legacyReportText = $validated['notes'] ?? ($validated['session_content'] ?? 'Session Report');

        $report = \App\Models\TeacherReport::create([
            'teacher_id'        => $teacher->id,
            'student_id'        => $studentUserId,
            'reportable_id'     => $reportableId,
            'reportable_type'   => $reportableType,
            'attendance_status' => $validated['attendance_status'],
            'reception_level'   => $validated['reception_level'] ?? null,
            'notes'             => $validated['notes'] ?? null,
            'session_content'   => $validated['session_content'] ?? null,
            'report'            => $legacyReportText,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'تم رفع التقرير بنجاح',
            'data'    => $report
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status'  => false,
            'message' => 'بيانات غير صالحة.',
            'errors'  => $e->errors(),
        ], 422);
    } catch (\Throwable $e) {
        return response()->json([
            'status'  => false,
            'message' => 'حدث خطأ أثناء حفظ التقرير.',
            'error'   => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}

    // Get report for a specific call (if exists)
    public function getByCall(Request $request, $callId): JsonResponse
    {
        try {
            $teacher = auth()->user()->teacher;
            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'Teacher profile not found.'], 404);
            }

            // Ensure the call belongs to this teacher
            $call = CallSession::where('id', $callId)
                ->where('teacher_id', $teacher->id)
                ->first();

            if (!$call) {
                return response()->json(['status' => false, 'message' => 'المكالمة غير موجودة أو لا تملك صلاحية عرضها.'], 404);
            }

            $report = TeacherReport::where('reportable_type', CallSession::class)
                ->where('reportable_id', $callId)
                ->first();

            if (!$report) {
                return response()->json(['status' => false, 'message' => 'لا يوجد تقرير لهذه المكالمة.'], 404);
            }

            return response()->json([
                'status' => true,
                'data'   => $report,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Call Report Error', [
                'teacher_id' => optional($teacher)->id,
                'call_id'    => $callId,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء جلب التقرير.'], 500);
        }
    }

    // Show a specific report by its ID
    public function show(Request $request, $reportId): JsonResponse
    {
        try {
            $teacher = auth()->user()->teacher;
            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'Teacher profile not found.'], 404);
            }

            $report = TeacherReport::where('id', $reportId)
                ->where('teacher_id', $teacher->id)
                ->first();

            if (!$report) {
                return response()->json(['status' => false, 'message' => 'التقرير غير موجود أو لا يخص المعلم.'], 404);
            }

            return response()->json([
                'status' => true,
                'data'   => $report,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Show Report Error', [
                'teacher_id' => optional($teacher)->id,
                'report_id'  => $reportId,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء جلب التقرير.'], 500);
        }
    }
    }