<?php

namespace App\Http\Controllers;

class BingoController extends Controller
{
    public function index()
    {
        $userBingo = (Auth('bingo')->user());
        $userBingoName = $userBingo ? $userBingo->name : '';
        return view('index', compact('userBingoName'));
    }

    public function dial()
    {
        return view('dial');
    }
}
