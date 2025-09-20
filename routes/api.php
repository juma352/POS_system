<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// M-Pesa Routes
Route::post('/mpesa/stkpush', [MpesaController::class, 'stkPush']);
Route::post('/mpesa/callback', [MpesaController::class, 'mpesaCallback']);
