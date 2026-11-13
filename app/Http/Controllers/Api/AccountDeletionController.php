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
                    DB::table('call_sessions')
                        ->where('student_id', $student->id)
                        ->update(['student_id' => null]);

                    // Ratings belong to the student via the `user_id` column (NOT NULL)
                    DB::table('ratings')
                        ->where('user_id', $user->id)
                        ->delete();

                    // Slot bookings reference the user via `user_id` column (NOT NULL)
                    DB::table('slot_bookings')
                        ->where('user_id', $user->id)
                        ->delete();

                    // Clear student's phone and soft-delete the student profile
                    DB::table('students')->where('id', $student->id)->update(['phone' => '']);
                    $student->delete();
                }
            }

            // ---------- Teacher cleanup ----------
            if ($user->isTeacher()) {
                $teacher = $user->teacherProfile;
                if ($teacher) {
                    // Detach many-to-many relations
                    DB::table('favorites')
                        ->where('teacher_id', $teacher->id)
                        ->delete();
                    $teacher->tracks()->detach();

                    // Nullify foreign keys that reference the teacher
                    $teacher->callSessions()->update(['teacher_id' => null]);
                    $teacher->ratings()->update(['teacher_id' => null]);

                    // Soft-delete the teacher profile
                    $teacher->delete();

                    // Clear phone on teacher application and delete it
                    $application = $teacher->application;
                    if ($application) {
                        DB::table('teacher_applications')
                            ->where('id', $application->id)
                            ->update(['phone' => '']);
                        $application->delete();
                    }
                }
            }

            // ---------- User email update & soft-delete ----------
            $user->forceFill([
                'email' => 'deleted_' . $user->id . '_' . time() . '_' . $user->email,
            ])->save();

            // Revoke active API tokens (if using Sanctum/Passport)
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

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

