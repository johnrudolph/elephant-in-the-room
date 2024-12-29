<?php

use App\Models\Game;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->foreignIdFor(Game::class, 'rematch_game_id')->nullable();
        });
    }
};
