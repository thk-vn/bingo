<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BingoUser extends Model
{
    protected $fillable = ['name', 'department', 'phone_number', 'bingo_board', 'marked_cells', 'session_token', 'reset_key'];

    protected $casts = [
        'bingo_board' => 'array',
        'marked_cells' => 'array',
    ];
}
