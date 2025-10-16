<?php

use App\Http\Controllers\Api\BingoController;
use Illuminate\Support\Facades\Route;

Route::get('/check-token', [BingoController::class, 'checkToken']);