<?php

use App\Http\Controllers\Api\Ads\AdsController;
use App\Http\Controllers\Api\Country\CountryController;
use App\Http\Controllers\Api\Gifts\GiftController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\Student\favoriteController;
use App\Http\Controllers\Api\Student\PrivateCallController;
use App\Http\Controllers\Api\Student\RatingController;
use App\Http\Controllers\Api\Student\StudentAuthController;
use App\Http\Controllers\Api\Student\StudentBookingController;
use App\Http\Controllers\Api\Student\StudentBuyPackageController;
use App\Http\Controllers\Api\Student\StudentXPayController;
use App\Http\Controllers\Api\Student\StudentCallHistoryController;
use App\Http\Controllers\Api\Student\StudentPackageController;
use App\Http\Controllers\Api\Student\StudentTeacherController;
use App\Http\Controllers\Api\Student\StudentTracksController;
use App\Http\Controllers\Api\Student\StudentWalletController;
use App\Http\Controllers\Api\Teacher\ContactSettingController;
use App\Http\Controllers\Api\Teacher\TeacherAuthController;
use App\Http\Controllers\Api\Teacher\TeacherBookingController;
use App\Http\Controllers\Api\Teacher\TeacherCallHistoryController;
use App\Http\Controllers\Api\Teacher\TeacherRatingController;
use App\Http\Controllers\Api\Teacher\TeacherSessionController;
use App\Http\Controllers\Api\Teacher\TeacherSlotController;
use App\Http\Controllers\Api\Teacher\TeacherWalletController;
use App\Http\Controllers\web\User\BuyPackageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Teacher Authentication Routes
Route::prefix('teacher')->middleware('throttle:60,1')->group(function () {
    // Login (Public)
    Route::post('/login', [TeacherAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
        // Logout
        Route::post('/logout', [TeacherAuthController::class, 'logout']);
        Route::get('/ads', [AdsController::class, 'index']);
        Route::get('/profile', [TeacherAuthController::class, 'profile']);
        Route::post('/toggle-online', [TeacherAuthController::class, 'toggleOnlineStatus']);
        Route::post('/profile/update', [TeacherAuthController::class, 'updateProfile']);
        // إدارة الأوقات المتاحة
        Route::post('/slots', [TeacherSlotController::class, 'setAvailability']);
        Route::get('/my-slots', [TeacherSlotController::class, 'getMySlots']);
        Route::delete('/slots/{id}', [TeacherSlotController::class, 'deleteSlot']);
        Route::post('/slots-by-day', [TeacherSlotController::class, 'deleteDaySlots']);
        Route::post('/slots/cancel', [TeacherSlotController::class, 'cancelSlotByTeacher']);
        Route::get('/soon', [TeacherBookingController::class, 'getSoonestBooking']);

        // إدارة الحجوزات
        Route::get('/bookings', [TeacherBookingController::class, 'getTeacherBookings']);
        Route::post('/bookings/start', [TeacherBookingController::class, 'startBookedSession']);
        Route::post('/bookings/end', [TeacherBookingController::class, 'endBookedSession']);


        // جلسات التلاوة
        Route::post('/sessions/{sessionId}/start', [TeacherSessionController::class, 'startSession']);
        Route::get('/sessions/{sessionId}/attendance', [TeacherSessionController::class, 'getAttendance']);
        Route::post('/sessions/{sessionId}/end', [TeacherSessionController::class, 'endSession']);
        Route::get('/sessions/my-sessions', [TeacherSessionController::class, 'getTeacherSessions']);

        // المعلم ينضم للمكالمة
        Route::post('/call/{callId}/join', [PrivateCallController::class, 'joinCall']);
        Route::post('/call/{callId}/end', [PrivateCallController::class, 'endCall']);
        Route::get('/calls', [TeacherCallHistoryController::class, 'index']);
        Route::get('/calls/{id}', [TeacherCallHistoryController::class, 'show']);


        // محفظة المعلم
        Route::prefix('wallet')->group(function () {
            Route::get('/', [TeacherWalletController::class, 'getWallet']);
            Route::get('/earnings-report', [TeacherWalletController::class, 'earningsReport']);
            Route::post('/withdraw', [TeacherWalletController::class, 'requestWithdrawal']);
            Route::get('/requests', [TeacherWalletController::class, 'getAllRequests']);
            Route::delete('/requests/{id}/cancel', [TeacherWalletController::class, 'cancelRequest']);
        });

        //contact us
        Route::get('/contact-settings', [ContactSettingController::class, 'index']);

        // تقييمات المعلم
        Route::get('/ratings', [TeacherRatingController::class, 'index']);
    });
});

// student endpoints
Route::prefix('student')->middleware('throttle:60,1')->group(function () {

    // Login
    Route::post('/login', [StudentAuthController::class, 'login']);

    // Registration
    Route::post('/register/send-otp', [StudentAuthController::class, 'sendOtp']);
    Route::post('/register/check-otp', [StudentAuthController::class, 'checkOtp']);
    Route::post('/register/complete', [StudentAuthController::class, 'completeRegistration']);

    // Forgot Password
    Route::post('/forgot-password/send-otp', [StudentAuthController::class, 'forgotPasswordSendOtp']);
    Route::post('/forgot-password/check-otp', [StudentAuthController::class, 'checkOtp']);

    // Reset Password
    Route::post('/forgot-password/reset', [StudentAuthController::class, 'resetPassword']);

    // PayTabs Payment Routes
    Route::group(['prefix' => 'paytabs'], function () {
        Route::match(['get', 'post'], '/response', [StudentBuyPackageController::class, 'handleResponse'])->name('api.paytabs.response');
        Route::post('/callback', [StudentBuyPackageController::class, 'handleCallback'])->name('api.paytabs.callback');
    });

    // XPay Payment Routes
    Route::group(['prefix' => 'xpay'], function () {
        Route::match(['get', 'post'], '/response', [StudentXPayController::class, 'handleResponse'])->name('api.xpay.response');
        Route::post('/callback', [StudentXPayController::class, 'handleCallback'])->name('api.xpay.callback');
    });

    // Get Countries
    Route::get('/countries', [CountryController::class, 'index']);

    Route::middleware(['auth:sanctum', 'role:student'])->group(function () {

        // جلسات التلاوة
        Route::get('/sessions/available-sessions', [TeacherSessionController::class, 'getAllSessionsForStudent']);
        Route::post('/sessions/{sessionId}/join', [TeacherSessionController::class, 'joinSession']);
        Route::post('/sessions/{sessionId}/leave', [TeacherSessionController::class, 'leaveSession']);
        Route::get('/sessions/history', [TeacherSessionController::class, 'getStudentSessionHistory']);

        Route::post('/profile/update', [StudentAuthController::class, 'updateProfile']);
        Route::get('/profile', [StudentAuthController::class, 'getProfile']);
        Route::post('/change-password', [StudentAuthController::class, 'ChangePassword']);

        // Logout
        Route::post('/logout', [StudentAuthController::class, 'logout']);

        Route::get('/packages', [StudentPackageController::class, 'index']);
        Route::get('/teachers', [StudentTeacherController::class, 'index']);
        Route::get('/tracks/{trackId}/teachers', [StudentTeacherController::class, 'getTeachersByTrack']);

        Route::get('/ads', [AdsController::class, 'index']);

        // FAVORITES ROUTES
        Route::post('/favorites/toggle', [favoriteController::class, 'toggle']);
        Route::get('/favorites', [favoriteController::class, 'index']);

        Route::post('/packages/{package}/buy', [BuyPackageController::class, 'buy'])->name('packages.buy');
        Route::post('/package/buy', [StudentBuyPackageController::class, 'buyPackage']);
        Route::post('/package/buy/xpay', [StudentXPayController::class, 'buyPackage']);
        Route::get('/user-packages', [StudentPackageController::class, 'userPackages']);

        Route::get('/tracks', [StudentTracksController::class, 'index']);
        Route::get('teachers/{id}', [StudentTeacherController::class, 'show']);
        Route::post('/package/{id}/coupon', [StudentPackageController::class, 'getPrice']);
        Route::get('/{id}/available-slots', [StudentTeacherController::class, 'getTeacherAvailableSlots']);

        //المكالمات الخاصة
        Route::post('/call/start', [PrivateCallController::class, 'startCall']);
        Route::post('/call/{callId}/end', [PrivateCallController::class, 'endCall']);
        Route::get('/calls', [StudentCallHistoryController::class, 'index']);
        Route::get('/calls/{id}', [StudentCallHistoryController::class, 'show']);

        // الحجز المباشر مع المعلم
        Route::post('/book-slot', [StudentTeacherController::class, 'bookSlot']);
        Route::get('/my-bookings', [StudentBookingController::class, 'getStudentBookings']);
        Route::get('/my-bookings/upcoming', [StudentBookingController::class, 'getUpcomingBookings']);
        Route::get('/my-bookings/history', [StudentBookingController::class, 'getBookingsHistory']);

        Route::post('/bookings/join', [StudentBookingController::class, 'joinBookedSession']);
        Route::post('/cancel-booking', [StudentTeacherController::class, 'cancelBookingByStudent']);
        Route::post('/bookings/leave', [StudentBookingController::class, 'leaveBookedSession']);
        Route::post('/rate-teacher', [RatingController::class, 'store']);
        Route::get('/featured', [StudentTeacherController::class, 'featuredTeachers']);
        Route::get('/contact-settings', [ContactSettingController::class, 'index']);
        Route::get('/wallet', [StudentWalletController::class, 'getWalletSummary']);
        Route::get('/wallet/transactions', [StudentWalletController::class, 'getTransactions']);
    });
});
Route::get('payments/callback', [BuyPackageController::class, 'handleCallback']);

Route::post('/gifts/payment/callback', [GiftController::class, 'handleCallback'])
    ->name('api.gifts.payment.callback');

Route::match(['get', 'post'], '/gifts/payment/response', [GiftController::class, 'handleResponse'])
    ->name('api.gifts.payment.response');


Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/gifts/buy', [GiftController::class, 'buyGift']);
    Route::post('/gifts/claim', [GiftController::class, 'claimGift']);
    Route::get('/gifts/history', [GiftController::class, 'myGifts']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);
});
