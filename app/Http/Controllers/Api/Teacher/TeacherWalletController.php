<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherWalletController extends Controller
{
    /**
     * 1. جلب تفاصيل محفظة المعلم (الرصيد وآخر 10 طلبات سحب)
     */
    public function getWallet(Request $request)
    {
        try {
            $teacher = Teacher::where('user_id', auth()->id())->first();

            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'بيانات المعلم غير موجودة.'], 404);
            }

            $totalMinutes   = $teacher->minutes ?? 0;
            $hourlyRate     = $teacher->category ? $teacher->category->egypt_rate : ($teacher->salary ?? 0);
            $currentBalance = $teacher->balance ?? 0;

            // جلب آخر 5 طلبات للعرض السريع في المحفظة
            $recentWithdrawals = WithdrawalRequest::where('teacher_id', $teacher->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($req) {
                    return [
                        'id'             => $req->id,
                        'amount'         => (float) $req->amount,
                        'account_number' => $req->account_number,
                        'status'         => $req->status,
                        'date'           => $req->created_at->format('Y-m-d H:i'),
                    ];
                });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع بيانات المحفظة بنجاح.',
                'data'    => [
                    'wallet' => [
                        'total_minutes'   => (int) $totalMinutes,
                        'hourly_rate'     => (float) $hourlyRate,
                        'current_balance' => round($currentBalance, 2),
                        'currency'        => 'EGP',
                    ],
                    'recent_withdrawals'  => $recentWithdrawals
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Get Teacher Wallet Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ تقني: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 2. جلب جميع طلبات السحب (سجل العمليات بالكامل) مع Pagination
     */
    public function getAllRequests(Request $request)
    {
        try {
            $teacher = Teacher::where('user_id', auth()->id())->first();

            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'بيانات المعلم غير موجودة.'], 404);
            }

            // تحديد عدد العناصر في الصفحة، الافتراضي 15
            $perPage = $request->query('per_page', 15);

            $requests = WithdrawalRequest::where('teacher_id', $teacher->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // تنسيق البيانات
            $requests->getCollection()->transform(function ($req) {
                return [
                    'id'             => $req->id,
                    'amount'         => (float) $req->amount,
                    'account_number' => $req->account_number,
                    'notes'          => $req->notes,
                    'status'         => $req->status,
                    'created_at'     => $req->created_at->format('Y-m-d H:i A'),
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم جلب سجل السحوبات بنجاح.',
                'data'    => $requests
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Get All Withdrawals Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء جلب السجل.'], 500);
        }
    }

    /**
     * 3. طلب سحب رصيد مالي
     */
public function requestWithdrawal(Request $request)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1',
            'account_number' => 'required|string|min:5|max:50',
            'notes'          => 'nullable|string|max:500',
        ], [
            'amount.required'         => 'يرجى إدخال المبلغ المراد سحبه بالجنيه المصري.',
            'amount.numeric'          => 'المبلغ يجب أن يكون رقماً.',
            'amount.min'              => 'المبلغ يجب أن يكون أكبر من الصفر.',
            'account_number.required' => 'يرجى إدخال رقم الكاش أو الحساب البنكي.',
            'account_number.min'      => 'رقم الحساب قصير جداً.',
        ]);

        $teacherUser = auth()->user();
        $teacher = \App\Models\Teacher::where('user_id', $teacherUser->id)->first();

        if (!$teacher) {
            return response()->json(['status' => false, 'message' => 'بيانات المعلم غير موجودة.'], 404);
        }

        $totalMinutes   = $teacher->minutes ?? 0;
        $hourlyRate     = $teacher->category ? $teacher->category->egypt_rate : ($teacher->salary ?? 0);
        $currentBalance = $teacher->balance ?? 0;

        if (round($currentBalance, 2) < $validated['amount']) {
            return response()->json([
                'status'  => false,
                'message' => 'رصيدك الحالي لا يكفي لإتمام عملية السحب.',
                'current_balance' => round($currentBalance, 2)
            ], 400);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $teacher->decrement('balance', $validated['amount']);

            $withdrawal = \App\Models\WithdrawalRequest::create([
                'teacher_id'     => $teacher->id,
                'amount'         => $validated['amount'],
                'account_number' => $validated['account_number'],
                'notes'          => $validated['notes'] ?? null,
                'status'         => 'pending',
            ]);

            \Illuminate\Support\Facades\DB::commit();

            // ==========================================
            // 🔔 إرسال إشعار لحظي للإدارة بطلب السحب
            // ==========================================
            try {
                $admins = \App\Models\User::where('role', 'admin')->get();
                if ($admins->count() > 0) {
                    $notificationData = [
                        'withdrawal_id' => $withdrawal->id,
                        'teacher_name'  => $teacherUser->name,
                        'amount'        => $withdrawal->amount,
                        'account'       => $withdrawal->account_number
                    ];

                    // 1. الداتابيز
                    \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\DynamicNotification(
                        'طلب سحب أرباح جديد 💸',
                        "طلب المعلم {$teacherUser->name} سحب مبلغ {$withdrawal->amount} جنيه.",
                        'new_withdrawal',
                        $notificationData
                    ));

                    // 2. Pusher
                    foreach ($admins as $admin) {
                        broadcast(new \App\Events\WithdrawalRequested($admin->id, $notificationData));
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Admin Withdrawal Notification Error: ' . $e->getMessage());
            }
            // ==========================================

            $remainingBalance = $teacher->fresh()->balance ?? 0;

            return response()->json([
                'status'  => true,
                'message' => 'تم إرسال طلب السحب بنجاح وجاري مراجعته.',
                'data'    => [
                    'request_id'        => $withdrawal->id,
                    'withdrawn_amount'  => $withdrawal->amount,
                    'remaining_balance' => round($remainingBalance, 2),
                    'currency'          => 'EGP'
                ]
            ], 201);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Withdrawal Request Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage()], 500);
        }
    }

    public function cancelRequest($id)
    {
        $teacherUser = auth()->user();
        $teacher = \App\Models\Teacher::where('user_id', $teacherUser->id)->first();

        if (!$teacher) {
            return response()->json(['status' => false, 'message' => 'بيانات المعلم غير موجودة.'], 404);
        }

        $withdrawal = \App\Models\WithdrawalRequest::where('id', $id)
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$withdrawal) {
            return response()->json(['status' => false, 'message' => 'الطلب غير موجود أو لا تملك صلاحية الوصول إليه.'], 404);
        }

        if ($withdrawal->status !== 'pending') {
            return response()->json(['status' => false, 'message' => 'لا يمكن إلغاء الطلب لأن حالته الحالية: ' . $withdrawal->status], 400);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $withdrawnAmount = $withdrawal->amount; // حفظ القيمة قبل الحذف للإشعار

            $teacher->increment('balance', $withdrawnAmount);
            $withdrawal->delete();

            \Illuminate\Support\Facades\DB::commit();

            // ==========================================
            // 🔔 إرسال إشعار لحظي للإدارة بإلغاء الطلب
            // ==========================================
            try {
                $admins = \App\Models\User::where('role', 'admin')->get();
                if ($admins->count() > 0) {
                    $notificationData = [
                        'withdrawal_id' => $id,
                        'teacher_name'  => $teacherUser->name,
                        'amount'        => $withdrawnAmount,
                    ];

                    // 1. الداتابيز
                    \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\DynamicNotification(
                        'إلغاء طلب سحب 🔄',
                        "قام المعلم {$teacherUser->name} بالتراجع عن طلب السحب بقيمة {$withdrawnAmount} جنيه.",
                        'withdrawal_cancelled',
                        $notificationData
                    ));

                    // 2. Pusher
                    foreach ($admins as $admin) {
                        broadcast(new \App\Events\WithdrawalCancelled($admin->id, $notificationData));
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Admin Withdrawal Cancel Notification Error: ' . $e->getMessage());
            }
            // ==========================================

            $newBalance = $teacher->fresh()->balance ?? 0;

            return response()->json([
                'status'  => true,
                'message' => 'تم إلغاء الطلب وإرجاع الرصيد إلى محفظتك بنجاح.',
                'data'    => [
                    'current_balance' => round($newBalance, 2),
                    'currency'        => 'EGP'
                ]
            ], 200);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Cancel Withdrawal Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء محاولة إلغاء الطلب.'], 500);
        }
    }

    /**
     * تقرير مفصل لأرباح المعلم حسب منطقة الطلاب (مصر، عرب، أجانب)
     */
    public function earningsReport(Request $request)
    {
        try {
            $teacher = Teacher::where('user_id', auth()->id())->first();

            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'بيانات المعلم غير موجودة.'], 404);
            }

            // Report logic: Aggregate from call_sessions
            $callSessions = \App\Models\CallSession::where('teacher_id', $teacher->id)
                ->where('status', 'ended')
                ->select(
                    DB::raw('COALESCE(student_region, "foreign") as region'),
                    DB::raw('SUM(duration_minutes) as total_minutes'),
                    DB::raw('SUM(teacher_earnings) as total_earnings')
                )
                ->groupBy('region')
                ->get();

            // Report logic: Aggregate from slot_bookings
            // Note: slot_bookings belongs to teacher_slot_id, we need to join teacher_slots
            $slotBookings = DB::table('slot_bookings')
                ->join('teacher_slots', 'slot_bookings.teacher_slot_id', '=', 'teacher_slots.id')
                ->where('teacher_slots.teacher_id', $teacher->id)
                ->where('slot_bookings.status', 'completed')
                ->select(
                    DB::raw('COALESCE(slot_bookings.student_region, "foreign") as region'),
                    DB::raw('SUM(slot_bookings.actual_duration) as total_minutes'),
                    DB::raw('SUM(slot_bookings.teacher_earnings) as total_earnings')
                )
                ->groupBy('region')
                ->get();

            // Merge and calculate totals
            $report = [
                'egypt' => ['total_minutes' => 0, 'total_earnings' => 0.0],
                'arab' => ['total_minutes' => 0, 'total_earnings' => 0.0],
                'foreign' => ['total_minutes' => 0, 'total_earnings' => 0.0],
            ];

            foreach ($callSessions as $session) {
                $region = $session->region;
                if (!isset($report[$region])) $region = 'foreign';
                $report[$region]['total_minutes'] += (int) $session->total_minutes;
                $report[$region]['total_earnings'] += (float) $session->total_earnings;
            }

            foreach ($slotBookings as $booking) {
                $region = $booking->region;
                if (!isset($report[$region])) $region = 'foreign';
                $report[$region]['total_minutes'] += (int) $booking->total_minutes;
                $report[$region]['total_earnings'] += (float) $booking->total_earnings;
            }

            return response()->json([
                'status' => true,
                'message' => 'تم استرجاع تقرير الأرباح بنجاح.',
                'data' => [
                    'report' => $report,
                    'grand_total_minutes' => array_sum(array_column($report, 'total_minutes')),
                    'grand_total_earnings' => array_sum(array_column($report, 'total_earnings')),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Teacher Earnings Report Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ غير متوقع أثناء تحميل التقرير.'
            ], 500);
        }
    }
}
