<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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

    public function fetchResetKey(): JsonResponse
    {
        $game = DB::table('games')->first();
        dd($game);
    }
}
