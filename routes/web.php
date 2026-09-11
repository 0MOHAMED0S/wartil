<?php

use App\Http\Controllers\Api\Gifts\GiftController;
use App\Http\Controllers\web\Admin\AdminBookingController;
use App\Http\Controllers\web\Admin\AdminCallSessionController;
use App\Http\Controllers\web\Admin\AdsController;
use App\Http\Controllers\web\Admin\AuthController;
use App\Http\Controllers\web\Admin\ContactSettingController;
use App\Http\Controllers\web\Admin\CountryController;
use App\Http\Controllers\web\Admin\CouponsController;
use App\Http\Controllers\web\Admin\DashboardController;
use App\Http\Controllers\web\Admin\PackageController;
use App\Http\Controllers\web\Admin\ProfileController;
use App\Http\Controllers\web\Admin\RecitationSessionController;
use App\Http\Controllers\web\Admin\SettingController;
use App\Http\Controllers\web\Admin\StudentsController;
use App\Http\Controllers\web\Admin\SubscriptionsController;
use App\Http\Controllers\web\Admin\TeacherController;
use App\Http\Controllers\web\Admin\TrackController as AdminTrackController;
use App\Http\Controllers\web\User\ContactController;
use App\Http\Controllers\web\User\MainController;
use App\Http\Controllers\web\User\TeacherApplicationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\web\Admin\WithdrawalController;

Route::get('/payment/success', function () {
    return view('payments.success');
})->name('payment.success');

Route::get('/close', function () {
    return view('main.close');
})->name('close.index');

Route::get('/msa', function () {
    return view('dashboard.teachers', [
        'teachers' => \App\Models\Teacher_application::paginate(10)
    ]);
});
Route::get('/teacher', [TeacherApplicationController::class, 'index'])->name('teacher.index');
Route::get('/', [MainController::class, 'index'])->name('welcome');


Route::prefix('admin')->group(function () {

    Route::get('/login', fn() => view('dashboard.login'))->name('admin.login');

    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');

    Route::middleware('admin')->group(function () {

        Route::get('/recitations/create', [RecitationSessionController::class, 'create'])->name('admin.recitations.create');
        Route::post('/recitations', [RecitationSessionController::class, 'store'])->name('admin.recitations.store');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('settings/toggle-registration', [SettingController::class, 'toggleTeacherRegistration'])->name('settings.toggleRegistration');
        Route::get('settings/free-minutes', [SettingController::class, 'freeMinutes'])->name('settings.freeMinutes');
        Route::post('settings/free-minutes', [SettingController::class, 'updateFreeMinutes'])->name('settings.updateFreeMinutes');
        Route::post('/teachers/{id}/update-details', [TeacherController::class, 'updateDetails'])->name('teacher.updateDetails');
        Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

        Route::resource('tracks', AdminTrackController::class);
        Route::resource('teachers', TeacherController::class);
        Route::resource('coupons', CouponsController::class);
        Route::put('/settings/contact', [ContactSettingController::class, 'updateContactSettings'])->name('settings.contact.update');

        Route::post('/teachers/{id}/approve', [TeacherController::class, 'approve'])->name('teacher.approve');
        Route::post('/teachers/{id}/reject', [TeacherController::class, 'reject'])->name('teacher.reject');
        Route::resource('packages', PackageController::class);
        Route::resource('ads', AdsController::class)->except(['create', 'show', 'edit']);
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');

        Route::get('/students', [StudentsController::class, 'index'])->name('admin.students');
        Route::put('/students/{id}', [StudentsController::class, 'update'])->name('admin.students.update');
        Route::post('/students/gift/{id}', [StudentsController::class, 'giftPackage'])->name('admin.students.gift');
        Route::get('/Subscriptions', [SubscriptionsController::class, 'index'])->name('admin.subscriptions');
        Route::put('/teachers/{teacher}/tracks', [AdminTrackController::class, 'updateTeacherTracks'])->name('teachers.tracks.update');
        Route::get('/countries', [CountryController::class, 'index'])->name('countries.index');
        Route::put('/countries/{country}/toggle-status', [CountryController::class, 'toggleStatus'])->name('countries.toggle_status');
        Route::put('/recitations/{id}', [RecitationSessionController::class, 'update'])->name('admin.recitations.update');
        Route::delete('/recitations/{id}', [RecitationSessionController::class, 'destroy'])->name('admin.recitations.destroy');
        Route::get('/calls', [AdminCallSessionController::class, 'index'])->name('calls.index');
        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('admin.bookings.index');

        Route::get('/notifications', [\App\Http\Controllers\web\Admin\AdminNotificationController::class, 'index'])->name('admin.notifications.index');
        Route::post('/notifications/read-all', [\App\Http\Controllers\web\Admin\AdminNotificationController::class, 'markAllAsRead'])->name('admin.notifications.readAll');
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\web\Admin\AdminNotificationController::class, 'markAsRead'])->name('admin.notifications.read');
        Route::post('/notifications/broadcast', [\App\Http\Controllers\web\Admin\AdminNotificationController::class, 'broadcast'])->name('admin.notifications.broadcast');

        Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('admin.withdrawals.index');
        Route::put('/withdrawals/{id}/status', [WithdrawalController::class, 'updateStatus'])->name('admin.withdrawals.update_status');

        Route::resource('teacher-categories', \App\Http\Controllers\web\Admin\TeacherCategoryController::class)->except(['create', 'show', 'edit']);
        Route::get('/teachers-export', [TeacherController::class, 'exportCsv'])->name('admin.teachers.export');
        Route::get('/students-export', [StudentsController::class, 'exportCsv'])->name('admin.students.export');
    });
});

Route::post('/teacher-apply', [TeacherApplicationController::class, 'store'])
    ->name('teacher.apply');

Route::post('/contact-us', [ContactController::class, 'sendEmail'])->name('contact.send');

Route::match(['get', 'post'], '/gifts/payment/response', [GiftController::class, 'handleResponse'])->name('web.gifts.payment.response');
Route::get('/gifts/card/{code}', [GiftController::class, 'showGiftCard'])->name('web.gifts.card.show');

Route::view('/policies', 'policies')->name('policies');
