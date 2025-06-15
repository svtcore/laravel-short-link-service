<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\LinkController as AdminLinkController;
use App\Http\Controllers\Admin\SearchController as AdminSearchController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\LinkController;
use App\Http\Controllers\User\SettingController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
                Route::put('/profile', [UserController::class, 'updateProfile'])->name('user.settings.profile');
                Route::put('/password', [UserController::class, 'updatePassword'])->name('user.settings.password');
                Route::post('/request', [SettingController::class, 'requestData'])->name('user.settings.data');
                Route::post('/deletion', [SettingController::class, 'requestDeletion'])->name('user.settings.deletion');
            });
        });
    });
});
Route::middleware(['role:admin'])->group(function () {
    Route::namespace('App\Http\Controllers\Admin')->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/', function () {
                return redirect()->route('admin.dashboard');
            });
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
                // search
            });
            Route::prefix('users')->group(function () {
                Route::get('/', action: [AdminUserController::class, 'index'])->name('admin.users.index');
                Route::get('/{id}', [AdminUserController::class, 'show'])->name('admin.users.show');
                Route::put('/{id}', [AdminUserController::class, 'update'])->name('admin.users.update');
                Route::delete('/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
                Route::put('/{id}/ban', [AdminUserController::class, 'ban'])->name('admin.users.ban');
                Route::put('/{id}/unban', [AdminUserController::class, 'active'])->name('admin.users.unban');
                Route::put('/{id}/unfreeze', [AdminUserController::class, 'active'])->name('admin.users.unfreeze');
                Route::put('/{id}/freeze', [AdminUserController::class, 'freeze'])->name('admin.users.freeze');
                // search
            });
            Route::prefix('settings')->group(function () {
                Route::get('/', action: [AdminSettingController::class, 'index'])->name('admin.settings.index');
                Route::get('/update', action: [AdminSettingController::class, 'update'])->name('admin.settings.update');
                Route::put('/profile', [AdminUserController::class, 'updateProfile'])->name('admin.profile.update');
                Route::put('/password', [AdminUserController::class, 'updatePassword'])->name('admin.password.update');
                Route::post('/maintenance', [AdminSettingController::class, 'maintenanceMode'])->name('admin.settings.maintenance');
            });

            Route::prefix('search')->group(function () {
                Route::get('/count', [AdminSearchController::class, 'count'])->name('admin.search.count');
                Route::get('/domains', [AdminSearchController::class, 'domains'])->name('admin.search.domains');
                Route::prefix('links')->group(function () {
                    Route::get('/', [AdminSearchController::class, 'links'])->name('admin.search.links');
                    Route::get('/domain/{id}', [AdminSearchController::class, 'linksByDomain'])->name('admin.search.links.byDomainId');
                    Route::get('/user', [AdminSearchController::class, 'linksByIP'])->name('admin.search.links.byUserIP');
                });
                Route::get('/users', [AdminSearchController::class, 'users'])->name('admin.search.users');
            });

        });
    });
});
Route::post('/links/store', [LinkController::class, 'store'])->name('links.store');
Route::get('/{link}', [LinkController::class, 'redirect'])->name('links.redirect');
Route::get('/', [HomeController::class, 'index'])->name('home');
