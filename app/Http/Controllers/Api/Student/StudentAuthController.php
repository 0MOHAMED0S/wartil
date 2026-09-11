<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ChangePasswordRequest;
use App\Http\Requests\Student\CheckOtpRequest;
use App\Http\Requests\Student\CompleteRegistrationRequest;
use App\Http\Requests\Student\ForgotPasswordSendOtpRequest;
use App\Http\Requests\Student\LoginRequest;
use App\Http\Requests\Student\ResetPasswordRequest;
use App\Http\Requests\Student\SendOtpRequest;
use App\Http\Requests\Student\UpdateProfileRequest;
use App\Mail\OtpMail;
use App\Mail\ResetOtpMail;
use App\Models\OtpCode;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class StudentAuthController extends Controller
{
    public function sendOtp(SendOtpRequest $request)
    {
        try {
            // Rate limiting to prevent spam and brute-force attacks (Max 5 attempts per hour)
            $key = 'send_otp_' . $request->ip() . '_' . md5($request->email);
            
            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
                return response()->json([
                    'status'  => false,
                    'message' => 'لقد تجاوزت الحد الأقصى للطلبات. يرجى المحاولة بعد ' . ceil($seconds / 60) . ' دقيقة.',
                ], 429);
            }

            // Prevent requesting a new OTP within 1 minute to avoid spam
            $recentOtp = OtpCode::where('email', $request->email)
                ->where('updated_at', '>=', Carbon::now()->subMinute())
                ->first();

            if ($recentOtp) {
                return response()->json([
                    'status'  => false,
                    'message' => 'يرجى الانتظار لمدة دقيقة قبل طلب رمز جديد.',
                ], 429);
            }

            $otp = random_int(1000, 9999);

            // Use DB Transactions to ensure data consistency if email sending fails
            DB::beginTransaction();

            OtpCode::updateOrCreate(
                ['email' => $request->email],
                [
                    'otp' => $otp,
                    'expires_at' => Carbon::now()->addMinutes(10),
                    'is_verified' => false,
                ]
            );

            // Send the OTP email
            Mail::to($request->email)->send(new OtpMail($otp));

            DB::commit();

            // Record the attempt in RateLimiter
            \Illuminate\Support\Facades\RateLimiter::hit($key, 3600);

            return response()->json([
                'status'  => true,
                'message' => 'تم إرسال رمز التحقق بنجاح.',
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Send OTP Error', [
                'email' => $request->email,
                'ip'    => $request->ip(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في إرسال رمز التحقق. حاول مرة أخرى لاحقاً.',
            ], 500);
        }
    }
    public function checkOtp(CheckOtpRequest $request)
    {
        try {
            // Brute-force protection using IP and email
            $key = 'check_otp_' . $request->ip() . '_' . md5($request->email);

            // Allow a maximum of 5 attempts
            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
                return response()->json([
                    'status'  => false,
                    'message' => 'محاولات خاطئة كثيرة. يرجى المحاولة بعد ' . ceil($seconds / 60) . ' دقيقة.',
                ], 429);
            }

            $otpRecord = OtpCode::where('email', $request->email)
                ->where('otp', $request->otp)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$otpRecord) {
                // Record failed attempt (expires after 10 minutes)
                \Illuminate\Support\Facades\RateLimiter::hit($key, 600);

                return response()->json([
                    'status'  => false,
                    'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
                ], 400);
            }

            // Clear failed attempts upon successful verification
            \Illuminate\Support\Facades\RateLimiter::clear($key);

            $otpRecord->update([
                'is_verified' => true,
                'otp' => null, // Delete the OTP to prevent reuse
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'تم التحقق من رمز التأكيد بنجاح.',
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Check OTP Error', [
                'email' => $request->email,
                'ip'    => $request->ip(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء التحقق من الرمز. حاول مرة أخرى لاحقاً.',
            ], 500);
        }
    }
    public function completeRegistration(CompleteRegistrationRequest $request)
    {
        DB::beginTransaction();

        try {
            $otpRecord = OtpCode::where('email', $request->email)
                ->where('is_verified', true)
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status'  => false,
                    'message' => 'لم يتم تأكيد البريد الإلكتروني. يرجى إدخال رمز التحقق أولاً.',
                ], 403);
            }

            $photoPath = null;
            if ($request->hasFile('profile_photo_path')) {
                // Validation is already handled by the Request class
                $photoPath = $request->file('profile_photo_path')->store('profiles', 'public');
            }

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'student',
            ]);

            $user->markEmailAsVerified();

            $countryId = $request->country_id;
            
            if (!$countryId) {
                try {
                    $ip = $request->ip();
                    // In local development, ip-api might not recognize 127.0.0.1, it will fallback
                    $response = \Illuminate\Support\Facades\Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
                    if ($response->successful() && $response->json('status') === 'success') {
                        $countryCode = $response->json('countryCode');
                        $country = \App\Models\Country::where('code', $countryCode)->first();
                        if ($country) {
                            $countryId = $country->id;
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('IP Geolocation failed: ' . $e->getMessage());
                }
                
                // Fallback if detection fails
                if (!$countryId) {
                    $countryId = \App\Models\Country::first()->id ?? 1;
                }
            }

            $student = Student::create([
                'user_id'             => $user->id,
                'country_id'          => $countryId,
                'phone'               => $request->phone,
                'address'             => $request->address,
                'qualification'       => $request->qualification,
                'professional_status' => $request->professional_status,
                'gender'              => $request->gender,
                'birth_date'          => $request->birth_date,
                'profile_photo_path'  => $photoPath,
            ]);

            $student->profile_photo_url = $photoPath ? asset('storage/' . $photoPath) : null;

            // Assign free minutes if enabled
            $setting = \App\Models\Setting::first();
            if ($setting && $setting->free_minutes_enabled && $setting->free_minutes_amount > 0) {
                $expiresAt = $setting->free_minutes_validity_days > 0 
                    ? now()->addDays($setting->free_minutes_validity_days) 
                    : null;

                \App\Models\UserPackage::create([
                    'user_id'           => $user->id,
                    'package_id'        => null,
                    'remaining_minutes' => $setting->free_minutes_amount,
                    'expires_at'        => $expiresAt,
                    'status'            => 'active',
                    'is_gift'           => true,
                ]);
            }

            // Delete the OTP record to prevent reuse
            $otpRecord->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            try {
                $admins = User::where('role', 'admin')->get();

                if ($admins->count() > 0) {
                    $notificationData = [
                        'student_id'   => $user->id,
                        'student_name' => $user->name,
                        'student_email' => $user->email,
                    ];

                    \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\DynamicNotification(
                        'طالب جديد 🎉',
                        "سجل الطالب {$user->name} للتو في التطبيق.",
                        'new_student',
                        $notificationData
                    ));

                    foreach ($admins as $admin) {
                        broadcast(new \App\Events\NewStudentRegistered($admin->id, $notificationData));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Admin Registration Notification Error', [
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'تم إنشاء الحساب بنجاح.',
                'data' => [
                    'user'    => $user,
                    'profile' => $student,
                    'token'   => $token,
                ]
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            // Delete the uploaded photo if the database transaction fails to prevent orphaned files
            if (isset($photoPath) && $photoPath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($photoPath);
            }

            Log::error('Complete Registration Error', [
                'email' => $request->email,
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء إنشاء الحساب. حاول مرة أخرى.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'بيانات تسجيل الدخول غير صحيحة.',
                ], 401);
            }

            if ($user->role !== 'student') {
                return response()->json([
                    'status'  => false,
                    'message' => 'غير مصرح لك بتسجيل الدخول من هنا.',
                ], 403);
            }

            // Check if the email address is verified
            if (is_null($user->email_verified_at)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'يرجى تأكيد البريد الإلكتروني الخاص بك أولاً.',
                ], 403);
            }

            // Optional: Revoke all other active sessions for this user to enforce single-device login
            // $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;
            $profile = $user->studentProfile;
            
            if ($profile) {
                $profile->profile_photo_url = $profile->profile_photo_path
                    ? asset('storage/' . $profile->profile_photo_path)
                    : null;
            }

            return response()->json([
                'status'  => true,
                'message' => 'تم تسجيل الدخول بنجاح.',
                'data' => [
                    'user'    => $user,
                    'profile' => $profile,
                    'token'   => $token,
                ]
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Login Error', [
                'email' => $request->email,
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء تسجيل الدخول. حاول مرة أخرى.',
            ], 500);
        }
    }
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'status'  => false,
                'message' => 'لم يتم العثور على الملف الشخصي.',
            ], 404);
        }

        DB::beginTransaction();

        $oldPhotoPath = null;
        $newPhotoPath = null;

        try {
            if ($request->filled('name')) {
                $user->update(['name' => $request->name]);
            }

            $studentData = $request->only([
                'phone',
                'address',
                'qualification',
                'professional_status',
                'country_id',
                'gender',
                'birth_date',
                'reading_level',
                'preferred_teacher_language',
                'reading_track',
                'memorized_amount',

                'plan_name',
                'reading_type',
                'teacher_response_speed'
            ]);

            if ($request->hasFile('profile_photo_path')) {
                // Store the old photo path to delete it only after a successful update
                $oldPhotoPath = $student->profile_photo_path;

                // Upload the new photo
                $newPhotoPath = $request->file('profile_photo_path')->store('students/photos', 'public');
                $studentData['profile_photo_path'] = $newPhotoPath;
            }

            $student->update($studentData);

            DB::commit();

            // Delete the old photo from storage after a successful database commit
            if ($oldPhotoPath && $newPhotoPath) {
                Storage::disk('public')->delete($oldPhotoPath);
            }

            return response()->json([
                'status'  => true,
                'message' => 'تم تحديث الملف الشخصي بنجاح.',
                'data' => [
                    'user'      => $user->fresh(),
                    'profile'   => $student->fresh(),
                    'photo_url' => $student->profile_photo_path
                        ? asset('storage/' . $student->profile_photo_path)
                        : null,
                ]
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            // Delete the newly uploaded photo if the database transaction fails
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            Log::error('Update Profile Error', [
                'user_id' => $user->id,
                'ip'      => $request->ip(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في تحديث الملف الشخصي. حاول مرة أخرى.',
            ], 500);
        }
    }
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user()->load(['student.country']);
            $userId = $user->id;

            if ($user->student) {
                $user->student->profile_photo_url = $user->student->profile_photo_path
                    ? asset('storage/' . $user->student->profile_photo_path)
                    : null;
            }

            $callsData = DB::table('call_sessions')
                ->where('student_id', $userId)
                ->where('status', 'ended')
                ->selectRaw('COUNT(id) as total_calls, SUM(duration_minutes) as total_minutes')
                ->first();

            $callsCount = (int) ($callsData->total_calls ?? 0);
            $totalMinutes = (int) ($callsData->total_minutes ?? 0);

            $learningHours = (int) floor($totalMinutes / 60);
            $remainingMinutes = $totalMinutes % 60;

            $slotsCount = DB::table('slot_bookings')
                ->where('user_id', $userId)
                ->where('status', '!=', 'cancelled')
                ->count();

            $sessionsCount = 0;
            try {
                $sessionsCount = DB::table('sessions')
                    ->where('student_id', $userId)
                    ->count();
            } catch (\Exception $e) {
                // Log the error instead of failing silently for easier debugging
                Log::warning('Get Student Profile - Sessions Count Error', [
                    'user_id' => $userId,
                    'error'   => $e->getMessage()
                ]);
                $sessionsCount = 0;
            }

            $user->statistics = [
                'calls_count'    => $callsCount,
                'slots_count'    => $slotsCount,
                'sessions_count' => $sessionsCount,
                'learning_stats' => [
                    'total_minutes' => $totalMinutes,
                    'hours'         => $learningHours,
                    'minutes'       => $remainingMinutes,
                    'formatted'     => "{$learningHours} ساعة و {$remainingMinutes} دقيقة"
                ]
            ];

            return response()->json([
                'status' => true,
                'data'   => $user
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Student Profile Error', [
                'user_id' => optional($request->user())->id,
                'ip'      => $request->ip(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء جلب البيانات.',
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || !$user->currentAccessToken()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'المستخدم غير مسجل الدخول.',
                ], 401);
            }

            $user->currentAccessToken()->delete();

            return response()->json([
                'status'  => true,
                'message' => 'تم تسجيل الخروج بنجاح.',
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Logout Error', [
                'user_id' => optional($request->user())->id,
                'ip'      => $request->ip(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء تسجيل الخروج. حاول مرة أخرى.',
            ], 500);
        }
    }
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                // Log failed attempt to detect suspicious activity
                Log::warning('Failed Password Change Attempt', [
                    'user_id' => $user->id,
                    'ip'      => $request->ip()
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'كلمة المرور الحالية غير صحيحة.'
                ], 401);
            }

            // Ensure the new password is different from the current one
            if (Hash::check($request->new_password, $user->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'كلمة المرور الجديدة يجب أن تكون مختلفة عن كلمة المرور الحالية.'
                ], 400);
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Best Practice: Revoke all other active sessions after password change
            if ($user->currentAccessToken()) {
                $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
            }

            return response()->json([
                'status'  => true,
                'message' => 'تم تغيير كلمة المرور بنجاح.'
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Change Password Error', [
                'user_id' => optional($request->user())->id,
                'ip'      => $request->ip(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء تغيير كلمة المرور. حاول مرة أخرى.'
            ], 500);
        }
    }
    public function forgotPasswordSendOtp(ForgotPasswordSendOtpRequest $request)
    {
        try {
            // Ensure the account belongs to a student to prevent unauthorized resets
            $user = User::where('email', $request->email)->first();
            if ($user && $user->role !== 'student') {
                return response()->json([
                    'status'  => false,
                    'message' => 'غير مصرح لك باستعادة كلمة المرور من هنا.',
                ], 403);
            }

            // Prevent requesting a new OTP within 1 minute to avoid spam
            $recentOtp = OtpCode::where('email', $request->email)
                ->where('updated_at', '>=', now()->subMinute())
                ->first();

            if ($recentOtp) {
                return response()->json([
                    'status'  => false,
                    'message' => 'يرجى الانتظار لمدة دقيقة قبل طلب رمز جديد.',
                ], 429);
            }

            $otp = random_int(1000, 9999);

            // Use DB Transactions to ensure data consistency if email sending fails
            DB::beginTransaction();

            OtpCode::updateOrCreate(
                ['email' => $request->email],
                [
                    'otp'         => $otp,
                    'expires_at'  => now()->addMinutes(10),
                    'is_verified' => false,
                ]
            );

            Mail::to($request->email)->send(new ResetOtpMail($otp));

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'تم إرسال رمز التحقق لإعادة تعيين كلمة المرور.',
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Forgot Password OTP Error', [
                'email' => $request->email,
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في إرسال رمز التحقق. حاول مرة أخرى.',
            ], 500);
        }
    }
    public function resetPassword(ResetPasswordRequest $request)
    {
        DB::beginTransaction();

        try {
            $otpRecord = OtpCode::where('email', $request->email)
                ->where('is_verified', true)
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status'  => false,
                    'message' => 'لم يتم التحقق من البريد الإلكتروني. يرجى تأكيد رمز التحقق أولاً.',
                ], 403);
            }

            $user = User::where('email', $request->email)->first();

            // Ensure the account belongs to a student
            if ($user && $user->role !== 'student') {
                return response()->json([
                    'status'  => false,
                    'message' => 'غير مصرح لك بتغيير كلمة المرور لهذا الحساب من هنا.',
                ], 403);
            }

            // Ensure the new password is different from the previous one
            if (Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'كلمة المرور الجديدة يجب أن تكون مختلفة عن الكلمة السابقة.'
                ], 400);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // Revoke all active sessions for security
            $user->tokens()->delete();

            // Delete the OTP record to prevent reuse
            $otpRecord->delete();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'تم إعادة تعيين كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.',
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Reset Password Error', [
                'email' => $request->email,
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء إعادة تعيين كلمة المرور. حاول مرة أخرى.',
            ], 500);
        }
    }
}
