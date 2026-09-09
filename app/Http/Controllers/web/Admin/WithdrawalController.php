<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        try {
            // جلب الطلبات مع بيانات المعلم وحسابه وتاريخ سحوباته
            $requests = WithdrawalRequest::with(['teacher.user', 'teacher.withdrawalRequests' => function($q) {
                $q->latest()->take(5); // جلب آخر 5 طلبات للمعلم لعرضها في المودال
            }])
            ->latest()
            ->paginate(20);

            // إحصائيات علوية للوحة التحكم
            $stats = [
                'pending'  => WithdrawalRequest::where('status', 'pending')->count(),
                'approved' => WithdrawalRequest::where('status', 'approved')->sum('amount'), // إجمالي المبالغ المدفوعة
                'rejected' => WithdrawalRequest::where('status', 'rejected')->count(),
            ];

            return view('dashboard.withdrawals', compact('requests', 'stats'));

        } catch (\Throwable $e) {
            Log::error('Admin Withdrawals Index Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل الطلبات.');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $withdrawal = WithdrawalRequest::with('teacher')->findOrFail($id);

        // التأكد أن الطلب ما زال قيد المراجعة
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'تمت معالجة هذا الطلب مسبقاً.');
        }

        DB::beginTransaction();
        try {
            if ($validated['status'] === 'approved') {
                // في حالة الموافقة: نغير الحالة فقط (الرصيد مخصوم بالفعل مسبقاً)
                $withdrawal->update(['status' => 'approved']);
                $message = 'تمت الموافقة على الطلب بنجاح.';

            } elseif ($validated['status'] === 'rejected') {
                // في حالة الرفض: يجب إرجاع الرصيد המالي للمعلم
                $withdrawal->teacher->increment('balance', $withdrawal->amount);

                $withdrawal->update(['status' => 'rejected']);
                $message = 'تم رفض الطلب وإرجاع الرصيد لمحفظة المعلم.';
            }

            DB::commit();
            return back()->with('success', $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin Update Withdrawal Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحديث حالة الطلب.');
        }
    }
}
