<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;

Route::get('/', function () {
    return redirect('/home');
});

Route::get('/home', [PageController::class, 'home']);

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (will be added later)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Bill Routes
    Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');
    Route::put('/bills/{bill}', [BillController::class, 'update'])->name('bills.update');
    Route::get('/bills/{bill}/expenses', [BillController::class, 'getExpenses'])->name('bills.expenses.index');
    Route::get('/bills/{bill}/balance-sheet', [BillController::class, 'getBalanceSheet'])->name('bills.balance-sheet');
    Route::post('/bills/{bill}/expenses', [BillController::class, 'storeExpense'])->name('bills.expenses.store');
    Route::put('/bills/{bill}/expenses/{expense}', [BillController::class, 'updateExpense'])->name('bills.expenses.update');
    Route::delete('/bills/{bill}/expenses/{expense}', [BillController::class, 'deleteExpense'])->name('bills.expenses.destroy');
});
