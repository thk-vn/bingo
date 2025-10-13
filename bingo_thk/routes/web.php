<?php

use App\Http\Controllers\BingoController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ResgisterBingoUserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/admin/login', [EmployeeController::class, 'login'])->name('admin.login');

Route::prefix('bingo')->group(function () {
    Route::get('/resgister/index', [ResgisterBingoUserController::class, 'index'])->name('bingo.resgister_index');
    Route::post('/resgister/user', [ResgisterBingoUserController::class, 'resgister'])->name('bingo.resgister_user');
    Route::post('/check-user', [ResgisterBingoUserController::class, 'checkUser'])->name('bingo.check_user');

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
