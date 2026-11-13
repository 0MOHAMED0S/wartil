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
            $deletedEmail = 'deleted_' . $user->id . '_' . time() . '_' . $user->email;
            $originalEmail = $user->email;

            // ---------- 1. Student cleanup ----------
            $student = \App\Models\Student::withTrashed()->where('user_id', $user->id)->first();
            if ($student) {
                // Nullify foreign keys that reference the student
                DB::table('call_sessions')
                    ->where('student_id', $student->id)
                    ->update(['student_id' => null]);

                // Remove student favorites
                DB::table('favorites')
                    ->where('student_id', $student->id)
                    ->delete();

                // Make student phone NULL and soft-delete
                DB::table('students')
                    ->where('id', $student->id)
                    ->update([
                        'phone'      => null,
                        'deleted_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            // Ratings & Slot bookings belong to the user via `user_id`
            DB::table('ratings')->where('user_id', $user->id)->delete();
            DB::table('slot_bookings')->where('user_id', $user->id)->delete();

            // ---------- 2. Teacher & Teacher_application cleanup ----------
            $teacher = \App\Models\Teacher::withTrashed()->where('user_id', $user->id)->first();
            $applicationId = null;

            if ($teacher) {
                $applicationId = $teacher->teacher_application_id;

                // Detach many-to-many relations
                DB::table('favorites')
                    ->where('teacher_id', $teacher->id)
                    ->delete();
                $teacher->tracks()->detach();

                // Nullify foreign keys that reference the teacher
                $teacher->callSessions()->update(['teacher_id' => null]);
                $teacher->ratings()->update(['teacher_id' => null]);

                // If teachers table also has a phone column, set it to null
                if (\Illuminate\Support\Facades\Schema::hasColumn('teachers', 'phone')) {
                    DB::table('teachers')
                        ->where('id', $teacher->id)
                        ->update(['phone' => null]);
                }

                // Soft-delete the teacher profile
                $teacher->delete();
            }

            // Find the Teacher_application by teacher_application_id OR by the user's email
            $applicationQuery = DB::table('teacher_applications');
            if ($applicationId) {
                $applicationQuery->where('id', $applicationId)->orWhere('email', $originalEmail);
            } else {
                $applicationQuery->where('email', $originalEmail);
            }

            // Update email to deleted and phone to null in teacher_applications
            $applicationQuery->update([
                'email'      => $deletedEmail,
                'phone'      => null,
                'updated_at' => now(),
            ]);

            // ---------- 3. User email update & soft-delete ----------
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'email'      => $deletedEmail,
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            // Revoke active API tokens (if using Sanctum/Passport)
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

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

