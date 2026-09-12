<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StudentsController extends Controller
{
    /**
     * عرض قائمة الطلاب مع معالجة الصور والباقات (Package +1)
     */
/**
     * عرض قائمة الطلاب مع الإحصائيات المتقدمة ومنطق الأسعار
     */
public function index(Request $request)
    {
        try {
            // 1. جلب البيانات العامة
            $allPackages = Package::where('status', 'active')->get();
            $countries = \App\Models\Country::whereHas('students')->get();

            // 2. الاستعلام الأساسي للطلاب
            $query = User::where('role', 'student')
                ->with(['student.country', 'packages.package'])
                ->latest();

            // --- 🟢 تطبيق الفلاتر والبحث من الـ Request (سيرفر سايد) ---

            // أ. البحث المفتوح (الاسم، الإيميل، الهاتف)
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhereHas('student', function ($sq) use ($search) {
                          $sq->where('phone', 'like', '%' . $search . '%');
                      });
                });
            }

            // ب. فلتر الدولة
            if ($request->filled('country') && $request->country !== 'all') {
                $country = $request->country;
                $query->whereHas('student.country', function ($q) use ($country) {
                    $q->where('name', $country);
                });
            }

            // ج. فلتر تاريخ الانضمام
            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            // د. فلتر الباقات والهدايا (من الأزرار العلوية)
            if ($request->filled('filter') && $request->filter !== 'all') {
                if ($request->filter === 'gift') {
                    $query->whereHas('packages', function ($q) {
                        $q->where('is_gift', true);
                    });
                } elseif (str_starts_with($request->filter, 'pkg-')) {
                    $pkgName = str_replace('pkg-', '', $request->filter);
                    $query->whereHas('packages.package', function ($q) use ($pkgName) {
                        $q->where('name', $pkgName);
                    });
                }
            }

            // 3. الإحصائيات الشاملة (تظل كما هي)
            $totalStudents = User::where('role', 'student')->count();
            $totalMinutesRegistered = UserPackage::where('status', 'active')->sum('remaining_minutes');
            $totalGifts = UserPackage::where('is_gift', true)->count();
            $activePackagesCount = UserPackage::where('status', 'active')->count();

            // 4. جلب تواريخ تسجيل الطلاب (للجافاسكريبت)
            $allStudentDates = User::where('role', 'student')
                ->select('created_at')
                ->pluck('created_at')
                ->map(function($date) {
                    return $date->format('Y-m-d');
                })
                ->toArray();

            // 5. الترقيم والمعالجة للعرض في الجدول
            // 🟢 (مع الاحتفاظ بالبحث في الرابط -> withQueryString)
            $studentsPaginated = $query->paginate(15)->withQueryString();

            $studentsPaginated->getCollection()->transform(function ($user) {
                $currentActive = $user->packages->where('status', 'active')->first() ?? $user->packages->first();

                return (object)[
                    'id'              => $user->id,
                    'name'            => $user->name,
                    'email'           => $user->email,
                    'phone'           => $user->student->phone ?? $user->phone ?? 'غير مسجل',
                    'avatar_initials' => mb_substr($user->name, 0, 1, 'utf-8'),
                    'created_at'      => $user->created_at->toDateTimeString(),
                    'created_at_human'=> $user->created_at->format('Y/m/d'),
                    'profile_image'   => ($user->student && $user->student->profile_photo_path) ? asset('storage/' . $user->student->profile_photo_path) : null,
                    'package_name'    => ($currentActive && $currentActive->package) ? $currentActive->package->name : 'لا يوجد باقة',
                    'country_name'    => $user->student->country->name ?? 'غير محدد',
                    'total_minutes'   => $user->packages->where('status', 'active')->sum('remaining_minutes'),
                    'gender'          => ($user->student->gender ?? '') == 'male' ? 'ذكر' : 'أنثى',
                    'qualification'   => $user->student->qualification ?? 'غير محدد',
                    'job'             => $user->student->professional_status ?? 'غير محدد',
                    'all_packages'    => $user->packages->map(function ($up) {
                        return [
                            'name'    => $up->package->name ?? 'باقة غير معروفة',
                            'remain'  => $up->remaining_minutes,
                            'is_gift' => (bool)$up->is_gift,
                            'date'    => \Carbon\Carbon::parse($up->created_at)->format('Y/m/d'),
                        ];
                    })
                ];
            });

            // 6. العرض
            return view('dashboard.students', [
                'students'               => $studentsPaginated,
                'allPackages'            => $allPackages,
                'countries'              => $countries,
                'totalStudents'          => $totalStudents,
                'totalMinutesRegistered' => $totalMinutesRegistered,
                'totalGifts'             => $totalGifts,
                'activePackagesCount'    => $activePackagesCount,
                'allStudentDates'        => $allStudentDates,
            ]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Admin Students Index Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
    /**
     * تحديث بيانات الطالب وتفاصيله الشخصية
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'phone'               => 'nullable|string|max:20',
            'qualification'       => 'nullable|string|max:255',
            'professional_status' => 'nullable|string|max:255',
            'gender'              => 'required|in:male,female',
        ]);

        try {
            $user = User::findOrFail($id);

            DB::beginTransaction();

            $user->update(['name' => $request->name]);

            $user->student()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone'               => $request->phone,
                    'qualification'       => $request->qualification,
                    'professional_status' => $request->professional_status,
                    'gender'              => $request->gender,
                ]
            );

            DB::commit();

            return redirect()->route('admin.students')->with('success', 'تم تحديث بيانات الطالب بنجاح');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Update Student Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء التحديث.');
        }
    }
    public function giftPackage(Request $request, $id)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        try {
            // 1. التأكد من أن المستخدم ليس لديه نفس هذه الباقة بحالة "نشطة" حالياً
            $alreadyHasActive = UserPackage::where('user_id', $id)
                ->where('package_id', $request->package_id)
                ->where('status', 'active')
                ->exists();

            if ($alreadyHasActive) {
                return redirect()->back()->with('error', 'هذا الطالب يمتلك هذه الباقة بالفعل وهي لا تزال نشطة.');
            }

            // 2. جلب بيانات الباقة
            $package = Package::findOrFail($request->package_id);

            // 3. إنشاء الاشتراك الجديد كهدية
            UserPackage::create([
                'user_id'           => $id,
                'package_id'        => $package->id,
                'remaining_minutes' => $package->base_minutes + $package->bonus_minutes,
                'expires_at'        => Carbon::now()->addDays($package->validity_days),
                'status'            => 'active',
                'is_gift'           => true,
            ]);

            return redirect()->back()->with('success', 'تم إهداء الباقة بنجاح! 🎁');
        } catch (\Throwable $e) {
            Log::error('Gift Package Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء عملية الإهداء.');
        }
    }

    public function exportCsv(Request $request)
    {
        $query = User::where('role', 'student')->with('student.country', 'packages.package');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('phone', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('country') && $request->country !== 'all') {
            $country = $request->country;
            $query->whereHas('student.country', function ($q) use ($country) {
                $q->where('name', $country);
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('filter') && $request->filter !== 'all') {
            if ($request->filter === 'gift') {
                $query->whereHas('packages', function ($q) {
                    $q->where('is_gift', true);
                });
            } elseif (str_starts_with($request->filter, 'pkg-')) {
                $pkgName = str_replace('pkg-', '', $request->filter);
                $query->whereHas('packages.package', function ($q) use ($pkgName) {
                    $q->where('name', $pkgName);
                });
            }
        }

        $students = $query->get();

        $filename = "students_export_" . date('Ymd') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'المعرف (ID)', 
            'اسم الطالب (Name)', 
            'البريد الإلكتروني (Email)', 
            'رقم الهاتف (Phone)', 
            'النوع (Gender)', 
            'البلد (Country)', 
            'العنوان (Address)',
            'رصيد الدقائق المتبقي (Remaining Minutes)', 
            'تاريخ التسجيل (Join Date)'
        ];

        $callback = function() use($students, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($students as $student) {
                $gender = ($student->student->gender ?? '') == 'male' ? 'ذكر' : 'أنثى';
                fputcsv($file, [
                    $student->id,
                    $student->name,
                    $student->email,
                    $student->student->phone ?? '',
                    $gender,
                    $student->student->country->name ?? '',
                    $student->student->address ?? '',
                    $student->packages->where('status', 'active')->sum('remaining_minutes') ?? 0,
                    $student->created_at ? $student->created_at->format('Y-m-d') : ''
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $query = User::where('role', 'student')->with('student.country', 'packages.package');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('phone', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('country') && $request->country !== 'all') {
            $country = $request->country;
            $query->whereHas('student.country', function ($q) use ($country) {
                $q->where('name', $country);
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('filter') && $request->filter !== 'all') {
            if ($request->filter === 'gift') {
                $query->whereHas('packages', function ($q) {
                    $q->where('is_gift', true);
                });
            } elseif (str_starts_with($request->filter, 'pkg-')) {
                $pkgName = str_replace('pkg-', '', $request->filter);
                $query->whereHas('packages.package', function ($q) use ($pkgName) {
                    $q->where('name', $pkgName);
                });
            }
        }

        $students = $query->latest()->get();

        $pdf = \PDF::loadView('dashboard.exports.students_pdf', compact('students'), [], [
            'title' => 'قائمة الطلاب',
            'format' => 'A4-L',
            'orientation' => 'L',
            'autoArabic' => true,
            'autoLangToFont' => true,
            'autoScriptToLang' => true
        ]);

        return $pdf->download('students_list_' . date('Y-m-d') . '.pdf');
    }
}
