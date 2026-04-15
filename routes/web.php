<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
});

Route::view('/', 'pages.dashboard')->name('dashboard');
Route::view('/scan', 'pages.scan')->name('scan');
Route::view('/history', 'pages.history')->name('history');
Route::view('/reports', 'pages.reports')->name('reports');
Route::view('/settings', 'pages.settings')->name('settings');