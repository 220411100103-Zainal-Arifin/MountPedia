<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\GearController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MountainController;
use App\Http\Controllers\RentalShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::prefix('gunung')->name('mountains.')->controller(MountainController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{mountain:slug}', 'show')->name('show');
});
Route::prefix('perlengkapan')->name('gear.')->controller(GearController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{slug}', 'show')->name('show');
});
Route::prefix('sewa')->name('rental.')->controller(RentalShopController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{slug}', 'show')->name('show');
});
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->controller(LoginController::class)->group(function () {
        Route::get('/login', 'create')->name('login');
        Route::post('/login', 'store')->middleware('throttle:5,1')->name('login.store');
    });
    Route::middleware(['auth', 'admin'])->controller(DashboardController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard');
    });
});
Route::post('/admin/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');