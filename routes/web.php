<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\CustomerController as AdminCustomer;
use App\Http\Controllers\Admin\PackageController as AdminPackage;
use App\Http\Controllers\Admin\ReportController as AdminReport;
use App\Http\Controllers\Admin\SettingController as AdminSetting;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboard;

Route::get('/', function () {
    return redirect()->route('login');
});

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    
    // Customers
    Route::get('/customers', [AdminCustomer::class, 'index'])->name('customers.index');
    Route::post('/customers', [AdminCustomer::class, 'store'])->name('customers.store');
    Route::post('/customers/{customer}/balance', [AdminCustomer::class, 'addBalance'])->name('customers.balance');
    Route::delete('/customers/{customer}', [AdminCustomer::class, 'destroy'])->name('customers.destroy');

    // Packages & Assignment
    Route::get('/packages', [AdminPackage::class, 'index'])->name('packages.index');
    Route::post('/packages/sync', [AdminPackage::class, 'sync'])->name('packages.sync');
    Route::post('/packages/assign', [AdminPackage::class, 'assign'])->name('packages.assign');
    Route::delete('/packages/assignment/{assignment}', [AdminPackage::class, 'removeAssignment'])->name('packages.removeAssignment');

    // Reports & Profit
    Route::get('/reports', [AdminReport::class, 'index'])->name('reports.index');

    // Settings & API Credentials
    Route::get('/settings', [AdminSetting::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSetting::class, 'update'])->name('settings.update');
});

// Customer Routes
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboard::class, 'index'])->name('dashboard');
    Route::post('/buy', [CustomerDashboard::class, 'buyPackage'])->name('buy');
    Route::get('/orders/{order}/usage', [CustomerDashboard::class, 'getUsageInfo'])->name('orders.usage');
});
