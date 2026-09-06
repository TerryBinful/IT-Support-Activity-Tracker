<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityTemplateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RecurringActivityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/reminders/dismiss', [DashboardController::class, 'dismissReminder'])->name('reminders.dismiss');

    Route::post('/activities/quick', [ActivityController::class, 'quickStore'])->name('activities.quick');
    Route::post('/activities/{activity}/start', [ActivityController::class, 'start'])->name('activities.start');
    Route::post('/activities/{activity}/complete', [ActivityController::class, 'complete'])->name('activities.complete');
    Route::post('/activities/{activity}/attachments', [ActivityController::class, 'storeAttachment'])->name('activities.attachments.store');
    Route::get('/activities/{activity}/attachments/{attachment}', [ActivityController::class, 'downloadAttachment'])->name('activities.attachments.download');
    Route::delete('/activities/{activity}/attachments/{attachment}', [ActivityController::class, 'destroyAttachment'])->name('activities.attachments.destroy');
    Route::resource('activities', ActivityController::class);

    Route::get('/follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');
    Route::post('/follow-ups/{activity}/complete', [FollowUpController::class, 'complete'])->name('follow-ups.complete');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    Route::resource('templates', ActivityTemplateController::class)->except(['show']);
    Route::resource('recurring', RecurringActivityController::class)->except(['show']);
    Route::post('/recurring/{recurring}/toggle', [RecurringActivityController::class, 'toggle'])->name('recurring.toggle');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/preferences', [ReportController::class, 'savePreference'])->name('reports.preferences');
    Route::patch('/reports/preferences/{preference}', [ReportController::class, 'updatePreference'])->name('reports.preferences.update');
    Route::post('/reports/preferences/{preference}/duplicate', [ReportController::class, 'duplicatePreference'])->name('reports.preferences.duplicate');
    Route::post('/reports/preferences/{preference}/default', [ReportController::class, 'setDefaultPreference'])->name('reports.preferences.default');
    Route::delete('/reports/preferences/{preference}', [ReportController::class, 'destroyPreference'])->name('reports.preferences.destroy');
    Route::get('/reports/export/{format}', [ReportController::class, 'export'])->name('reports.export');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});
