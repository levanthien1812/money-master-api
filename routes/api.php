<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\CategoriesController;
use App\Http\Controllers\Api\v1\CategoryPlansController;
use App\Http\Controllers\Api\v1\EventsController;
use App\Http\Controllers\Api\v1\GoalAdditionsController;
use App\Http\Controllers\Api\v1\GoalsController;
use App\Http\Controllers\Api\v1\MonthPlansController;
use App\Http\Controllers\Api\v1\NotificationsController;
use App\Http\Controllers\Api\v1\ReportsController;
use App\Http\Controllers\Api\v1\TransactionsController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\WalletsController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('v1')->group(function () {
    Route::post('/send-verification-code', [AuthController::class, 'sendVerificationCode'])->name('send-verification-code');

    Route::middleware('guest')->group(function () {
        // Authentication
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/verify', [AuthController::class, 'verify'])->name('verify');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
        Route::post('/forget-password', [AuthController::class, 'forgetPassword'])->name('forget-password');
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::middleware(['role:user'])->group(function () {
            // Categories
            Route::get('/categories', [CategoriesController::class, 'get'])->name('get-categories');
            Route::delete('/categories/{id}', [CategoriesController::class, 'delete'])->name('delete-category');

            // Wallets
            Route::post('/wallets', [WalletsController::class, 'create'])->name('create-wallet');
            Route::get('/wallets', [WalletsController::class, 'get'])->name('get-wallets');
            Route::patch('/wallets/{id}', [WalletsController::class, 'update'])->name('update-wallet');
            Route::delete('/wallets/{id}', [WalletsController::class, 'delete'])->name('delete-wallet');

            // Transactions
            Route::post('/transactions', [TransactionsController::class, 'create'])->name('create-transaction');
            Route::get('/transactions', [TransactionsController::class, 'get'])->name('get-transactions');
            Route::get('/transactions/years', [TransactionsController::class, 'getYears'])->name('get-transactions-years');
            Route::patch('/transactions/{id}', [TransactionsController::class, 'update'])->name('update-transactions');
            Route::delete('/transactions/{id}', [TransactionsController::class, 'delete'])->name('delete-transactions');

            // Reports
            Route::get('/reports', [ReportsController::class, 'get'])->name('get-reports');
            Route::get('/reports/export', [ReportsController::class, 'export'])->name('export-transactions');

            // Month Plans
            Route::post('/plans/month', [MonthPlansController::class, 'create'])->name('create-month-plan');
            Route::get('/plans/month', [MonthPlansController::class, 'get'])->name('get-month-plans');
            Route::get('/plans/month/years', [MonthPlansController::class, 'getYears'])->name('get-month-plans-years');
            Route::patch('/plans/month/{id}', [MonthPlansController::class, 'update'])->name('update-month-plan');
            Route::delete('/plans/month/{id}', [MonthPlansController::class, 'delete'])->name('delete-month-plan');

            // Category Plans
            Route::post('/plans/category', [CategoryPlansController::class, 'create'])->name('create-category-plan');
            Route::get('/plans/category', [CategoryPlansController::class, 'get'])->name('get-category-plans');
            Route::get('/plans/category/years', [CategoryPlansController::class, 'getYears'])->name('get-category-plans-years');
            Route::patch('/plans/category/{id}', [CategoryPlansController::class, 'update'])->name('update-category-plan');
            Route::delete('/plans/category/{id}', [CategoryPlansController::class, 'delete'])->name('delete-category-plan');

            // Goals
            Route::post('/goals', [GoalsController::class, 'create'])->name('create-goal');
            Route::get('/goals', [GoalsController::class, 'get'])->name('get-goals');
            Route::get('/goals/transferable', [GoalsController::class, 'getTransferable'])->name('get-transferable-goals');
            Route::post('/goals/{id}/transfer', [GoalsController::class, 'transferToAnotherGoal'])->name('transfer-to-another-goal');
            Route::post('/goals/{id}/return', [GoalsController::class, 'returnBackToWallet'])->name('return-back-to-wallet');
            Route::patch('/goals/{id}', [GoalsController::class, 'update'])->name('update-goal');
            Route::delete('/goals/{id}', [GoalsController::class, 'delete'])->name('delete-goal');

            // Goal additions
            Route::post('/goals/{goalId}/additions', [GoalAdditionsController::class, 'create'])->name('create-goal-addition');
            Route::get('/goals/{goalId}/additions', [GoalAdditionsController::class, 'get'])->name('get-goal-addition');

            // Events
            Route::post('/events', [EventsController::class, 'create'])->name('create-event');
            Route::get('/events', [EventsController::class, 'get'])->name('get-events');
            Route::patch('/events/{id}', [EventsController::class, 'update'])->name('update-event');
            Route::delete('/events/{id}/with-transactions', [EventsController::class, 'deleteWithTransactions'])
                ->name('delete-event-with-transactions');
            Route::delete('/events/{id}/without-transactions', [EventsController::class, 'deleteWithoutTransactions'])
                ->name('delete-event-without-transactions');
            Route::get('/events/{id}/transactions', [EventsController::class, 'getTransactions'])->name('get-transactions-by-event');

            // Users
            Route::delete('/users', [UserController::class, 'deleteUser'])->name('delete-user');
        });

        Route::middleware(['role:admin'])->group(function () {
            Route::get('/users', [UserController::class, 'getAll'])->name('get-users');
            Route::get('/users/count', [UserController::class, 'getCounts'])->name('get-users-count');
            Route::get('/users/years', [UserController::class, 'getYears'])->name('get-users-years');
            Route::delete('/users/{id}', [UserController::class, 'deleteById'])->name('delete-user-by-id');

            Route::get('/transactions/count', [TransactionsController::class, 'getCounts'])->name('get-transactions-count');

            Route::get('/categories/default', [CategoriesController::class, 'getDefault'])->name('get-default-categories');
            Route::get('/categories/default/count', [CategoriesController::class, 'getDefaultCount'])->name('get-default-count');
            Route::delete('/categories/default/{id}', [CategoriesController::class, 'deleteDefault'])->name('delete-default-categories');

            Route::get('/reports/users-per-month', [ReportsController::class, 'getUserQuantityPerMonth'])->name('get-user-quantity-per-month');
            Route::get('/reports/transactions-per-month', [ReportsController::class, 'getTransactionQuantityPerMonth'])->name('get-transaction-quantity-per-month');
        });

        // Users
        Route::patch('/users', [UserController::class, 'updateUser'])->name('update-user');
        Route::patch('/users/update-password', [UserController::class, 'updatePassword'])->name('update-password');

        Route::post('/categories', [CategoriesController::class, 'create'])->name('create-category');
        Route::patch('/categories/{id}', [CategoriesController::class, 'update'])->name('update-category');

        // Notifications
        Route::get('/notifications', [NotificationsController::class, 'get'])->name('get-notifications');
        Route::patch('/notifications/mark-as-read/{id}', [NotificationsController::class, 'markAsRead'])->name('mark-notification-read');
        Route::patch('/notifications/mark-all-as-read', [NotificationsController::class, 'markAllAsRead'])->name('mark-all-notifications-read');
        Route::delete('/notifications/{id}', [NotificationsController::class, 'delete'])->name('delete-notification');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
