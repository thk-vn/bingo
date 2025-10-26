<?php

namespace App\Services;

use App\Models\BingoUser;
use App\Models\BingoUserBoard;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class BingoUserBoardService
{
    /**
     * Create user bingo game
     *
     * @param array $bingoBoard
     * @param array $markedCells
     * @param BingoUser $bingoUser
     * @return bool
     */
    public function create(array $bingoBoard, array $markedCells, BingoUser $bingoUser): bool
    {
        $bingoUserBoard = new BingoUserBoard();
        $bingoUserBoard->bingo_user_id = $bingoUser->id;
        $bingoUserBoard->bingo_board = json_encode($bingoBoard);
        $bingoUserBoard->marked_cells = json_encode($markedCells);
        return $bingoUserBoard->save();
    }

    /**
     * Fetch user bingo game
     *
     * @param BingoUser $bingoUser
     * @return array
     */
    public function fetchBingoUserBoard(BingoUser $bingoUser): array
    {
        $bingoUserBoard = BingoUserBoard::where('status', BingoUserBoard::STATUS_NOT_END)
            ->where('bingo_user_id', $bingoUser->id)
            ->first();

        return $bingoUserBoard ? $this->transformBingoUserBoard($bingoUserBoard) : [];
    }

    /**
     * Transform bingo user board data
     *
     * @param BingoUserBoard $bingoUserBoard
     * @return array
     */
    public function transformBingoUserBoard($bingoUserBoard): array
    {
        $data = [];
        $data['id'] = $bingoUserBoard->id;
        $data['bingo_board'] = !empty($bingoUserBoard->bingo_board) ? json_decode($bingoUserBoard->bingo_board) : null;
        $data['marked_cells'] = !empty($bingoUserBoard->marked_cells) ? json_decode($bingoUserBoard->marked_cells) : null;
        $data['status'] = $bingoUserBoard->status;
        return $data;
    }

    /**
     * Reset board game
     *
     * @param BingoUser $bingoUser
     * @return void
     */
    public function resetBoardGame(BingoUser $bingoUser, $request): void
    {
        $bingoUserBoard = BingoUserBoard::where('bingo_user_id', $bingoUser->id)
            ->leftJoin('bingo_users', 'bingo_users.id', '=', 'bingo_user_boards.bingo_user_id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('games')
                    ->whereColumn('games.reset_key', '>', 'bingo_users.reset_key');
            })
            ->where('status', BingoUserBoard::STATUS_NOT_END)
            ->select('bingo_user_boards.*')
            ->first();

        if ($bingoUserBoard) {
            $bingoUserBoard->forceFill([
                'bingo_board' => json_encode($request['bingo_board']),
                'marked_cells' => json_encode($request['marked_cells']),
                'status' => BingoUserBoard::STATUS_END,
            ]);
            $bingoUserBoard->save();

            // Auto increment reset key after bingo board game end round and admin start new game
            $bingoUser->reset_key += 1;
            $bingoUser->save();
        }
    }

    /**
     * Check game allow reset
     *
     * @param BingoUser $bingoUser
     * @return bool
     */
    public function checkGameAllowedReset(BingoUser $bingoUser): bool
    {
        $game = Game::first();
        if($game && $game->reset_key > $bingoUser->reset_key) {
            return true;
        }
        return false;
    }
}
