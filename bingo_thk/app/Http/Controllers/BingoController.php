<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class BingoController extends Controller
{
    public function index()
    {
        $bingoUser = Auth::guard('bingo')->user();

        return view('index', compact('bingoUser'));
    }

    public function dial()
    {
        return view('dial');
    }
}
