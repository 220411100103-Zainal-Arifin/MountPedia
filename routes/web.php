<?php

use App\Http\Controllers\GearController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MountainController;
use App\Http\Controllers\RentalShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/gunung', [MountainController::class, 'index'])->name('mountains.index');
Route::get('/gunung/{mountain:slug}', [MountainController::class, 'show'])->name('mountains.show');
Route::get('/perlengkapan', [GearController::class, 'index'])->name('gear.index');
Route::get('/sewa', [RentalShopController::class, 'index'])->name('rental.index');
Route::get('/sewa/{slug}', [RentalShopController::class, 'show'])->name('rental.show');