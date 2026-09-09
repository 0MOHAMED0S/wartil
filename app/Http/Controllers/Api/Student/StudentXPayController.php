<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Models\UserPackage;
use App\Models\GiftCard;
use App\Services\XPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentXPayController extends Controller
{
    public function buyPackage(Request $request, XPayService $xpayService)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'coupon'     => 'nullable|string'
        ]);

        try {
            $user = auth()->user();
            $package = Package::findOrFail($request->package_id);

            $hasActive = UserPackage::where('user_id', $user->id)
                ->where('package_id', $package->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->exists();

            if ($hasActive) {
                return response()->json(['error' => 'لديك باقة نشطة بالفعل.'], 400);
            }

            $country = $user->country;
            $rate = ($country && $country->rate_to_usd > 0) ? (float) $country->rate_to_usd : 1;
            $currency = ($country && $country->currency_code) ? $country->currency_code : 'EGP';

            $basePrice = $package->egypt_price; // Default
            if ($country) {
                if ($country->region === 'egypt') $basePrice = $package->egypt_price;
                elseif ($country->region === 'arab') $basePrice = $package->arab_price;
                elseif ($country->region === 'foreign') $basePrice = $package->foreign_price;
            }

            $convertedPrice = (float) $basePrice * $rate;
            $discountAmount = 0;
            $couponId = null;
            $couponCode = $request->input('coupon');

            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)
                    ->where('status', 'active')
                    ->first();

                if (!$coupon || ($coupon->expiry_date && $coupon->expiry_date->isPast()) || ($coupon->used >= $coupon->limit)) {
                    return response()->json(['error' => 'كود الخصم غير صحيح أو منتهي الصلاحية.'], 422);
                }

                $couponId = $coupon->id;
                $discountAmount = ($convertedPrice * $coupon->percent) / 100;
            }

            $finalPriceToPay = max($convertedPrice - $discountAmount, 0);
            $finalPriceToPay = round($finalPriceToPay, 2);
            if ($finalPriceToPay <= 0) {
                return DB::transaction(function () use ($user, $package, $couponId) {
                    UserPackage::create([
                        'user_id'           => $user->id,
                        'package_id'        => $package->id,
                        'remaining_minutes' => $package->base_minutes + ($package->bonus_minutes ?? 0),
                        'expires_at'        => now()->addDays($package->validity_days),
                        'status'            => 'active'
                    ]);

                    if ($couponId) {
                        Coupon::where('id', $couponId)->increment('used');
                    }

                    return response()->json([
                        'message'      => 'تم تفعيل الباقة بنجاح مجاناً باستخدام الكوبون.',
                        'redirect_url' => route('payment.success')
                    ]);
                });
            }

            $order = Order::create([
                'user_id'    => $user->id,
                'package_id' => $package->id,
                'coupon_id'  => $couponId,
                'country_id' => optional($country)->id,
                'amount'     => $finalPriceToPay,
                'currency'   => strtoupper($currency),
                'status'     => 'pending',
            ]);

            $payment = $xpayService->createPayment($order, $user);

            // New XPay API returns 'url' directly
            if (isset($payment['url'])) {
                return response()->json([
                    'payment_url' => $payment['url'],
                    'order_id'    => $order->id,
                    'data'        => $payment
                ]);
            }

            // Fallback for old API if still active for some reason
            if (isset($payment['data']['iframe_url'])) {
                return response()->json([
                    'payment_url' => $payment['data']['iframe_url'],
                    'order_id'    => $order->id,
                    'data'        => $payment
                ]);
            }

            return response()->json([
                'error' => 'فشل في إنشاء طلب الدفع عبر XPay',
                'debug' => $payment
            ], 400);
        } catch (\Throwable $e) {
            Log::error('XPay Purchase Error: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ غير متوقع أثناء معالجة الطلب.'], 500);
        }
    }

    private function fulfillOrder($orderId, $transactionId = null)
    {
        $order = Order::with(['user', 'package', 'giftCard'])->find($orderId);

        if ($order && $order->status !== 'paid') {
            DB::transaction(function () use ($order, $transactionId) {
                $order->update([
                    'status' => 'paid',
                    'transaction_id' => $transactionId
                ]);

                if ($order->is_gift) {
                    $giftCard = $order->giftCard;
                    if ($giftCard) {
                        $couponCode = 'GFT-' . strtoupper(Str::random(6));
                        $giftCard->update([
                            'payment_status' => 'paid',
                            'coupon_code'    => $couponCode,
                        ]);
                        Log::info("Gift Order #{$order->id} paid successfully via XPay. Gift Code generated: {$couponCode}");
                    }
                } else {
                    if ($order->coupon_id) {
                        $coupon = \App\Models\Coupon::find($order->coupon_id);
                        if ($coupon) {
                            $coupon->increment('used');
                            Log::info("Coupon ID {$coupon->id} usage incremented for Order #{$order->id}");
                        }
                    }
                    $package = $order->package;
                    UserPackage::create([
                        'user_id'           => $order->user_id,
                        'package_id'        => $order->package_id,
                        'remaining_minutes' => $package->base_minutes + ($package->bonus_minutes ?? 0),
                        'expires_at'        => now()->addDays($package->validity_days),
                        'status'            => 'active'
                    ]);

                    Log::info("Order #{$order->id} fulfilled successfully via XPay.");
                }
            });

            try {
                $admins = \App\Models\User::where('role', 'admin')->get();

                if ($admins->count() > 0) {
                    $studentName = $order->user->name ?? 'طالب';
                    $packageName = $order->package->name ?? 'باقة';

                    $notificationData = [
                        'order_id'     => $order->id,
                        'student_name' => $studentName,
                        'package_name' => $packageName,
                        'amount'       => $order->amount,
                        'currency'     => $order->currency,
                    ];

                    \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\DynamicNotification(
                        'عملية شراء جديدة 💰',
                        "قام الطالب {$studentName} بشراء {$packageName} بنجاح عبر XPay.",
                        'new_order',
                        $notificationData
                    ));

                    foreach ($admins as $admin) {
                        broadcast(new \App\Events\NewOrderPaid($admin->id, $notificationData));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Admin Order Notification Error: ' . $e->getMessage());
            }
            return true;
        }
        return false;
    }

    public function handleCallback(Request $request)
    {
        $payload = $request->all();
        Log::info('XPay Webhook Callback Payload:', $payload);
        
        $orderId = null;
        
        // New API Structure: check metadata
        if (isset($payload['data']['object']['metadata']['orderId'])) {
            $orderId = $payload['data']['object']['metadata']['orderId'];
        } elseif (isset($payload['metadata']['orderId'])) {
            $orderId = $payload['metadata']['orderId'];
        }

        // Old API Structure / custom_fields fallback
        if (!$orderId && isset($payload['custom_fields'])) {
            foreach ($payload['custom_fields'] as $field) {
                $label = $field['field_label'] ?? $field['custom_field_label'] ?? '';
                $value = $field['field_value'] ?? $field['custom_field_value'] ?? null;
                if (strtolower($label) === 'order id' || strtolower($label) === 'order_id') {
                    $orderId = $value;
                    break;
                }
            }
        }

        if (!$orderId) {
            $orderId = $payload['order_id'] ?? $request->query('order_id');
        }

        // Check new API paymentStatus or old transactionStatus
        $paymentStatus = $payload['data']['object']['paymentStatus'] ?? $payload['paymentStatus'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? $payload['status'] ?? null;
        $transactionId = $payload['transaction_uuid'] ?? $payload['transaction_id'] ?? null;
        
        $isSuccessful = strtolower($paymentStatus) === 'paid' || strtoupper($transactionStatus) === 'SUCCESSFUL';
        
        if ($isSuccessful && $orderId) {
            $this->fulfillOrder($orderId, $transactionId);
        }
        return response('OK');
    }

    public function handleResponse(Request $request)
    {
        $data = $request->all();
        $status = $data['transaction_status'] ?? $data['status'] ?? null;
        $orderId = $data['order_id'] ?? $request->query('order_id');
        $transactionId = $data['transaction_uuid'] ?? null;

        if (strtoupper($status) === 'SUCCESSFUL' || strtolower($status) === 'paid' || (isset($data['session_id']) && $orderId)) {
            // We can try to fulfill the order right here if it wasn't done by the webhook yet
            if ($orderId) {
                $this->fulfillOrder($orderId, $transactionId);
            }

            return response()->json([
                'status'  => 'true',
                'message' => 'تمت عملية الدفع بنجاح',
                'data'    => [
                    'order_id'       => $orderId,
                    'transaction_id' => $transactionId
                ]
            ], 200);
        }

        Log::error('XPay Payment Response Error:', [
            'received_data' => $data,
            'status_extracted' => $status,
            'order_id_extracted' => $orderId
        ]);

        return response()->json([
            'status'          => 'false',
            'message'         => 'فشلت عملية الدفع أو تم إلغاؤها',
            'received_status' => $status
        ], 400);
    }
}
