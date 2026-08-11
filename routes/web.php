<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpendingController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/spendings', [SpendingController::class, 'index']);
