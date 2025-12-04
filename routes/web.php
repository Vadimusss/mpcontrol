<?php

use App\Http\Controllers\ShopController;
use App\Http\Controllers\WorkSpaceController;
use App\Http\Controllers\GoodListController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ViewStatesController;
use App\Http\Controllers\SubRowsController;
use App\Http\Controllers\GoodDetailsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

Route::resource('shops.reports', ReportController::class)
    ->only(['index'])
    ->middleware(['auth', 'verified']);

Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

Route::post('/api/{workspaceId}/{viewId}', [ViewStatesController::class, 'saveState'])
    ->middleware('auth')
    ->withoutMiddleware(['inertia']);

Route::get('/api/workspaces/{workspaceId}/goods/{goodId}/subrows', [SubRowsController::class, 'getSubRows'])
    ->middleware('auth')
    ->withoutMiddleware(['inertia']);

Route::get('/api/shops/{shop}/goods/{good}/details', [GoodDetailsController::class, 'getGoodDetails'])
    ->middleware('auth')
    ->withoutMiddleware(['inertia']);

Route::prefix('api')->group(function () {
    Route::get('notes/', [NoteController::class, 'index']);
    Route::post('notes/', [NoteController::class, 'store']);
    Route::put('notes/{note}', [NoteController::class, 'update']);
    Route::delete('notes/{note}', [NoteController::class, 'destroy']);
    Route::get('notes/isNotesExists', [NoteController::class, 'isNotesExists']);
})->middleware(['auth']);

require __DIR__ . '/auth.php';
