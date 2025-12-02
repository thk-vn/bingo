<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class BingoUser extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'reset_key'];

    protected $casts = [];
}
