<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class BingoUserBoard extends Model
{
    use Notifiable;

    const STATUS_END = 1;
    const STATUS_NOT_END = 0;

    protected $fillable = ['bingo_board', 'marked_cells', 'status'];

    protected $casts = [
        'bingo_board' => 'array',
        'marked_cells' => 'array',
    ];

    /**
     * Get the bingo user that owns the board.
     * @return BelongsTo
     */
    public function bingoUser(): BelongsTo
    {
        return $this->belongsTo(BingoUser::class, 'bingo_user_id');
    }
}
