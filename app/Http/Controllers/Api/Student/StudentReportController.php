<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\TeacherReport;
use App\Models\CallSession;
use Illuminate\Http\Request;
use App\Models\SlotBooking;
use Illuminate\Support\Facades\Log;

class StudentReportController extends Controller
{
    /**
     * Get all reports for the authenticated student.
     */
    public function myReports(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير مسجل الدخول',
                ], 401);
            }

            if ($user->role !== 'student') {
                return response()->json([
                    'status' => false,
                    'message' => 'غير مصرح لك بالوصول لهذه البيانات',
                ], 403);
            }

            $perPage = min((int) $request->query('per_page', 15), 50);

            $reports = TeacherReport::where('student_id', $user->id)
                ->with([
                    'teacher:id,user_id',
                    'teacher.user:id,name',
                    'reportable',
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $reports->getCollection()->transform(function ($report) {
                return $this->formatReport($report);
            });

            return response()->json([
                'status' => true,
                'message' => 'تم جلب التقارير بنجاح',
                'data' => $reports,
            ]);
        } catch (\Throwable $e) {
            Log::error('Student MyReports Error', [
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب التقارير.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get all reports for a specific dependent of the authenticated user.
     */
    public function dependentReports(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير مسجل الدخول',
                ], 401);
            }

            if ($user->role !== 'student') {
                return response()->json([
                    'status' => false,
                    'message' => 'غير مصرح لك بالوصول لهذه البيانات',
                ], 403);
            }

            // Validate ID is a positive integer
            if (!is_numeric($id) || (int) $id <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'معرّف التابع غير صالح',
                ], 422);
            }

            $id = (int) $id;

            // Verify the dependent belongs to the authenticated user
            $dependent = $user->dependents()->where('id', $id)->first();

            if (!$dependent) {
                return response()->json([
                    'status' => false,
                    'message' => 'التابع غير موجود أو لا ينتمي إليك',
                ], 404);
            }

            // Prevent accessing own reports through this endpoint
            if ($dependent->id === $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'لا يمكنك استخدام هذا المسار لتقاريرك الشخصية',
                ], 422);
            }

            $perPage = min((int) $request->query('per_page', 15), 50);

            $reports = TeacherReport::where('student_id', $dependent->id)
                ->with([
                    'teacher:id,user_id',
                    'teacher.user:id,name',
                    'reportable',
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $reports->getCollection()->transform(function ($report) {
                return $this->formatReport($report);
            });

            return response()->json([
                'status' => true,
                'message' => 'تم جلب تقارير التابع بنجاح',
                'student' => [
                    'id' => $dependent->id,
                    'name' => $dependent->name,
                ],
                'data' => $reports,
            ]);
        } catch (\Throwable $e) {
            Log::error('Student DependentReports Error', [
                'user_id' => optional($request->user())->id,
                'dependent_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب تقارير التابع.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Format a single report for the API response.
     */
    private function formatReport(TeacherReport $report): array
    {
        return [
            'id' => $report->id,
            'teacher_name' => $report->teacher->user->name ?? 'Unknown',
            'teacher_id' => $report->teacher_id,
            'attendance_status' => $report->attendance_status,
            'reception_level' => $report->reception_level,
            'notes' => $report->notes,
            'session_content' => $report->session_content,
            'report' => $report->report,
            'reportable_type' => $report->reportable_type,
            'reportable_id' => $report->reportable_id,
            'created_at' => $report->created_at?->format('Y-m-d H:i:s'),
        ];
    }
    // Get a specific report by call ID for the authenticated student
    public function getByCall(Request $request, $callId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'المستخدم غير مسجل الدخول'], 401);
        }

        if ($user->role !== 'student') {
            return response()->json(['status' => false, 'message' => 'غير مصرح لك بالوصول لهذه البيانات'], 403);
        }

        $report = TeacherReport::where('student_id', $user->id)
            ->where('reportable_type', CallSession::class)
            ->where('reportable_id', $callId)
            ->with(['teacher.user:id,name', 'reportable'])
            ->first();

        if (!$report) {
            return response()->json(['status' => false, 'message' => 'التقرير غير موجود'], 404);
        }

        return response()->json(['status' => true, 'data' => $this->formatReport($report)]);
    }

    // Show a specific report by its ID for the authenticated student
    public function show(Request $request, $reportId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'المستخدم غير مسجل الدخول'], 401);
        }

        if ($user->role !== 'student') {
            return response()->json(['status' => false, 'message' => 'غير مصرح لك بالوصول لهذه البيانات'], 403);
        }

        $report = TeacherReport::where('student_id', $user->id)
            ->where('id', $reportId)
            ->with(['teacher.user:id,name', 'reportable'])
            ->first();

        if (!$report) {
            return response()->json(['status' => false, 'message' => 'التقرير غير موجود'], 404);
        }

        return response()->json(['status' => true, 'data' => $this->formatReport($report)]);
    }

    /**
 * Get a specific report for a booking (slot).
 *
 * GET /reports/booking/{slot_id}
 */
public function getReportByBooking(Request $request, $slot_id)
{
    try {
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'المستخدم غير مسجل الدخول'], 401);
        }

        if ($user->role !== 'student') {
            return response()->json(['status' => false, 'message' => 'غير مصرح لك بالوصول لهذه البيانات'], 403);
        }

        // Validate slot_id
        if (!is_numeric($slot_id) || (int) $slot_id <= 0) {
            return response()->json(['status' => false, 'message' => 'معرّف الحجز غير صالح'], 422);
        }

        $slot_id = (int) $slot_id;

        // البحث عن الحجز المرتبط بهذا الـ slot والمستخدم للتأكد من وجوده وجلب الـ booking_id والـ slot_id معاً
        $booking = SlotBooking::where('user_id', $user->id)
            ->where(function ($query) use ($slot_id) {
                $query->where('id', $slot_id)
                      ->orWhere('teacher_slot_id', $slot_id);
            })->first();

        $bookingId = $booking ? $booking->id : null;
        $actualSlotId = $booking ? $booking->teacher_slot_id : $slot_id;

        // جلب التقرير بالبحث عن reportable_id إما بقيمة الـ slot_id أو الـ booking_id
        $report = TeacherReport::where('student_id', $user->id)
            ->where('reportable_type', SlotBooking::class)
            ->where(function ($query) use ($actualSlotId, $bookingId) {
                $query->where('reportable_id', $actualSlotId);
                if ($bookingId) {
                    $query->orWhere('reportable_id', $bookingId);
                }
            })
            ->with(['teacher:id,user_id', 'teacher.user:id,name'])
            ->first();

        if (!$report) {
            return response()->json(['status' => false, 'message' => 'التقرير غير موجود'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم جلب التقرير بنجاح',
            'data' => $this->formatReport($report),
        ], 200);
    } catch (\Throwable $e) {
        Log::error('Student getReportByBooking Error', [
            'user_id' => optional($request->user())->id,
            'slot_id' => $slot_id ?? null,
            'error'   => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء جلب التقرير.',
            'error'   => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}


}
