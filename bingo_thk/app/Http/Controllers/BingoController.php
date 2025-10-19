<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Game;

class BingoController extends Controller
{
    public function index()
    {
        return view('index');
    }

}
