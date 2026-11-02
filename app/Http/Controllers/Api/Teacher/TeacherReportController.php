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
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['status' => false, 'message' => 'Teacher profile not found.'], 404);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'reportable_id' => 'nullable|integer',
            'reportable_type' => 'nullable|string|in:call,slot,session',
            'attendance_status' => 'required|string|in:present,absent',
            'reception_level' => 'nullable|string|in:excellent,very_good,good,needs_follow_up',
            'notes' => 'nullable|string',
            'session_content' => 'nullable|string',
        ]);

        $typeMap = [
            'call'    => CallSession::class,
            'slot'    => SlotBooking::class,
            'session' => Session_student::class,
        ];

        $reportableType = null;
        if (!empty($validated['reportable_type'])) {
            $reportableType = $typeMap[$validated['reportable_type']] ?? null;
        }

        // Backward compatibility: set report text
        $legacyReportText = $validated['notes'] ?? ($validated['session_content'] ?? 'Session Report');

        $report = TeacherReport::create([
            'teacher_id' => $teacher->id,
            'student_id' => $validated['student_id'],
            'reportable_id' => $validated['reportable_id'] ?? null,
            'reportable_type' => $reportableType,
            'attendance_status' => $validated['attendance_status'],
            'reception_level' => $validated['reception_level'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'session_content' => $validated['session_content'] ?? null,
            'report' => $legacyReportText, 
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم رفع التقرير بنجاح',
            'data' => $report
        ], 201);
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
}
