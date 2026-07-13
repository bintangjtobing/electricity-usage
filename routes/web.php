<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Seluruh halaman data berada di balik middleware "auth". Sebelumnya semua
| route terbuka untuk publik -- siapa pun yang tahu URL-nya bisa melihat pola
| konsumsi listrik rumah ini dan menambah data sembarangan.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));

    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/purchase', 'purchase')->name('purchase');
    Route::view('/check', 'check')->name('check');
    Route::view('/history', 'history')->name('history');
    Route::view('/settings', 'settings')->name('settings');
});
