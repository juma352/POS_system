<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use Illuminate\Foundation\Application;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SaleController;
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
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('products', ProductController::class);
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::resource('sales', SaleController::class)->middleware(['auth']);
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');

});

require __DIR__.'/auth.php';
