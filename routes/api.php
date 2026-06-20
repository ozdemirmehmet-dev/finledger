<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'store']);
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'destroy']);

    Route::get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index']);
    Route::post('/invoices', [\App\Http\Controllers\InvoiceController::class, 'store']);

    Route::post('/receipts', [\App\Http\Controllers\ReceiptController::class, 'store']);
    Route::get('/receipts/{id}/status', [\App\Http\Controllers\ReceiptController::class, 'status']);
});
