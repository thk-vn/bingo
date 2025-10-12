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
            $table->string('department');
            $table->string('phone_number', 11);
            $table->json('bingo_board')->nullable();
            $table->json('marked_cells')->nullable();
            $table->string('session_token')->unique();
            $table->string('reset_key')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('bingo_users', function (Blueprint $table) {
            $table->dropColumn(['department', 'bingo_board', 'marked_cells', 'session_token', 'reset_key']);
        });
    }
};
