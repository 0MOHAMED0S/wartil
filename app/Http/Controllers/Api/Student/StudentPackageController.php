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
public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 5);

        // Whether you are querying Teacher_application::with(['profile.user', 'profile.ratings.user', 'tracks'])
        // or $student->favorites()->with(['profile.user', 'profile.ratings.user', 'tracks'])
        $paginator = \App\Models\Teacher_application::where('status', 'approved')
            ->with(['profile.user', 'profile.ratings.user', 'tracks'])
            ->paginate($perPage);

        $teachers = $paginator->getCollection()->map(function ($app) {
            $profile = $app->profile;
            if ($profile) {
                $profile->loadCount('ratings');
                $profile->loadAvg('ratings', 'rating');
            }

            $user = $profile ? $profile->user : null;

            $rawPhoto = ($profile && $profile->profile_photo_path)
                ? $profile->profile_photo_path
                : $app->getRawOriginal('profile_photo_path');

            $photoUrl = $rawPhoto
                ? (filter_var($rawPhoto, FILTER_VALIDATE_URL) ? $rawPhoto : asset('storage/' . ltrim($rawPhoto, '/')))
                : null;

            $teacherId = $profile ? $profile->id : null;

            $studentsCount = $teacherId
                ? \Illuminate\Support\Facades\DB::table('call_sessions')
                    ->where('teacher_id', $teacherId)
                    ->whereNotNull('student_id')
                    ->distinct('student_id')
                    ->count('student_id')
                : 0;

            $callsCount = $teacherId
                ? \Illuminate\Support\Facades\DB::table('call_sessions')
                    ->where('teacher_id', $teacherId)
                    ->count()
                : 0;

            $slotsCount = ($teacherId && \Illuminate\Support\Facades\Schema::hasTable('slots'))
                ? \Illuminate\Support\Facades\DB::table('slots')->where('teacher_id', $teacherId)->count()
                : 0;

            $avgRating = $profile ? round((float) ($profile->ratings_avg_rating ?? $profile->average_rating ?? 0), 1) : 0;
            $reviewsCount = $profile ? (int) ($profile->ratings_count ?? $profile->ratings->count()) : 0;

            if ($profile) {
                $profile->average_rating = $avgRating;
            }

            return [
                'id'               => $profile ? $profile->id : $app->id,
                'application_id'   => $app->id,
                'name'             => $app->full_name ?? ($user ? $user->name : null),
                'photo_url'        => $photoUrl,
                'is_online'        => $profile ? (bool) $profile->is_online : false,
                'rating'           => $avgRating,
                'reviews_count'    => $reviewsCount,
                'students_count'   => $studentsCount,
                'calls_count'      => $callsCount,
                'slots_count'      => $slotsCount,
                'sessions_count'   => 0,
                'qualification'    => $app->qualification,
                'country'          => $app->origin_country,
                'languages'        => $app->languages ?? [],
                'experience_years' => (int) $app->experience_years,
                'specialties'      => $app->tracks ? $app->tracks->map(fn ($track) => [
                    'id'   => $track->id,
                    'name' => $track->name,
                ])->values() : [],
                'about'            => $app->ijazas_text,
                'reviews_details'  => ($profile && $profile->ratings)
                    ? $profile->ratings->sortByDesc('id')->map(fn ($rating) => [
                        'id'           => $rating->id,
                        'student_name' => optional($rating->user)->name,
                        'rating'       => (int) $rating->rating,
                        'comment'      => $rating->comment,
                        'date'         => optional($rating->created_at)->format('Y-m-d'),
                    ])->values()
                    : [],
                'user_data'        => $user,
                'profile_data'     => $profile,
            ];
        })->values();

        return response()->json([
            'status'  => true,
            'message' => 'تم استرجاع قائمة المعلمين بنجاح.',
            'data'    => [
                'teachers'   => $teachers,
                'pagination' => [
                    'total'        => $paginator->total(),
                    'per_page'     => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'total_pages'  => $paginator->lastPage(),
                ],
            ],
        ], 200);
    }

    public function userPackages(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load('country');
            $country = $user->country;
            $rate = $country?->rate_to_usd ?? 1;

            $ownerId = $user->accountOwner()->id;
            $userIds = array_unique([$user->id, $ownerId]);
            $query = \App\Models\UserPackage::whereIn('user_id', $userIds)
                ->orderByRaw("CASE WHEN user_id = {$user->id} THEN 1 ELSE 2 END")
                ->with('package')->latest();
            $allUserPackages = $query->get();

            // Filter out user packages whose linked package was deleted
            $validUserPackages = $allUserPackages->filter(fn($up) => $up->package !== null);

            $totalRemainingMinutes = $validUserPackages->sum('remaining_minutes');
            $totalOriginalMinutes = $validUserPackages->sum(function ($up) {
                return ($up->package->base_minutes ?? 0) + ($up->package->bonus_minutes ?? 0);
            });
            $totalActiveRemainingMinutes = $validUserPackages
                ->where('status', 'active')
                ->sum('remaining_minutes');

            $perPage = $request->query('per_page', 10);
            $paginatedPackages = $query->paginate($perPage);

            $studentProfile = $user->studentProfile ?? clone $user->student; // Just to be safe
            $pricingService = app(PricingEngineService::class);

            $paginatedPackages->getCollection()->transform(function ($userPackage) use ($country, $rate, $pricingService, $studentProfile) {
                $package = $userPackage->package;

                // Skip user packages whose linked package no longer exists
                if (! $package) {
                    return null;
                }

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
            })->filter()->values();

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
