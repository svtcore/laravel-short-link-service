<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\LinkController;
use App\Http\Controllers\User\SettingController;
use App\Http\Controllers\User\UserController;

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/test', [HomeController::class, 'test'])->name('test');
Route::post('/links/store', [LinkController::class, 'store'])->name('links.store');

Route::middleware(['role:user'])->group(function () {
    Route::namespace('App\Http\Controllers\User')->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
            Route::prefix('links')->group(function () {
                Route::get('/', [LinkController::class, 'index'])->name('user.links.index');
                Route::get('/show', [LinkController::class, 'show'])->name('user.links.show');
                Route::get('{id}/edit', [LinkController::class, 'edit'])->name('user.links.edit');
                Route::put('{id}', [LinkController::class, 'update'])->name('user.links.update');
                Route::delete('{id}', [LinkController::class, 'destroy'])->name('user.links.destroy');
            });
            Route::prefix('settings')->group(function () {
                Route::get('/', [SettingController::class, 'index'])->name('user.settings.index');
                Route::put('/profile', [UserController::class, 'update_profile'])->name('user.settings.profile');
                Route::put('/password', [UserController::class, 'update_password'])->name('user.settings.password');
                Route::post('/request', [SettingController::class, 'request_data'])->name('user.settings.data');
                Route::post('/deletion', [SettingController::class, 'request_deletion'])->name('user.settings.deletion');
            });
        });
    });
});