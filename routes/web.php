<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::middleware('firebase.guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
});

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('firebase.auth')->group(function () {
    Route::view('/dashboard', 'pages.dashboard')->name('dashboard');
    Route::view('/scan', 'pages.scan')->name('scan');
    Route::view('/history', 'pages.history')->name('history');
    Route::view('/settings', 'pages.settings')->name('settings');
    Route::view('/result', 'pages.result')->name('result');
});

Route::get('/session-check', function () {
    return response()->json(session()->all());
});