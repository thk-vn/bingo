<?php

namespace App\Observers;

use App\Models\BingoUser;
use Illuminate\Support\Str;

class BingoUserObserver
{
    public function creating(BingoUser $bingoUser)
    {
        $bingoUser->session_token = Str::random(60);
        $bingoUser->reset_key = '0';
    }
}
