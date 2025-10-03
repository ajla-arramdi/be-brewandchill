<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;

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

// Public authentication routes (only login allowed)
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes (for Admin and Cashier only)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    
    // Role-based routes for category management (admin only)
    Route::middleware('admin')->group(function () {
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('menus', MenuController::class);
    });
    
    // User management routes (admin only - for managing cashiers)
    Route::middleware('admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });
    
    // Order management routes with appropriate permissions
    // Guest orders (no authentication required for creation)
    Route::prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']); // Guest users can create orders
    });
    
    // Authenticated order routes (admin and cashier can access)
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('/{order}', [OrderController::class, 'show']);
            Route::put('/{order}', [OrderController::class, 'update']); // Only admin can update order details (non-status fields)
            
            // Cashier-specific routes for status changes (only cashier can change status)
            Route::middleware('cashier')->group(function () {
                Route::patch('/{order}/mark-paid', [OrderController::class, 'markAsPaid']);
                Route::patch('/{order}/mark-completed', [OrderController::class, 'markAsCompleted']);
            });
        });
    });
});
