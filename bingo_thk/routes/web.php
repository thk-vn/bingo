<?php

use App\Http\Controllers\BingoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BingoController::class, 'index'])->name('bingo.index');
