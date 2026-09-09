<?php

namespace App\Http\Controllers\Api\Gifts;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\Order;
use App\Models\Package;
use App\Models\UserPackage;
use App\Services\PayTabsGiftService;
use App\Notifications\DynamicNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GiftController extends Controller
{
    public function buyGift(Request $request, \App\Services\XPayService $xpayService)
    {
        $request->validate([
            'package_id'     => 'required|exists:packages,id',
            'recipient_name' => 'required|string|max:255',
            'occasion'       => 'required|string',
            'message'        => 'nullable|string'
        ]);

        try {
            $user = auth()->user();
            $package = Package::findOrFail($request->package_id);
            $country = $user->country;

            $rate = ($country && $country->rate_to_usd > 0) ? (float) $country->rate_to_usd : 1;
            $currency = ($country && $country->currency_code) ? $country->currency_code : 'EGP';

            $pricingService = app(\App\Services\PricingEngineService::class);
            $basePrice = $pricingService->getPackagePriceUsd($package, $user->studentProfile);

            $allowedCurrencies = ['EGP', 'USD', 'EUR', 'GBP', 'SAR', 'AED', 'QAR', 'KWD', 'JOD', 'OMR', 'BHD', 'LYD', 'AUD', 'CAD', 'CNY'];
            if (!in_array(strtoupper($currency), $allowedCurrencies)) {
                $currency = 'EGP';
                $price = (float) $basePrice; // Revert to base price which is in EGP
            } else {
                $price = (float) $basePrice * $rate;
            }

            return DB::transaction(function () use ($user, $package, $price, $currency, $request, $xpayService, $country) {
                $giftCard = GiftCard::create([
                    'sender_id'      => $user->id,
                    'package_id'     => $package->id,
                    'minutes'        => $package->base_minutes + ($package->bonus_minutes ?? 0),
                    'price'          => $price,
                    'recipient_name' => $request->recipient_name,
                    'occasion'       => $request->occasion,
                    'message'        => $request->message,
                    'payment_status' => 'pending',
                    'status'         => 'active'
                ]);
                $order = Order::create([
                    'user_id'      => $user->id,
                    'package_id'   => $package->id,
                    'country_id'   => optional($country)->id,
                    'amount'       => $price,
                    'currency'     => strtoupper($currency),
                    'status'       => 'pending',
                    'is_gift'      => true,
                    'gift_card_id' => $giftCard->id,
                ]);

                // We pass the gift response URL to XPay
                $redirectUrl = route('api.gifts.payment.response') . '?order_id=' . $order->id . '&session_id={CHECKOUT_SESSION_ID}';
                $payment = $xpayService->createPayment($order, $user, $redirectUrl);

                if (isset($payment['url'])) {
                    return response()->json([
                        'status'      => true,
                        'payment_url' => $payment['url'],
                        'order_id'    => $order->id,
                        'data'        => $payment
                    ]);
                }

                return response()->json([
                    'error' => 'فشل في إنشاء طلب الدفع عبر XPay',
                    'debug' => $payment
                ], 400);
            });
        } catch (\Throwable $e) {
            Log::error('XPay Gift Purchase Error: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ غير متوقع أثناء تجهيز الهدية'], 500);
        }
    }
    public function handleCallback(Request $request)
    {
        $payload = $request->all();
        $orderId = $payload['cartId'] ?? $payload['cart_id'] ?? null;
        $status  = $payload['respStatus'] ?? $payload['payment_result']['response_status'] ?? null;
        if ($status === 'A' && $orderId) {
            $order = Order::with('giftCard')->find($orderId);
            if ($order && $order->status !== 'paid' && $order->is_gift) {
                DB::transaction(function () use ($order, $payload) {
                    $order->update([
                        'status' => 'paid',
                        'transaction_id' => $payload['tranRef'] ?? $payload['tran_ref'] ?? null
                    ]);
                    $giftCard = $order->giftCard;
                    if ($giftCard) {
                        $couponCode = 'GFT-' . strtoupper(Str::random(6));
                        $giftCard->update([
                            'payment_status' => 'paid',
                            'coupon_code'    => $couponCode,
                        ]);
                        Log::info("Gift Order #{$order->id} paid successfully. Gift Code generated: {$couponCode}");
                    }
                });
            }
        }
        return response('OK');
    }
    public function handleResponse(Request $request)
    {
        $data = $request->all();
        $status = $data['transaction_status'] ?? $data['status'] ?? null;
        $cartId = $data['order_id'] ?? $request->query('order_id');
        $isSuccessful = strtoupper($status) === 'SUCCESSFUL' || strtolower($status) === 'paid' || (isset($data['session_id']) && $cartId);

        if ($isSuccessful && $cartId) {
            $order = Order::with('giftCard')->find($cartId);
            
            if ($order && $order->status !== 'paid') {
                DB::transaction(function () use ($order) {
                    $order->update(['status' => 'paid']);
                    $giftCard = $order->giftCard;
                    if ($giftCard && !$giftCard->coupon_code) {
                        $couponCode = 'GFT-' . strtoupper(\Illuminate\Support\Str::random(6));
                        $giftCard->update([
                            'payment_status' => 'paid',
                            'coupon_code'    => $couponCode,
                        ]);
                        \Illuminate\Support\Facades\Log::info("Gift Order #{$order->id} paid successfully via Response fallback. Gift Code: {$couponCode}");
                    }
                });
                // Reload order after update
                $order = $order->fresh(['giftCard']);
            }
            
            $giftCard = $order ? $order->giftCard : null;
            if ($giftCard && $giftCard->coupon_code) {
                $couponCode = $giftCard->coupon_code;
                $cardLink = route('web.gifts.card.show', ['code' => $couponCode]);
                $whatsappMessage = urlencode("أهديتك باقة دقائق في التطبيق! 🎁 اضغط على الرابط لفتح هديتك واستلامها: \n {$cardLink}");
                $whatsappLink = "https://wa.me/?text={$whatsappMessage}";
                return response()->json([
                    'status'  => 'success',
                    'message' => 'تمت عملية الدفع بنجاح وتم إنشاء الهدية',
                    'data'    => [
                        'coupon_code'   => $couponCode,
                        'card_link'     => $cardLink,
                        'whatsapp_link' => $whatsappLink
                    ]
                ], 200);
            }
        }
        return response()->json([
            'status'  => 'error',
            'message' => 'فشلت عملية الدفع أو تم إلغاؤها',
            'received_status' => $status
        ], 400);
    }
    public function showGiftCard($code)
    {
        $giftCard = GiftCard::with('sender')->where('coupon_code', $code)->firstOrFail();
        $isClaimed = $giftCard->status === 'claimed';
        return view('gifts.card_view', compact('giftCard', 'isClaimed'));
    }
    public function claimGift(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string'
        ]);

        try {
            $user = auth()->user();
            $giftCard = GiftCard::with('sender')->where('coupon_code', $request->coupon_code)->first();

            if (!$giftCard) {
                return response()->json(['error' => 'كود الهدية غير صحيح أو غير موجود.'], 404);
            }

            if ($giftCard->sender_id === $user->id) {
                return response()->json(['error' => 'لا يمكنك استلام الهدية التي قمت بإرسالها بنفسك.'], 400);
            }

            if ($giftCard->payment_status !== 'paid') {
                return response()->json(['error' => 'لم يتم إكمال عملية الدفع لهذه الهدية.'], 400);
            }

            if ($giftCard->status === 'claimed') {
                return response()->json(['error' => 'عذراً، تم استخدام هذه الهدية مسبقاً.'], 400);
            }

            $hasActive = UserPackage::where('user_id', $user->id)
                ->where('package_id', $giftCard->package_id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->exists();

            if ($hasActive) {
                return response()->json(['error' => 'لديك باقة نشطة بالفعل، لا يمكنك استلام هذه الباقة الآن.'], 400);
            }

            DB::transaction(function () use ($giftCard, $user) {
                $giftCard->update([
                    'status'             => 'claimed',
                    'claimed_by_user_id' => $user->id,
                    'claimed_at'         => now(),
                ]);

                $package = Package::find($giftCard->package_id);
                UserPackage::create([
                    'user_id'           => $user->id,
                    'package_id'        => $package->id,
                    'remaining_minutes' => $package->base_minutes + ($package->bonus_minutes ?? 0),
                    'expires_at'        => now()->addDays($package->validity_days),
                    'status'            => 'active'
                ]);

                if ($giftCard->sender) {
                    $giftCard->sender->notify(new DynamicNotification(
                        'تم قبول هديتك! 🎉',
                        "قام {$user->name} بتفعيل الباقة التي أهديتها له.",
                        'gift_claimed',
                        ['gift_card_id' => $giftCard->id]
                    ));
                }
            });

            return response()->json([
                'status'  => true,
                'message' => 'مبروك! تم تفعيل الباقة المهداة بنجاح.',
                'data'    => [
                    'sender_name' => optional($giftCard->sender)->name,
                    'message'     => $giftCard->message
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Claim Gift Error: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ غير متوقع أثناء استلام الهدية'], 500);
        }
    }
    public function myGifts()
    {
        try {
            $user = auth()->user();
            $sentGifts = GiftCard::with('claimer:id,name')
                ->where('sender_id', $user->id)
                ->where('payment_status', 'paid')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($gift) {
                    return [
                        'id'             => $gift->id,
                        'recipient_name' => $gift->recipient_name,
                        'claimed_by'     => $gift->claimer ? $gift->claimer->name : null,
                        'minutes'        => $gift->minutes,
                        'occasion'       => $gift->occasion,
                        'coupon_code'    => $gift->coupon_code,
                        'status'         => $gift->status,
                        'created_at'     => $gift->created_at->format('Y-m-d h:i A'),
                        'share_link'     => url("/gifts/card/" . $gift->coupon_code),
                    ];
                });
            $receivedGifts = GiftCard::with('sender:id,name')
                ->where('claimed_by_user_id', $user->id)
                ->orderBy('claimed_at', 'desc')
                ->get()
                ->map(function ($gift) {
                    return [
                        'id'          => $gift->id,
                        'sender_name' => $gift->sender ? $gift->sender->name : 'صديق',
                        'minutes'     => $gift->minutes,
                        'occasion'    => $gift->occasion,
                        'message'     => $gift->message,
                        'claimed_at'  => $gift->claimed_at->format('Y-m-d h:i A'),
                    ];
                });
            return response()->json([
                'status' => true,
                'message' => 'تم جلب سجل الهدايا بنجاح',
                'data' => [
                    'sent'     => $sentGifts,
                    'received' => $receivedGifts,
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Get My Gifts Error: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ غير متوقع أثناء جلب الهدايا'], 500);
        }
    }
}
