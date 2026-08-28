<?php
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
Route::middleware('guest')->group(function(){
 Route::get('/login',[AuthController::class,'showLogin'])->name('login');
 Route::post('/login',[AuthController::class,'login'])->name('login.store');
 Route::get('/register',[AuthController::class,'showRegister'])->name('register');
 Route::post('/register',[AuthController::class,'register'])->name('register.store');
});
Route::middleware('auth')->group(function(){
 Route::post('/logout',[AuthController::class,'logout'])->name('logout');
 Route::get('/',[DashboardController::class,'index'])->name('dashboard');
 Route::resource('activities',ActivityController::class)->except(['show']);
 Route::get('/reports',[ReportController::class,'index'])->name('reports.index');
 Route::post('/reports/preferences',[ReportController::class,'savePreference'])->name('reports.preferences');
 Route::get('/reports/export/{format}',[ReportController::class,'export'])->name('reports.export');
});
