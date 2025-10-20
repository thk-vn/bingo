<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Admin dial guarded by token from .env (query ?token=...)
     */
    public function index(Request $request): View
    {
        $token_request = $request->query('token');
        $admin_token = env('ADMIN_TOKEN');
        if ($token_request !== $admin_token) {
            abort(403, 'Forbidden');
        }

        return view('dial');
    }

    /**
     * Increment reset_key on the first game row (creates if not exists)
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
