<?php

namespace App\Events;

use App\Models\Game;
use App\Models\Player;
use Thunk\Verbs\Event;
use App\States\GameState;
use Illuminate\Support\Carbon;
use App\Events\GameStartedBroadcast;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class GameStarted extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    public function apply(GameState $state)
    {
        $state->status = 'active';

        $state->current_player_id = $state->player_1_id;

        $state->phase = GameState::PHASE_PLACE_TILE;

        $state->victor_ids = [];
    }

    public function handle()
    {
        $game = Game::find($this->game_id);
        $game->status = 'active';
        $game->save();

        Player::find($game->current_player_id)->update([
            'forfeits_at' => Carbon::now()->addSeconds(35),
        ]);

        GameStartedBroadcast::dispatch($game);
    }
}
