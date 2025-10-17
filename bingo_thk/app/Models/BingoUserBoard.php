<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class BingoUserBoard extends Model
{
    use Notifiable;

    const STATUS_END = 1;
    const STATUS_NOT_END = 0;

    protected $fillable = ['bingo_board', 'marked_cells'];

    protected $casts = [
        'bingo_board' => 'array',
        'marked_cells' => 'array',
    ];
}
