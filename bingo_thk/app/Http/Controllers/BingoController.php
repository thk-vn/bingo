<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class BingoController extends Controller
{
    /**
     * Redirect view bingo
     *
     * @return View
     */
    public function index(): View
    {
        $userBingo = (Auth('bingo')->user());
        $userBingoName = $userBingo ? $userBingo->name : '';
        return view('index', compact('userBingoName'));
    }

    /**
     * Redirect view dial
     *
     * @return View
     */
    public function dial(): View
    {
        return view('dial');
    }
}
