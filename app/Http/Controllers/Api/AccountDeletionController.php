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
            // ---------- 1. Student cleanup (Direct lookup by user_id) ----------
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

                // Set phone to null and soft-delete the student row directly
                DB::table('students')
                    ->where('user_id', $user->id)
                    ->update([
                        'phone'      => null,
                        'deleted_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            // Ratings & Slot bookings belong to the user via `user_id`
            DB::table('ratings')->where('user_id', $user->id)->delete();
            DB::table('slot_bookings')->where('user_id', $user->id)->delete();

            // ---------- 2. Teacher cleanup ----------
            $teacher = $user->teacherProfile;
            if ($teacher) {
                DB::table('favorites')
                    ->where('teacher_id', $teacher->id)
                    ->delete();
                $teacher->tracks()->detach();

                $teacher->callSessions()->update(['teacher_id' => null]);
                $teacher->ratings()->update(['teacher_id' => null]);

                $teacher->delete();

                // If teacher has an application relation, clear its phone & delete
                if ($teacher->application) {
                    DB::table('teacher_applications')
                        ->where('id', $teacher->application->id)
                        ->update([
                            'phone'      => null,
                            'deleted_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
            }

            // Also clear phone on teacher_applications directly by user_id or email if the column exists
            if (\Illuminate\Support\Facades\Schema::hasColumn('teacher_applications', 'user_id')) {
                DB::table('teacher_applications')
                    ->where('user_id', $user->id)
                    ->update(['phone' => null]);
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('teacher_applications', 'email')) {
                DB::table('teacher_applications')
                    ->where('email', $user->email)
                    ->update(['phone' => null]);
            }

            // ---------- 3. User email update & soft-delete ----------
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

