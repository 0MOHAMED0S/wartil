<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\PackagePriceRequest;
use App\Models\Coupon;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Services\PricingEngineService;

class StudentPackageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $studentProfile = $user->studentProfile;

            if (!$studentProfile || !$studentProfile->country) {
                return response()->json([
                    'status' => false,
                    'message' => 'ملف الطالب أو بيانات الدولة غير موجودة.'
                ], 404);
            }

            $country = $studentProfile->country;
            $rate = $country->rate_to_usd ?? 1;
            $bestSellerPackageId = \Illuminate\Support\Facades\DB::table('orders')
                ->where('status', 'paid')
                ->select('package_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('package_id')
                ->orderByDesc('total')
                ->value('package_id');

            $perPage = $request->query('per_page', 10);
            $region = $country->region ?? 'foreign'; // fallback just in case
            
            $packages = Package::where('status', 'active')
                ->where('show_in_' . $region, true)
                ->paginate($perPage);

            $pricingService = app(PricingEngineService::class);
            $packages->getCollection()->transform(function ($package) use ($country, $rate, $bestSellerPackageId, $pricingService, $studentProfile) {

                $originalPriceUsd = $pricingService->getPackagePriceUsd($package, $studentProfile);
                $discountPercent = (float) ($package->discount ?? 0);

                $finalPriceUsd = ($discountPercent > 0 && $discountPercent < 100)
                    ? $originalPriceUsd * (1 - ($discountPercent / 100))
                    : $originalPriceUsd;

                $localOriginalPrice = $originalPriceUsd * $rate;
                $localFinalPrice = $finalPriceUsd * $rate;

                return [
                    'id'            => $package->id,
                    'name'          => $package->name,
                    'description'   => $package->description,

                    'base_minutes'  => $package->base_minutes,
                    'bonus_minutes' => $package->bonus_minutes,
                    'validity_days' => $package->validity_days,

                    'is_best_seller' => $package->id === $bestSellerPackageId,

                    'discount_percent'     => $discountPercent,
                    'has_discount'         => $discountPercent > 0,

                    'local_original_price' => (int) round($localOriginalPrice),
                    'local_final_price'    => (int) round($localFinalPrice),

                    'original_price_usd'   => round($originalPriceUsd, 2),
                    'final_price_usd'      => round($finalPriceUsd, 2),

                    'currency_code'   => $country->currency_code,
                    'currency_symbol' => $country->currency_symbol,

                    'display_original_price' => sprintf('%s %s', $country->currency_symbol, number_format($localOriginalPrice, 0)),
                    'display_final_price'    => sprintf('%s %s', $country->currency_symbol, number_format($localFinalPrice, 0)),
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع الباقات بنجاح.',
                'user_country' => [
                    'name'        => $country->name,
                    'currency'    => $country->currency_code,
                    'rate_to_usd' => $rate,
                ],
                'packages' => $packages
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Packages Pagination Error', [
                'user_id' => optional($request->user())->id,
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في تحميل الباقات.'
            ], 500);
        }
    }

    public function userPackages(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load('country');
            $country = $user->country;
            $rate = $country?->rate_to_usd ?? 1;

            $query = $user->packages()->with('package')->latest();
            $allUserPackages = $query->get();

            $totalRemainingMinutes = $allUserPackages->sum('remaining_minutes');
            $totalOriginalMinutes = $allUserPackages->sum(function ($up) {
                return ($up->package->base_minutes ?? 0) + ($up->package->bonus_minutes ?? 0);
            });
            $totalActiveRemainingMinutes = $allUserPackages
                ->where('status', 'active')
                ->sum('remaining_minutes');

            $perPage = $request->query('per_page', 10);
            $paginatedPackages = $query->paginate($perPage);

            $studentProfile = $user->studentProfile ?? clone $user->student; // Just to be safe
            $pricingService = app(PricingEngineService::class);

            $paginatedPackages->getCollection()->transform(function ($userPackage) use ($country, $rate, $pricingService, $studentProfile) {
                $package = $userPackage->package;

                $finalPriceUsd = $pricingService->getPackagePriceUsd($package, $studentProfile);
                $discountPercent = (float) ($package->discount ?? 0);

                $originalPriceUsd = ($discountPercent > 0 && $discountPercent < 100)
                    ? $finalPriceUsd / (1 - ($discountPercent / 100))
                    : $finalPriceUsd;

                $localOriginalPrice = $originalPriceUsd * $rate;
                $localFinalPrice = $finalPriceUsd * $rate;
                $currencySymbol = $country?->currency_symbol ?? 'ج.م';

                $purchaseDate = $userPackage->created_at ? \Carbon\Carbon::parse($userPackage->created_at) : null;

                return [
                    'id'                => $userPackage->id,
                    'remaining_minutes' => $userPackage->remaining_minutes,
                    'expires_at'        => $userPackage->expires_at,
                    'status'            => $userPackage->status,

                    'purchase_date'     => $purchaseDate ? $purchaseDate->format('Y-m-d') : null,
                    'purchase_time'     => $purchaseDate ? $purchaseDate->format('h:i A') : null,
                    'purchase_datetime' => $purchaseDate ? $purchaseDate->format('Y-m-d h:i A') : null,

                    'package' => [
                        'id'            => $package->id,
                        'name'          => $package->name,
                        'base_minutes'  => $package->base_minutes,
                        'bonus_minutes' => $package->bonus_minutes,
                        'validity_days' => $package->validity_days,
                        'description'   => $package->description,

                        'discount_percent'     => $discountPercent,
                        'has_discount'         => $discountPercent > 0,

                        'local_original_price' => (int) round($localOriginalPrice),
                        'local_final_price'    => (int) round($localFinalPrice),

                        'original_price_usd'   => round($originalPriceUsd, 2),
                        'final_price_usd'      => round($finalPriceUsd, 2),

                        'currency'        => $country?->currency_code ?? 'EGP',
                        'currency_symbol' => $currencySymbol,

                        'display_original_price' => sprintf('%s %s', $currencySymbol, number_format($localOriginalPrice, 0)),
                        'display_final_price'    => sprintf('%s %s', $currencySymbol, number_format($localFinalPrice, 0)),
                    ]
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع باقات المستخدم بنجاح.',
                'country' => $country?->name ?? 'Default (EGP)',
                'summary' => [
                    'total_original_minutes'         => $totalOriginalMinutes,
                    'total_remaining_minutes'        => $totalRemainingMinutes,
                    'total_active_remaining_minutes' => $totalActiveRemainingMinutes,
                ],
                'packages' => $paginatedPackages
            ], 200);
        } catch (\Throwable $e) {
            Log::error('User Packages Pagination Error', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء جلب باقات المستخدم.'
            ], 500);
        }
    }

    public function getPrice(PackagePriceRequest $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            $package = Package::find($id);
            if (!$package) {
                return response()->json([
                    'status' => false,
                    'message' => 'الباقة غير موجودة.'
                ], 404);
            }
            $packageFinalPriceUsd = app(PricingEngineService::class)->getPackagePriceUsd($package, $user->studentProfile ?? $user->student);
            $rate = $user->country?->rate_to_usd ?? 1;
            $convertedPrice = $packageFinalPriceUsd * $rate;

            $discountAmount = 0;
            $discountPercentage = 0;
            $couponCode = $request->input('coupon');

            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->first();

                if (!$coupon || $coupon->status !== 'active') {
                    return response()->json(['status' => false, 'message' => 'كود الخصم غير صحيح أو غير مفعل.'], 422);
                }

                if ($coupon->used >= $coupon->limit) {
                    return response()->json(['status' => false, 'message' => 'تم تجاوز الحد الأقصى لاستخدام هذا الكود.'], 422);
                }

                if ($coupon->expiry_date && $coupon->expiry_date->isPast()) {
                    return response()->json(['status' => false, 'message' => 'كود الخصم منتهي الصلاحية.'], 422);
                }

                $discountPercentage = $coupon->percent;
                $discountAmount = ($convertedPrice * $discountPercentage) / 100;
            }

            $finalPriceAfterCoupon = max($convertedPrice - $discountAmount, 0);

            return response()->json([
                'status'   => true,
                'message'  => 'تم احتساب السعر بنجاح.',
                'data'     => [
                    'package_id'       => $package->id,
                    'package_name'     => $package->name,
                    'original_price'   => round($packageFinalPriceUsd, 2),
                    'country_currency' => $user->country?->currency_code ?? 'EGP',
                    'converted_price'  => round($convertedPrice, 2),
                    'discount_percent' => $discountPercentage,
                    'discount_amount'  => round($discountAmount, 2),
                    'final_price'      => round($finalPriceAfterCoupon, 2),
                    'coupon_used'      => $couponCode,
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Package Price Error', [
                'user_id' => optional($request->user())->id,
                'package_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ ما، يرجى المحاولة لاحقاً.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
