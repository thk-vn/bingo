<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class BingoUser extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'department', 'phone_number', 'bingo_board', 'marked_cells', 'session_token', 'reset_key'];

    protected $casts = [
        'bingo_board' => 'array',
        'marked_cells' => 'array',
    ];
}
