<?php

use App\Http\Controllers\Admin\CampsiteController as AdminCampsiteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GearController as AdminGearController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MountainController as AdminMountainController;
use App\Http\Controllers\Admin\RentalItemController as AdminRentalItemController;
use App\Http\Controllers\Admin\RentalShopController as AdminRentalShopController;
use App\Http\Controllers\Admin\TrailController as AdminTrailController;
use App\Http\Controllers\GearController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MountainController;
use App\Http\Controllers\RentalShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::prefix('gunung')->name('mountains.')->controller(MountainController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{mountain:slug}', 'show')->name('show');
    Route::get('/{mountain:slug}/cuaca', 'weather')
        ->middleware('throttle:30,1')
        ->name('weather');
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
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::prefix('gunung')->name('mountains.')->controller(AdminMountainController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{mountain:id}/edit', 'edit')->name('edit');
            Route::put('/{mountain:id}', 'update')->name('update');
            Route::delete('/{mountain:id}', 'destroy')->name('destroy');
        });
        Route::prefix('jalur')->name('trails.')->controller(AdminTrailController::class)->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{trail:id}/edit', 'edit')->name('edit');
            Route::put('/{trail:id}', 'update')->name('update');
            Route::delete('/{trail:id}', 'destroy')->name('destroy');
        });
        Route::prefix('camp')->name('campsites.')->controller(AdminCampsiteController::class)->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{campsite:id}/edit', 'edit')->name('edit');
            Route::put('/{campsite:id}', 'update')->name('update');
            Route::delete('/{campsite:id}', 'destroy')->name('destroy');
        });
        Route::prefix('gear')->name('gear.')->controller(AdminGearController::class)->group(function () {
            Route::get('/', 'index')->name('index');
        });
        Route::prefix('tempat-sewa')->name('shops.')->controller(AdminRentalShopController::class)->group(function () {
            Route::get('/', 'index')->name('index');
        });
        Route::prefix('barang-sewa')->name('items.')->controller(AdminRentalItemController::class)->group(function () {
            Route::get('/', 'index')->name('index');
        });
    });
});
Route::post('/admin/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');
