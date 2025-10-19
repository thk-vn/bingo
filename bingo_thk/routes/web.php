<?php

use App\Http\Controllers\BingoController;
use App\Http\Controllers\BingoUserController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', function(){
    return redirect()->route('bingo.resgister_index');
});

Route::get('/admin/login', [EmployeeController::class, 'login'])->name('admin.login');

Route::prefix('bingo')->group(function () {
    Route::get('/resgister/index', [BingoUserController::class, 'index'])->name('bingo.resgister_index');
    Route::post('/resgister/user', [BingoUserController::class, 'resgister'])->name('bingo.resgister_user');
    Route::post('/check-user', [BingoUserController::class, 'checkUser'])->name('bingo.check_user');
    Route::get('/detail/{bingoUser}', [BingoUserController::class, 'detail'])->name('bingo.detail');
    Route::post('/update-user', [BingoUserController::class, 'update'])->name('bingo.update');
});

Route::middleware(['auth:bingo'])->group(function () {
    Route::prefix('bingo')->group(function () {
        Route::get('/number-plate', [BingoController::class, 'index'])->name('bingo.index');
        Route::get('/dial', [BingoController::class, 'dial'])->name('bingo.dial');
        Route::post('/save-board-game', [BingoUserController::class, 'saveBoardGame'])->name('bingo.save.board_game');
        Route::get('/fetch-reset-key', [BingoController::class, 'fetchResetKey'])->name('game.fetch.reset_key');
        Route::get('/fetch-board-game', [BingoUserController::class, 'fetchBingoUserBoard'])->name('bingo.fetch.board_game');
        Route::post('/reset-board-game', [BingoUserController::class, 'resetBoardGame'])->name('bingo.reset.board_game');
    });
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');
