<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpendingController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/spendings', [SpendingController::class, 'index']);
Route::get('/gmb-spending', [SpendingController::class, 'gmb']);
Route::get('/garage-spending', [SpendingController::class, 'garage']);
Route::get('/tyre-spending', [SpendingController::class, 'tyre']);
