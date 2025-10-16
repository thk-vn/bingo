<?php

use App\Http\Controllers\BingoController;
use App\Http\Controllers\BingoUserController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/admin/login', [EmployeeController::class, 'login'])->name('admin.login');

Route::prefix('bingo')->group(function () {
    Route::get('/register/index', [BingoUserController::class, 'index'])->name('bingo.register_index');
    Route::post('/register/user', [BingoUserController::class, 'register'])->name('bingo.register_user');
    Route::post('/check-user', [BingoUserController::class, 'checkUser'])->name('bingo.check_user');
    Route::get('/detail/{bingoUser}', [BingoUserController::class, 'detail'])->name('bingo.detail');
    Route::post('/update-user', [BingoUserController::class, 'update'])->name('bingo.update');

});

Route::middleware(['auth:bingo'])->group(function () {
    Route::prefix('bingo')->group(function () {
        Route::get('/number-plate', [BingoController::class, 'index'])->name('bingo.index');
        Route::get('/dial', [BingoController::class, 'dial'])->name('bingo.dial');
    });
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');
