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

    public function dial()
    {
        return view('dial');
    }

    /**
     * Admin dial guarded by token from .env (query ?token=...)
     */
    public function admin(Request $request)
    {
        $token = $request->query('token');
        $expected = env('ADMIN_TOKEN');
        if (!$expected || $token !== $expected) {
            abort(403, 'Forbidden');
        }

        return view('dial');
    }

    /**
     * Increment reset_key on the first game row (creates if absent)
     */
    public function resetGame(Request $request): JsonResponse
    {
        $game = Game::query()->first();
        if (!$game) {
            $game = new Game();
            $game->reset_key = 0;
        }
        $game->reset_key = (int) $game->reset_key + 1;
        $game->save();

        return response()->json(['success' => true, 'reset_key' => $game->reset_key]);
    }
}
