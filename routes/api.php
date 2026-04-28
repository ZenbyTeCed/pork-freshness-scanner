<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ResultController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Scan routes
Route::middleware('auth')->group(function () {
    Route::post('/scan', [ScanController::class, 'store']);
    Route::get('/scans', [ScanController::class, 'index']);
    Route::get('/scans/{id}', [ScanController::class, 'show']);
});

// Result routes
Route::middleware('auth')->group(function () {
    Route::post('/results', [ResultController::class, 'store']);
    Route::get('/results/{scanId}', [ResultController::class, 'show']);
});