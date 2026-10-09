<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Teacher\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Events\TeacherStatusChanged;
use App\Http\Requests\Teacher\UpdateTeacherProfileRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TeacherAuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = User::where('email', $validated['email'])->first();
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'بيانات الاعتماد غير صحيحة'
                ], 401);
            }
            if ($user->role !== 'teacher') {
                return response()->json([
                    'status' => false,
                    'message' => 'غير مصرح لك بالدخول من هنا. هذا التطبيق للمعلمين فقط.'
                ], 403);
            }
            $token = $user->createToken('teacher_auth_token')->plainTextToken;
            return response()->json([
                'status' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'user' => $user,
                'profile' => $user->teacherProfile,
                'token' => $token
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Teacher Login Error', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'فشل تسجيل الدخول. حاول مرة أخرى.'
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        try {
            $token = $request->user()->currentAccessToken();

            if ($token) {
                $token->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'تم تسجيل الخروج بنجاح'
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'المستخدم غير مسجل الدخول'
            ], 401);
        } catch (\Throwable $e) {
            Log::error('Teacher Logout Error', [
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'فشل تسجيل الخروج. حاول مرة أخرى.'
            ], 500);
        }
    }
    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            $user->load([
                'teacherProfile.application.tracks',
                'teacherProfile.category',
            ]);

            if (!$user->isTeacher() || !$user->teacherProfile) {
                return response()->json([
                    'status' => false,
                    'message' => 'الملف الشخصي غير مكتمل.'
                ], 403);
            }

            $category = $user->teacherProfile->category;
            $categoryData = $category ? $category->toArray() : null;

            if ($categoryData) {
                $teacherRate = $category->foreign_rate; // Default fallback
                $originCountryCode = $user->teacherProfile->application?->origin_country;
                if ($originCountryCode) {
                    $country = \App\Models\Country::where('code', $originCountryCode)->first();
                    $region = $country ? strtolower(trim($country->region)) : 'foreign';
                    if ($region === 'egypt') {
                        $teacherRate = $category->egypt_rate;
                    } elseif ($region === 'arab') {
                        $teacherRate = $category->arab_rate;
                    }
                }
                $categoryData['teacher_rate'] = $teacherRate;
            }

            return response()->json([
                'status' => true,
                'message' => 'تم جلب البيانات بنجاح',
                'data' => [
                    'user' => [
                        'id'    => $user->id,
                        'name'  => $user->name,
                        'email' => $user->email,
                    ],
                    'profile' => $user->teacherProfile,
                    'category' => $categoryData,
                    'tracks'  => $user->teacherProfile->application?->tracks ?? [],
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء جلب البيانات.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function toggleOnlineStatus(Request $request)
    {
        try {
            $user = $request->user();
            $profile = $user->teacherProfile;
            if (!$profile) {
                return response()->json([
                    'status' => false,
                    'message' => 'الملف الشخصي غير موجود.'
                ], 404);
            }
            $newStatus = !$profile->is_online;
            $profile->update([
                'is_online' => $newStatus
            ]);
            try {
                broadcast(new TeacherStatusChanged($profile->id, (bool) $newStatus));
            } catch (\Exception $e) {
                Log::error('Pusher Broadcast Error in Status Toggle: ' . $e->getMessage());
            }
            return response()->json([
                'status' => true,
                'message' => $newStatus ? 'أنت الآن متصل ومتاح لاستقبال الطلاب' : 'أنت الآن غير متصل',
                'data' => [
                    'is_online' => $profile->is_online
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Teacher Toggle Online Status Error', [
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة الاتصال.'
            ], 500);
        }
    }
    public function updateProfile(UpdateTeacherProfileRequest $request)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher || !$teacher->application) {
            return response()->json([
                'status'  => false,
                'message' => 'بيانات المعلم غير موجودة.'
            ], 404);
        }

        $application = $teacher->application;

        DB::beginTransaction();

        try {
            if ($request->filled('name')) {
                $user->update(['name' => $request->name]);
            }
            $photoPath = $teacher->profile_photo_path;
            if ($request->hasFile('profile_photo_path')) {
                if ($photoPath) {
                    Storage::disk('public')->delete($photoPath);
                }
                $photoPath = $request->file('profile_photo_path')->store('teachers/photos', 'public');
                $teacher->update(['profile_photo_path' => $photoPath]);
            }

            $cvPath = $application->cv_pdf_path;
            if ($request->hasFile('cv_pdf_path')) {
                if ($cvPath) {
                    Storage::disk('public')->delete($cvPath);
                }
                $cvPath = $request->file('cv_pdf_path')->store('teachers/cvs', 'public');
            }
            $applicationData = $request->only([
                'phone',
                'residence_location',
                'qualification',
                'experience_years',
                'work_hours',
                'online_experience',
                'internet_quality',
                'tech_skills',
                'ijazas_text'
            ]);
            if ($request->filled('name')) {
                $applicationData['full_name'] = $request->name;
            }
            if ($request->has('languages')) {
                $applicationData['languages'] = $request->languages;
            }
            $applicationData['profile_photo_path'] = $photoPath;
            $applicationData['cv_pdf_path'] = $cvPath;
            $application->update($applicationData);
            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'تم تحديث الملف الشخصي بنجاح.',
                'data'    => [
                    'user'    => $user->fresh(),
                    'teacher' => $teacher->fresh()->load('application'),
                    'photo_url' => $photoPath ? asset('storage/' . $photoPath) : null,
                ]
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Teacher Profile Update Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء تحديث البيانات.',
            ], 500);
        }
    }
}
