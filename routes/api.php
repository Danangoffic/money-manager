<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BudgetController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\GoalController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| RESTful JSON API v1 — Authentication via Laravel Sanctum (Bearer Token)
|
| Base URL: /api/v1
| Auth: POST /api/v1/login, POST /api/v1/register
| All other endpoints require: Authorization: Bearer {token}
|
*/

// Public routes (no auth required)
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected routes (Sanctum token required)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Accounts
    Route::apiResource('accounts', AccountController::class);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Transactions
    Route::apiResource('transactions', TransactionController::class);

    // Budgets
    Route::apiResource('budgets', BudgetController::class)->only(['index', 'store', 'destroy']);

    // Goals
    Route::apiResource('goals', GoalController::class);
    Route::patch('/goals/{id}/progress', [GoalController::class, 'updateProgress']);
});
