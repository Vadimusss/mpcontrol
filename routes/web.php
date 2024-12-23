<?php

use App\Http\Controllers\ShopController;
use App\Http\Controllers\WorkSpaceController;
use App\Http\Controllers\GoodListController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('shops.index');;
    } else {
        return redirect()->route('login');
    }
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('shops', ShopController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->middleware(['auth', 'verified']);

Route::resource('shops.workspaces', WorkSpaceController::class)
    ->only(['index', 'store', 'update', 'show', 'destroy'])
    ->middleware(['auth', 'verified']);

Route::resource('shops.goodlists', GoodListController::class)
    ->only(['index', 'store', 'show', 'update', 'destroy'])
    ->middleware(['auth', 'verified']);

require __DIR__.'/auth.php';
