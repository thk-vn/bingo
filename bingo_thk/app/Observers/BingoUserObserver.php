<?php

namespace App\Observers;

use App\Models\BingoUser;

class BingoUserObserver
{
    public function creating(BingoUser $bingoUser)
    {
        $bingoUser->reset_key = '0';
    }
}
