<?php

use App\Http\Controllers\AdScheduleController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\AnalyticsEventController;
use App\Http\Controllers\DisplayAdvertisementController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('advertisements', AdvertisementController::class);
    Route::resource('displays', DisplayController::class);
    Route::resource('display-advertisements', DisplayAdvertisementController::class);
    Route::resource('ad-schedules', AdScheduleController::class);
    Route::resource('menus', MenuController::class);
    Route::resource('menu-items', MenuItemController::class);
    Route::resource('users', UserController::class);
    Route::resource('analytics-events', AnalyticsEventController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
