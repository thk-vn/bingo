<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draw_numbers', function (Blueprint $table) {
            $table->id();
            $table->integer('number');
            $table->bigInteger('game_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draw_numbers');
    }
};
