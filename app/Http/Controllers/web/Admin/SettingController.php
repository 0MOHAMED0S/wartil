<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function toggleTeacherRegistration(Request $request)
    {
        // 1. استدعاء أو إنشاء الإعدادات
        $setting = Setting::firstOrCreate([]);

        // 2. التحقق من الـ Checkbox وتحويله للنص المناسب
        // إذا تم تحديد المربع (true) -> القيمة تصبح 'open'
        // إذا لم يتم تحديده (false) -> القيمة تصبح 'close'
        $status = $request->has('teacher_application_status') ? 'open' : 'close';

        // 3. الحفظ
        $setting->teacher_application_status = $status;
        $setting->save();

        // 4. رسالة النجاح
        $message = ($status === 'open')
            ? 'تم فتح باب التسجيل بنجاح'
            : 'تم إغلاق باب التسجيل بنجاح';

        return redirect()->back()->with('success', $message);
    }

    public function freeMinutes()
    {
        $setting = Setting::firstOrCreate([]);
        return view('dashboard.settings.free_minutes', compact('setting'));
    }

    public function updateFreeMinutes(Request $request)
    {
        $request->validate([
            'free_minutes_amount' => 'required|numeric|min:0',
            'free_minutes_validity_days' => 'required|integer|min:0',
        ]);

        $setting = Setting::firstOrCreate([]);
        $setting->free_minutes_enabled = $request->has('free_minutes_enabled');
        $setting->free_minutes_amount = $request->free_minutes_amount;
        $setting->free_minutes_validity_days = $request->free_minutes_validity_days;
        $setting->save();

        return redirect()->back()->with('success', 'تم حفظ إعدادات الدقائق المجانية بنجاح.');
    }
}
