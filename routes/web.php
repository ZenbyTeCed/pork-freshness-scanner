<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\ScanController;
use App\Services\FirebaseService;

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
    Route::get('/dashboard', [ResultController::class, 'dashboard'])->name('dashboard');
    Route::view('/scan', 'pages.scan')->name('scan');
    Route::get('/history', [ResultController::class, 'history'])->name('history');
    Route::post('/history/delete', [ResultController::class, 'deleteHistory'])->name('history.delete');
    Route::view('/settings', 'pages.settings')->name('settings');
    Route::post('/auth/session', [AuthController::class, 'updateSession'])->name('auth.session.update');
    Route::get('/result/{historyId}', [ResultController::class, 'result'])->name('result');
    Route::post('/result/{historyId}/ai-insight', [ResultController::class, 'generateAiInsight'])
        ->middleware('throttle:gemini-insight')
        ->name('result.ai-insight');
    Route::post('/upload-image', [ScanController::class, 'uploadImage'])->name('upload.image');
    Route::post('/capture-esp32', [ScanController::class, 'captureEsp32'])->name('capture.esp32');
    Route::get('/api/latest-scan', [ResultController::class, 'latest']);
});

Route::get('/session-check', function () {
    return response()->json(session()->all());
});


Route::get('/test-firebase', function (FirebaseService $firebase) {
    return response()->json($firebase->getLatestScan());
});
