<?php

use App\Http\Controllers\BingoController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/dial', [BingoController::class, 'dial'])->name('bingo.dial')->middleware('auth');
Route::get('/', [BingoController::class, 'index'])->name('bingo.index');
Route::get('/login', [LoginController::class, 'index'])->name('bingo.login.index');
Route::post('/login', [LoginController::class, 'login'])->name('bingo.login');