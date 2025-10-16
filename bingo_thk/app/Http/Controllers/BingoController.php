<?php

namespace App\Http\Controllers;

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
        return view('index');
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
