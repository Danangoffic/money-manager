<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class);

// Household creation (no household middleware)
Route::middleware('auth')->group(function () {
    Route::get('/household/create', [HouseholdController::class, 'create'])->name('household.create');
    Route::post('/household', [HouseholdController::class, 'store'])->name('household.store');

    // Profile (no household required)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// All authenticated routes that require a household
Route::middleware(['auth', 'verified', 'household'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Household settings
    Route::get('/household/settings', [HouseholdController::class, 'settings'])->name('household.settings');
    Route::patch('/household', [HouseholdController::class, 'update'])->name('household.update');
    Route::post('/household/invite', [HouseholdController::class, 'inviteMember'])->name('household.invite');
    Route::delete('/household/members/{userId}', [HouseholdController::class, 'removeMember'])->name('household.remove-member');
    Route::patch('/household/members/{memberId}/role', [HouseholdController::class, 'changeRole'])->name('household.change-role');

    // Accounts
    Route::resource('accounts', AccountController::class)->only(['index', 'store', 'update', 'destroy']);

    // Categories
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    // Transactions
    Route::resource('transactions', TransactionController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Recurring Transactions
    Route::resource('recurring-transactions', RecurringTransactionController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::patch('/recurring-transactions/{id}/toggle', [RecurringTransactionController::class, 'toggle'])->name('recurring-transactions.toggle');

    // Budgets
    Route::resource('budgets', BudgetController::class)->only(['index', 'store', 'destroy']);

    // Goals
    Route::resource('goals', GoalController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('/goals/{id}/progress', [GoalController::class, 'updateProgress'])->name('goals.update-progress');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Export
    Route::get('/export/transactions', [ExportController::class, 'transactionsCsv'])->name('export.transactions');
});

require __DIR__.'/auth.php';
