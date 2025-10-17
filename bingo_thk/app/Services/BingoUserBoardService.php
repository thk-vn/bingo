<?php
namespace App\Services;

use App\Models\BingoUser;
use App\Models\BingoUserBoard;

class BingoUserBoardService
{
    public function create(array $bingoBoard, array $markedCells, BingoUser $bingoUser): bool
    {
        $bingoUserBoard = new BingoUserBoard();
        $bingoUserBoard->bingo_user_id = $bingoUser->id;
        $bingoUserBoard->bingo_board = json_encode($bingoBoard);
        $bingoUserBoard->marked_cells = json_encode($markedCells);
        return $bingoUserBoard->save();
    }

    public function update(array $bingoBoard, array $markedCells, BingoUser $bingoUser): bool
    {
        $bingoUserBoard = new BingoUserBoard();
        $bingoUserBoard->bingo_board = json_encode($bingoBoard);
        $bingoUserBoard->marked_cells = json_encode($markedCells);
        return $bingoUserBoard->save();
    }

    public function fetchBingoUserBoard(BingoUser $bingoUser): array
    {
        $bingoUserBoard = BingoUserBoard::where('status', BingoUserBoard::STATUS_NOT_END)
        ->where('bingo_user_id', $bingoUser->id)
        ->first();

        return $this->transformBingoUserBoard($bingoUserBoard);
    }

    public function transformBingoUserBoard($bingoUserBoard): array
    {
        $data = [];
        $data['id'] = $bingoUserBoard->id;
        $data['bingo_board'] = !empty($bingoUserBoard->bingo_board) ? json_decode($bingoUserBoard->bingo_board) : null;
        $data['marked_cells'] = !empty($bingoUserBoard->marked_cells) ? json_decode($bingoUserBoard->marked_cells) : null;
        $data['status'] = $bingoUserBoard->status;
        return $data;
    }
}