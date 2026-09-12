<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveTeacherRequest;
use App\Models\Teacher_application;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TeacherController extends Controller
{
public function index(Request $request)
    {
        // بناء الاستعلام الأساسي
        $query = \App\Models\Teacher_application::with(['tracks', 'profile.user'])->latest();

        // 1. فلترة حسب حالة الحساب (Status) من الـ Request
        if ($request->has('status') && $request->status !== 'all' && $request->status != '') {
            $query->where('status', $request->status);
        }

        // 2. البحث (Search) الشامل في قاعدة البيانات
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('origin_country', 'like', '%' . $search . '%')
                  ->orWhereHas('profile.user', function ($qu) use ($search) {
                      $qu->where('name', 'like', '%' . $search . '%')
                         ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        // 3. حساب الإحصائيات (العدادات) لجميع البيانات في قاعدة البيانات (وليس صفحة واحدة)
        $pendingCount = \App\Models\Teacher_application::where('status', 'pending')->count();
        $approvedCount = \App\Models\Teacher_application::where('status', 'approved')->count();
        $rejectedCount = \App\Models\Teacher_application::whereIn('status', ['rejected', 'not_active'])->count();

        // 4. جلب البيانات مع الترقيم والاحتفاظ بالبحث في الرابط (withQueryString)
        $teachers = $query->paginate(10)->withQueryString();
        $categories = \App\Models\TeacherCategory::all();

        return view('dashboard.teachers', compact('teachers', 'pendingCount', 'approvedCount', 'rejectedCount', 'categories'));
    }
    public function approve(ApproveTeacherRequest $request, $id)
    {
        $application = Teacher_application::findOrFail($id);

        // التحقق من أن الطلب لم يتم قبوله مسبقاً لتجنب تكرار إنشاء الحسابات
        if ($application->status === 'approved') {
            return back()->with('error', 'هذا الطلب تم قبوله مسبقاً.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction(); // استخدام الترانزاكشن لضمان سلامة البيانات

        try {
            // 1. معالجة الصورة الشخصية (التحقق من وجودها)
            $photoPath = null;
            if ($request->hasFile('profile_photo')) {
                $photoPath = $request->file('profile_photo')
                    ->store('teachers/photos', 'public');
            } else {
                // خيار احتياطي: استخدام صورة الطلب الأصلية إذا لم يرفع الأدمن صورة جديدة
                $photoPath = $application->profile_photo_path;
            }

            // 2. إنشاء المستخدم مع تأكيد البريد الإلكتروني فوراً
            $user = User::create([
                'name'              => $application->full_name,
                'email'             => $request->email,
                'password'          => \Illuminate\Support\Facades\Hash::make($request->password),
                'role'              => 'teacher',
                'email_verified_at' => now(), // ✅ تم إضافة توثيق البريد هنا
            ]);

            // 3. إنشاء بروفايل المعلم
            $teacher = \App\Models\Teacher::create([
                'user_id'                => $user->id,
                'teacher_application_id' => $application->id,
                'category_id'            => $request->category_id,
                'salary'                 => 0,
                'profile_photo_path'     => $photoPath,
                'minutes'                => 0, // تعيين القيمة الافتراضية للدقائق
            ]);

            // 4. تحديث حالة الطلب
            $application->update([
                'status' => 'approved'
            ]);

            \Illuminate\Support\Facades\DB::commit(); // اعتماد التغييرات في قاعدة البيانات

            // 5. إرسال الإيميل والإشعارات (خارج الترانزاكشن لتجنب تأخير الاستجابة في حال بطء السيرفر)
            try {
                // إرسال الإيميل
                $category = \App\Models\TeacherCategory::find($request->category_id);
                $categoryName = $category ? $category->name : 'غير محدد';
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\TeacherApprovedMail($user, $request->password, $categoryName));

                // ==========================================
                // 🔔 إرسال إشعار ترحيبي للمعلم (يراه فور تسجيل دخوله)
                // ==========================================
                $notificationData = [
                    'teacher_id' => $teacher->id,
                    'category'   => $categoryName
                ];

                // 1. الحفظ في الداتابيز (أساسي جداً هنا)
                $user->notify(new \App\Notifications\DynamicNotification(
                    'تم قبول طلبك! 🎉',
                    'مرحباً بك في فريق المعلمين الخاص بنا. يمكنك الآن إعداد جدولك والبدء في تقديم الجلسات.',
                    'application_approved',
                    $notificationData
                ));

                // 2. إرسال البث اللحظي (في حال كان يمتلك حساب طالب مثلاً وتمت ترقيته ومسجل دخوله حالياً)
                broadcast(new \App\Events\TeacherAccountApproved($user->id, $notificationData));
                // ==========================================

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('فشل إرسال إيميل أو إشعار قبول المعلم: ' . $e->getMessage());
                // لا نوقف العملية هنا لأن الحساب تم إنشاؤه بالفعل
            }

            return back()->with('success', 'تم قبول المعلم بنجاح، إنشاء الحساب، وإرسال تفاصيل الدخول للبريد الإلكتروني');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack(); // تراجع عن كل العمليات في حال حدوث أي خطأ

            // حذف الصورة المرفوعة إذا فشلت العملية
            if ($photoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath) && $photoPath !== $application->profile_photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($photoPath);
            }

            \Illuminate\Support\Facades\Log::error('خطأ في اعتماد المعلم: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ تقني: ' . $e->getMessage());
        }
    }
    public function reject($id)
    {
        $application = Teacher_application::findOrFail($id);

        if ($application->status === 'rejected') {
            return back()->with('error', 'هذا الطلب تم رفضه مسبقاً.');
        }
        $application->update([
            'status' => 'rejected'
        ]);

        try {
            Mail::to($application->email)
                ->send(new \App\Mail\TeacherRejectedMail($application));

            return redirect()->back()->with('success', 'تم رفض طلب المعلم وإرسال بريد إلكتروني بالاعتذار.');
        } catch (\Exception $e) {
            Log::error('فشل إرسال إيميل رفض المعلم: ' . $e->getMessage());

            return redirect()->back()->with('success', 'تم رفض طلب المعلم، ولكن تعذر إرسال الإيميل التنبيهي.');
        }
    }
    public function updateDetails(Request $request, $id)
    {
        $application = Teacher_application::findOrFail($id);

        // التأكد من وجود البروفايل والمستخدم (لأننا نحدث بياناتهم)
        if (!$application->profile || !$application->profile->user) {
            return redirect()->back()->with('error', 'لا يوجد حساب مستخدم مرتبط لهذا الطلب');
        }

        $profile = $application->profile;
        $user = $profile->user;

        // التحقق من البيانات
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'password'      => 'nullable|string|min:8',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'        => 'required|in:approved,not_active',
            'category_id'   => 'required|exists:teacher_categories,id',
        ]);

        // 1. تحديث جدول المستخدمين (Login Info)
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // 2. تحديث بروفايل المعلم
        $profile->category_id = $request->category_id;
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('teachers/photos', 'public');
            $profile->profile_photo_path = $path;
        }
        $profile->save();

        // 3. تحديث حالة الطلب فقط (للسماح بالدخول أو منعه)
        // لا نقوم بتحديث الاسم أو الإيميل في جدول الطلبات للحفاظ على البيانات الأصلية
        $application->status = $request->status;
        $application->save();

        return redirect()->back()->with('success', 'تم تحديث بيانات المعلم بنجاح');
    }

    public function exportCsv(Request $request)
    {
        $query = \App\Models\Teacher_application::with(['profile.user', 'profile.category', 'tracks'])->latest();

        if ($request->has('status') && $request->status !== 'all' && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('origin_country', 'like', '%' . $search . '%')
                  ->orWhereHas('profile.user', function ($qu) use ($search) {
                      $qu->where('name', 'like', '%' . $search . '%')
                         ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        $applications = $query->get();

        $filename = "teachers_export_" . date('Ymd') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'المعرف (ID)', 
            'اسم المعلم (Name)', 
            'البريد الإلكتروني (Email)', 
            'رقم الهاتف (Phone)', 
            'النوع (Gender)',
            'بلد المنشأ (Origin Country)', 
            'مكان الإقامة (Residence)', 
            'المؤهل (Qualification)', 
            'اللغات (Languages)', 
            'سنوات الخبرة (Experience Years)',
            'ساعات العمل المتاحة (Work Hours)',
            'جودة الإنترنت (Internet Quality)',
            'المسارات (Tracks)',
            'فئة المعلم (Category)', 
            'الرصيد المالي (Financial Balance)', 
            'رصيد الدقائق (Minutes)', 
            'الراتب المتفق عليه (Agreed Salary)', 
            'الحالة (Status)', 
            'تاريخ الطلب/الانضمام (Join Date)'
        ];

        $callback = function() use($applications, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // Add BOM for UTF-8 Arabic support in Excel
            fputcsv($file, $columns);

            foreach ($applications as $app) {
                $languages = '';
                if(is_array($app->languages) || is_object($app->languages)){
                    $languages = implode(', ', (array)$app->languages);
                }

                $tracks = '';
                if($app->tracks){
                    $tracks = $app->tracks->pluck('name')->implode('، ');
                }

                $statusArr = [
                    'pending' => 'قيد المراجعة',
                    'approved' => 'نشط/مقبول',
                    'not_active' => 'غير مفعل',
                    'rejected' => 'مرفوض'
                ];
                $status = $statusArr[$app->status] ?? $app->status;
                $gender = $app->gender == 'male' ? 'ذكر' : 'أنثى';

                fputcsv($file, [
                    $app->id,
                    $app->full_name,
                    $app->email,
                    $app->phone,
                    $gender,
                    $app->origin_country,
                    $app->residence_location,
                    $app->qualification,
                    $languages,
                    $app->experience_years,
                    $app->work_hours,
                    $app->internet_quality,
                    $tracks,
                    optional(optional($app->profile)->category)->name ?? 'بدون فئة',
                    optional($app->profile)->balance ?? 0,
                    optional($app->profile)->minutes ?? 0,
                    optional($app->profile)->salary ?? 0,
                    $status,
                    $app->created_at ? $app->created_at->format('Y-m-d') : ''
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        // بناء الاستعلام مع العلاقات
        $query = Teacher_application::with('profile');

        // البحث (مطابق لنفس الفلتر في صفحة العرض)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // فلترة بالحالة (مطابق لصفحة العرض)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $teachers = $query->latest()->get();

        // تمرير البيانات لملف الـ View لتوليد الـ PDF
        $pdf = \PDF::loadView('dashboard.exports.teachers_pdf', compact('teachers'), [], [
            'title' => 'قائمة المعلمين',
            'format' => 'A4-L', // عرضي Landscape ليتسع للأعمدة
            'orientation' => 'L',
            'autoArabic' => true,
            'autoLangToFont' => true,
            'autoScriptToLang' => true
        ]);

        return $pdf->download('teachers_list_' . date('Y-m-d') . '.pdf');
    }
}
