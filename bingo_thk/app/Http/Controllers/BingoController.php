<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BingoController extends Controller
{
    /**
     * View index
     *
     * @return void
     */
    public function index()
    {
        return view('index');
    }


    public function dial()
    {
        return view('dial');
    }
}
