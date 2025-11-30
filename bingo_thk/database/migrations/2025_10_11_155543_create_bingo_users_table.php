<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bingo_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->tinyInteger('reset_key')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bingo_users');
    }
};
