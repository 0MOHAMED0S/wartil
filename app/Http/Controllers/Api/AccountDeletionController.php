<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Teacher_application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * AccountDeletionController
 *
 * Handles the "delete my account" endpoint for both students and teachers.
 */
class AccountDeletionController extends Controller
{
    /**
     * Delete the authenticated user (student or teacher).
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        DB::beginTransaction();
        try {
            // ---------- Student cleanup ----------
            if ($user->isStudent()) {
                $student = $user->studentProfile;
                if ($student) {
                    // Nullify foreign keys that reference the student
                    // Call sessions reference the student via student_id column
                    DB::table('call_sessions')->where('student_id', $student->id)->update(['student_id' => null]);
                    // Ratings may have a student_id column (direct rating by student)
                    DB::table('ratings')->where('student_id', $student->id)->update(['student_id' => null]);
                    // Slot bookings reference the user (student) via user_id column
                    // we nullify that to keep historic bookings
                    DB::table('slot_bookings')->where('user_id', $student->user->id)->update(['user_id' => null]);
                    // Soft‑delete the student profile
                    $student->delete();
                }
            }

            // ---------- Teacher cleanup ----------
            if ($user->isTeacher()) {
                $teacher = $user->teacherProfile;
                if ($teacher) {
                    // Detach many‑to‑many relations
                    // The "favorites" pivot is defined on the Student model, not on Teacher.
                    // Delete any favorite rows that reference this teacher.
                    DB::table('favorites')->where('teacher_id', $teacher->id)->delete();
                    $teacher->tracks()->detach();

                    // Nullify foreign keys that reference the teacher
                    // The SlotBooking model does not have a direct teacher_id column;
                    // bookings are linked via TeacherSlot. We keep historic bookings
                    // untouched, so no action is needed here.
                    // $teacher->slotBookings()->update(['teacher_id' => null]);
                    $teacher->callSessions()->update(['teacher_id' => null]);
                    $teacher->ratings()->update(['teacher_id' => null]);


                    // Soft‑delete the teacher profile
                    $teacher->delete();

                    // Delete the teacher application (the form record)
                    $application = $teacher->application;
                    if ($application) {
                        $application->delete();
                    }
                }
            }

            // ---------- User soft‑delete ----------
            $user->delete();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Account deleted successfully.',
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Account deletion error (user_id=' . $user->id . '): ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete account.',
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

