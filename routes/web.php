<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\LinkController;
use App\Http\Controllers\User\SettingController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LinkController as AdminLinkController;
use App\Http\Controllers\Admin\DomainController;

Auth::routes();

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
Route::middleware(['role:admin'])->group(function () {
    Route::namespace('App\Http\Controllers\Admin')->group(function () {
        Route::prefix('admin')->group(function () {
            Route::prefix('dashboard')->group(function () {
                Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
                Route::get('/show', [AdminDashboardController::class, 'show'])->name('admin.dashboard.show');
            });
            Route::prefix('domains')->group(function () {
                Route::get('/', [DomainController::class, 'index'])->name('admin.domains.index');
                Route::post('/', [DomainController::class, 'store'])->name('admin.domains.store');
                Route::put('/{id}', [DomainController::class, 'update'])->name('admin.domains.update');
                Route::delete('/{id}', [DomainController::class, 'destroy'])->name('admin.domains.destroy');
            });
            Route::prefix('links')->group(function () {
                Route::get('/', [AdminLinkController::class, 'index'])->name('admin.links.index');
                Route::get('/{id}', [AdminLinkController::class, 'show'])->name('admin.links.show');
                Route::post('/', [AdminLinkController::class, 'store'])->name('admin.links.store');
                Route::put('/{id}', [AdminLinkController::class, 'update'])->name('admin.links.update');
                Route::delete('/{id}', [AdminLinkController::class, 'destroy'])->name('admin.links.destroy');
            });
        });
    });
});
Route::post('/links/store', [LinkController::class, 'store'])->name('links.store');
Route::get('/{link}', [LinkController::class, 'redirect'])->name('links.redirect');
Route::get('/', [HomeController::class, 'index'])->name('home');

