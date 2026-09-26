<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\CallSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DependentController extends Controller
{
    /**
     * Send OTP to parent's email for adding a dependent.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ], [
            'email.unique' => 'البريد الإلكتروني هذا مستخدم بالفعل.',
        ]);

        $email = $request->email;

        try {
            // Generate 4-digit OTP
            $otp = rand(1000, 9999);

            \App\Models\OtpCode::updateOrCreate(
                ['email' => $email],
                [
                    'otp' => (string) $otp,
                    'expires_at' => Carbon::now()->addMinutes(10),
                    'is_verified' => false,
                ]
            );

            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\OtpMail($otp));

            return response()->json([
                'status' => true,
                'message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني بنجاح.',
            ], 200);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Dependent Send OTP Error', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'فشل في إرسال رمز التحقق. يرجى المحاولة لاحقاً.',
            ], 500);
        }
    }

    /**
     * Check OTP for parent.
     */
    public function checkOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
        ]);

        $otpRecord = \App\Models\OtpCode::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'status'  => false,
                'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
            ], 400);
        }

        $otpRecord->update([
            'is_verified' => true,
            'otp' => null, // Delete the OTP to prevent reuse
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'تم التحقق من رمز التأكيد بنجاح.',
        ], 200);
    }

    /**
     * Get list of dependents for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $dependents = User::where('parent_id', $user->id)
            ->with(['student.country'])
            ->get();

        $data = $dependents->map(function ($dependent) {
            $student = $dependent->student;
            
            // Calculate sessions this month
            $sessionsThisMonth = CallSession::where('student_id', $dependent->id)
                ->where('status', 'ended')
                ->whereMonth('started_at', Carbon::now()->month)
                ->whereYear('started_at', Carbon::now()->year)
                ->count();

            // Next session
            $nextSession = \App\Models\SlotBooking::where('user_id', $dependent->id)
                ->whereIn('status', ['scheduled', 'started', 'confirmed'])
                ->where('started_at', '>=', Carbon::now())
                ->orderBy('started_at', 'asc')
                ->first();

            $nextSessionStr = null;
            if ($nextSession && $nextSession->started_at) {
                $sessionDate = Carbon::parse($nextSession->started_at);
                $isToday = $sessionDate->isToday();
                $isTomorrow = $sessionDate->isTomorrow();
                
                $dateStr = $isToday ? 'اليوم' : ($isTomorrow ? 'الغد' : $sessionDate->format('Y-m-d'));
                $timeStr = $sessionDate->format('h:i A');
                
                $nextSessionStr = "الجلسة: {$dateStr}، {$timeStr}";
            }

            $dependentAvailableMinutes = (int) \App\Models\UserPackage::where('user_id', $dependent->id)
                ->whereIn('status', ['active', 'Active'])
                ->where('remaining_minutes', '>', 0)
                ->where(function ($q) {
                    $q->where('expires_at', '>', Carbon::now())
                        ->orWhereNull('expires_at');
                })
                ->sum('remaining_minutes');

            $hasReports = \App\Models\TeacherReport::where('student_id', $dependent->id)->exists();

            return [
                'id' => $dependent->id,
                'name' => $dependent->name,
                'email' => $dependent->email,
                'age' => $student ? $student->age : null,
                'track' => $student ? $student->reading_track : null,
                'gender' => $student ? $student->gender : null,
                'reading_level' => $student ? $student->reading_level : null,
                'reading_type' => $student ? $student->reading_type : null,
                'plan_name' => $student ? $student->plan_name : null,
                'sessions_this_month' => $sessionsThisMonth,
                'progress_percentage' => 0, // Placeholder for now
                'next_session' => $nextSessionStr,
                'dependent_available_minutes' => $dependentAvailableMinutes,
                'has_reports' => $hasReports,
                'profile_photo_path' => $student && $student->profile_photo_path ? asset('storage/' . $student->profile_photo_path) : null,
                'phone' => $student ? $student->phone : null,
                'address' => $student ? $student->address : null,
                'qualification' => $student ? $student->qualification : null,
                'professional_status' => $student ? $student->professional_status : null,
                'birth_date' => $student ? $student->birth_date : null,
                'preferred_teacher_language' => $student ? $student->preferred_teacher_language : null,
                'memorized_amount' => $student ? $student->memorized_amount : null,
                'teacher_response_speed' => $student ? $student->teacher_response_speed : null,
                'country_id' => $student ? $student->country_id : null,
                'country' => $student && $student->country ? $student->country : null,
            ];
        });

        // Overall stats
        $totalDependents = $dependents->count();
        $totalSessionsThisMonth = $data->sum('sessions_this_month');
        $overallProgress = 0; // Placeholder

        $now = Carbon::now();
        $parentAvailableMinutes = (int) \App\Models\UserPackage::where('user_id', $user->id)
            ->whereIn('status', ['active', 'Active'])
            ->where('remaining_minutes', '>', 0)
            ->where(function ($q) use ($now) {
                $q->where('expires_at', '>', $now)
                    ->orWhereNull('expires_at');
            })
            ->sum('remaining_minutes');

        return response()->json([
            'status' => true,
            'message' => 'تم جلب التابعين بنجاح',
            'data' => [
                'dependents' => $data,
                'stats' => [
                    'total_dependents' => $totalDependents,
                    'total_sessions_this_month' => $totalSessionsThisMonth,
                    'overall_progress' => $overallProgress,
                    'parent_available_minutes' => $parentAvailableMinutes,
                ]
            ]
        ], 200);
    }

    /**
     * Create a new dependent account.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'privacy_agree' => ['required', 'accepted'],
            'phone' => ['required', 'string', 'min:6', 'max:20'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'address' => ['required', 'string', 'min:5', 'max:500'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'professional_status' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'birth_date' => ['required', 'date', 'before:today'],
            'profile_photo_path' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $parent = $request->user();

        // Check if dependent's email has been verified via OTP
        $otpRecord = \App\Models\OtpCode::where('email', $request->email)
            ->where('is_verified', true)
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'status'  => false,
                'message' => 'لم يتم تأكيد البريد الإلكتروني. يرجى إدخال رمز التحقق أولاً.',
            ], 403);
        }

        // Delete the OTP record after successful registration
        $otpRecord->delete();

        $dependentUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'parent_id' => $parent->id,
            'privacy_agree' => true,
        ]);

        $dependentUser->markEmailAsVerified();

        $countryId = $request->country_id;
        if (!$countryId) {
            // Default to parent's country or 1
            $countryId = $parent->student->country_id ?? (\App\Models\Country::first()->id ?? 1);
        }

        $photoPath = null;
        if ($request->hasFile('profile_photo_path')) {
            $photoPath = $request->file('profile_photo_path')->store('profile-photos', 'public');
        }

        $student = Student::create([
            'user_id' => $dependentUser->id,
            'country_id' => $countryId,
            'phone' => $request->phone,
            'address' => $request->address,
            'qualification' => $request->qualification,
            'professional_status' => $request->professional_status,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'profile_photo_path' => $photoPath,
        ]);

        $dependentUser->load('student');

        return response()->json([
            'status' => true,
            'message' => 'تم إضافة التابع بنجاح',
            'data' => [
                'user' => $dependentUser,
                'student_profile' => clone $dependentUser->student,
            ]
        ], 201);
    }

    /**
     * Get dependent details / Performance Report Placeholder
     */
    public function show($id)
    {
        $user = auth()->user();
        $dependent = User::where('id', $id)
            ->where('parent_id', $user->id)
            ->with(['student.country'])
            ->first();

        if (!$dependent) {
            return response()->json(['status' => false, 'message' => 'التابع غير موجود'], 404);
        }

        // Return basic info for performance report
        return response()->json([
            'status' => true,
            'message' => 'تم جلب تقرير الأداء بنجاح',
            'data' => [
                'id' => $dependent->id,
                'name' => $dependent->name,
                'progress_details' => 'قيد التطوير',
            ]
        ], 200);
    }

    /**
     * Detach a dependent to make it a normal account.
     */
    public function detach($id)
    {
        $user = auth()->user();
        
        $dependent = User::where('id', $id)
            ->where('parent_id', $user->id)
            ->first();

        if (!$dependent) {
            return response()->json([
                'status' => false,
                'message' => 'التابع غير موجود أو غير مرتبط بحسابك'
            ], 404);
        }

        $dependent->update([
            'parent_id' => null
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم فصل التابع بنجاح، وأصبح حساباً مستقلاً'
        ], 200);
    }

    /**
     * Update dependent profile by parent.
     */
    public function update(\App\Http\Requests\Student\UpdateProfileRequest $request, $id)
    {
        $parent = $request->user();
        
        $dependent = User::where('id', $id)
            ->where('parent_id', $parent->id)
            ->first();

        if (!$dependent) {
            return response()->json([
                'status' => false,
                'message' => 'التابع غير موجود أو غير مرتبط بحسابك'
            ], 404);
        }

        $student = $dependent->student;
        if (!$student) {
            return response()->json([
                'status' => false,
                'message' => 'الملف الشخصي للتابع غير موجود'
            ], 404);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();

        $oldPhotoPath = null;
        $newPhotoPath = null;

        try {
            if ($request->filled('name')) {
                $dependent->update(['name' => $request->name]);
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
                $oldPhotoPath = $student->profile_photo_path;
                $newPhotoPath = $request->file('profile_photo_path')->store('students/photos', 'public');
                $studentData['profile_photo_path'] = $newPhotoPath;
            }

            $student->update($studentData);

            \Illuminate\Support\Facades\DB::commit();

            if ($oldPhotoPath && $newPhotoPath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPhotoPath);
            }

            return response()->json([
                'status' => true,
                'message' => 'تم تحديث الملف الشخصي للتابع بنجاح.',
                'data' => [
                    'user' => $dependent->fresh(),
                    'profile' => $student->fresh(),
                    'photo_url' => $student->profile_photo_path
                        ? asset('storage/' . $student->profile_photo_path)
                        : null,
                ]
            ], 200);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            if ($newPhotoPath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($newPhotoPath);
            }

            \Illuminate\Support\Facades\Log::error('Update Dependent Profile Error', [
                'dependent_id' => $dependent->id,
                'parent_id' => $parent->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'فشل في تحديث الملف الشخصي للتابع. حاول مرة أخرى.'
            ], 500);
        }
    }
}
