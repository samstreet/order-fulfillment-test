<?php

declare(strict_types=1);

use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Order management routes
Route::apiResource('orders', OrderController::class)->except(['update']);

// Custom route for updating order status
Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])
    ->name('orders.update-status');