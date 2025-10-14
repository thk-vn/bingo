<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class BingoController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function dial()
    {
        return view('dial');
    }
}
