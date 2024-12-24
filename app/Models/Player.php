<?php

namespace App\Models;

use App\Models\Move;
use App\States\GameState;
use App\States\PlayerState;
use Thunk\Verbs\Facades\Verbs;
use App\Events\PlayerPlayedTile;
use App\Events\PlayerMovedElephant;
use Glhd\Bits\Database\HasSnowflakes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Player extends Model
{
    use HasFactory, HasSnowflakes;

    protected $casts = [
        'forfeits_at' => 'datetime',
    ];

    protected $guarded = [];

    public function state()
    {
        return PlayerState::load($this->id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function moves()
    {
        return $this->hasMany(Move::class);
    }

    public function playTile(?int $space = null, ?string $direction = null)
    {
        $game = $this->game->state();

        if ($this->is_bot && !$space && !$direction) {
            $bot_move_scores = $game->selectBotTileMove($game->board)->toArray();
            $move = array_slice($bot_move_scores, 0, 1)[0];
            $space = $move['space'];
            $direction = $move['direction'];
        } else {
            $bot_move_scores = null;
        }

        PlayerPlayedTile::fire(
            game_id: $this->game->id,
            player_id: $this->id,
            space: $space,
            direction: $direction,
            bot_move_scores: $bot_move_scores ?? null,
            board_before_slide: $game->board,
        );
    }

    public function moveElephant(?int $space = null, ?bool $skip_bot_phase = false)
    {
        $game = $this->game->state();

        if ($this->is_bot && !$space) {
            $bot_move_scores = $game->selectBotElephantMove($game->board)->toArray();
            $move = array_slice($bot_move_scores, 0, 1)[0];
            $space = $move['space'];
        } else {
            $bot_move_scores = null;
        }

        PlayerMovedElephant::fire(
            game_id: $this->game->id,
            player_id: $this->id,
            space: $space,
            bot_move_scores: $bot_move_scores ?? null,
            elephant_space_before: $game->elephant_space,
        );

        // this exists for testing purposes
        if ($skip_bot_phase) {
            return;
        }

        $game = $this->game->state();

        $next_player = $game->currentPlayer()->model();

        if ($next_player->is_bot) {
            $next_player->playTile();
            $next_player->moveElephant();
        }
    }

    public function takeBotTurnIfNecessary()
    {
        if(
            ! $this->is_bot 
            || $this->game->fresh()->status !== 'active' 
            || $this->fresh()->hand < 1 
            || $this->game->fresh()->current_player_id !== (string) $this->id
        ) {
            return false;
        }

        if ($this->game->phase === 'tile') {
            $this->playTile();
            Verbs::commit();
        }

        if ($this->game->fresh()->status === 'active') {    
            $this->moveElephant();
            Verbs::commit();
        }

        $this->takeBotTurnIfNecessary();
    }
}
