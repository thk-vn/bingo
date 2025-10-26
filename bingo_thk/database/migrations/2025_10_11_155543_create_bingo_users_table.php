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
            $table->string('phone_number', 11);
            $table->string('reset_key');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('bingo_users', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'phone_number', 'bingo_board', 'marked_cells', 'reset_key']);
        });
    }
};
