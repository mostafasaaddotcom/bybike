<?php

use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('users', UserController::class)->only(['index', 'store', 'show', 'update']);

    Route::post('customers/check-phone', [CustomerController::class, 'checkPhone']);
    Route::get('customers/{customer}', [CustomerController::class, 'show']);
    Route::post('customers', [CustomerController::class, 'store']);

    Route::get('events', [EventController::class, 'index']);
    Route::post('events', [EventController::class, 'store']);

    Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus']);
});
