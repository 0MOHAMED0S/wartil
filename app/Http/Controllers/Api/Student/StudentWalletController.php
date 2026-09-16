<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\UserPackage;
use App\Models\CallSession;
use App\Models\SlotBooking;
use App\Models\GiftCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentWalletController extends Controller
{
    public function getTransactions(Request $request)
    {
        $userId = auth()->id();
        $ownerId = auth()->user()->accountOwner()->id;
        
        // Include the user and their dependents (if they have any)
        $userIds = \App\Models\User::where('id', $ownerId)
            ->orWhere('parent_id', $ownerId)
            ->pluck('id')
            ->toArray();
            
        // If the current user is a dependent, only show their own calls/refunds, otherwise show all
        $targetIds = auth()->user()->parent_id ? [$userId] : $userIds;

        $calls = CallSession::whereIn('student_id', $targetIds)
            ->where('status', 'ended')
            ->with('student:id,name')
            ->select('id', 'student_id', 'duration_minutes as minutes', 'started_at as date')
            ->get();
        $refunds = SlotBooking::whereIn('user_id', $targetIds)
            ->where('status', 'cancelled')
            ->with('user:id,name')
            ->where('deducted_minutes', '>', 0)
            ->select('id', 'user_id', 'deducted_minutes as minutes', 'updated_at as date')
            ->get();
        $additions = UserPackage::where('user_id', $ownerId)
            ->with('package:id,name,base_minutes,bonus_minutes')
            ->select('id', 'package_id', 'created_at as date')
            ->get();
        $receivedGifts = GiftCard::where('claimed_by_user_id', $userId)
            ->where('status', 'claimed')
            ->with('sender:id,name')
            ->select('id', 'minutes', 'occasion', 'sender_id', 'claimed_at as date')
            ->get();
        $transactions = collect();
        foreach ($calls as $call) {
            $subtitle = 'استهلاك رصيد دقائق';
            if ($call->student_id != $userId && $call->student) {
                $subtitle .= ' (التابع: ' . $call->student->name . ')';
            }
            $transactions->push($this->mapTransaction($call, 'out', 'جلسة تعليمية مباشرة', $subtitle, 'call'));
        }

        foreach ($refunds as $refund) {
            $subtitle = 'تمت إعادة الدقائق لمحفظتك';
            if ($refund->user_id != $userId && $refund->user) {
                $subtitle .= ' (التابع: ' . $refund->user->name . ')';
            }
            $transactions->push($this->mapTransaction($refund, 'in', 'مستردات حجز ملغي', $subtitle, 'refund'));
        }

        foreach ($additions as $addition) {
            $totalMins = ($addition->package->base_minutes ?? 0) + ($addition->package->bonus_minutes ?? 0);
            $transactions->push($this->mapTransaction($addition, 'in', 'شراء باقة: ' . ($addition->package->name ?? 'باقة دقائق'), 'عملية شحن ناجحة', 'purchase', $totalMins));
        }
        foreach ($receivedGifts as $gift) {
            $transactions->push($this->mapTransaction($gift, 'in', 'هدية مستلمة', 'من: ' . ($gift->sender->name ?? 'فاعل خير'), 'gift_received'));
        }
        $sortedData = $transactions->sortByDesc('sort_date')->values()->map(function ($item) {
            unset($item['sort_date']);
            return $item;
        })->toArray();

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب سجل العمليات بنجاح.',
            'data'    => $sortedData
        ]);
    }
    private function mapTransaction($item, $type, $title, $subtitle, $icon, $customMinutes = null)
    {
        $parsedDate = Carbon::parse($item->date ?? $item->created_at ?? now());
        return [
            'id'        => $item->id,
            'title'     => $title,
            'subtitle'  => $subtitle,
            'minutes'   => ($type == 'out' ? '-' : ($type == 'in' ? '+' : '')) . ($customMinutes ?? $item->minutes ?? 0) . " دقيقة",
            'type'      => $type,
            'datetime'  => $parsedDate->format('Y-m-d h:i A'),
            'sort_date' => $parsedDate->timestamp,
            'icon_type' => $icon
        ];
    }
    public function getWalletSummary()
    {
        $userId = auth()->id();
        $ownerId = auth()->user()->accountOwner()->id;
        $now = Carbon::now();
        $userIds = array_unique([$userId, $ownerId]);
        
        $activePackages = UserPackage::with('package:id,name,base_minutes,bonus_minutes')
            ->whereIn('user_id', $userIds)
            ->whereIn('status', ['active', 'Active'])
            ->where(function ($q) use ($now) {
                $q->where('expires_at', '>', $now)
                    ->orWhereNull('expires_at');
            })
            ->get();
        $availableMinutes = $activePackages->sum('remaining_minutes');
        $totalOriginalMinutes = $activePackages->sum(function ($up) {
            $pkgTotal = ($up->package->base_minutes ?? 0) + ($up->package->bonus_minutes ?? 0);
            return max($pkgTotal, $up->remaining_minutes);
        });
        $expiredMinutes = UserPackage::whereIn('user_id', $userIds)
            ->where(function ($q) use ($now) {
                $q->where('status', 'expired')
                    ->orWhere('expires_at', '<=', $now);
            })
            ->where('remaining_minutes', '>', 0)
            ->sum('remaining_minutes');
        $totalUsedMinutes = CallSession::where('student_id', $userId)
            ->where('status', 'ended')
            ->sum('duration_minutes');
        $learningHours = floor($totalUsedMinutes / 60);
        $learningMinutesRemaining = $totalUsedMinutes % 60;
        $myPackages = UserPackage::with('package:id,name,base_minutes,bonus_minutes')
            ->whereIn('user_id', $userIds)
            ->orderByRaw("CASE WHEN user_id = {$userId} THEN 1 ELSE 2 END")
            ->latest()
            ->get()
            ->map(function ($up) use ($now) {
                $pkgTotal = ($up->package->base_minutes ?? 0) + ($up->package->bonus_minutes ?? 0);
                $original = max($pkgTotal, $up->remaining_minutes);
                return [
                    'package_name'     => $up->package->name ?? 'باقة مخصصة',
                    'remaining'        => $up->remaining_minutes,
                    'total_original'   => $original,
                    'text_format'      => "{$up->remaining_minutes} دقيقة من أصل {$original}",
                    'is_expired'       => $up->expires_at ? Carbon::parse($up->expires_at)->isPast() : false,
                    'expiry_date'      => $up->expires_at ? $up->expires_at : 'لا تنتهي',
                    'status'           => $up->status,
                ];
            });
        return response()->json([
            'status' => true,
            'message' => 'تم استرجاع بيانات المحفظة بنجاح.',
            'data' => [
                'summary' => [
                    'available_minutes'      => (int) $availableMinutes,
                    'total_original_minutes' => (int) $totalOriginalMinutes,
                    'balance_text'           => "الرصيد المتبقي {$availableMinutes} من أصل {$totalOriginalMinutes} دقيقة",
                    'used_minutes'           => (int) $totalUsedMinutes,
                    'expired_minutes'        => (int) $expiredMinutes,
                ],
                'learning_stats' => [
                    'total_hours'   => (int) $learningHours,
                    'total_minutes' => (int) $totalUsedMinutes,
                    'formatted'     => "{$learningHours} ساعة و {$learningMinutesRemaining} دقيقة",
                ],
                'packages_details' => $myPackages
            ]
        ]);
    }
}
