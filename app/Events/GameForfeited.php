<?php

namespace App\Events;

use App\Models\Game;
use App\Models\User;
use Thunk\Verbs\Event;
use App\States\GameState;
use App\Events\GameEndedBroadcast;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class GameForfeited extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    public int $loser_id;

    public int $winner_id;

    public function apply(GameState $state)
    {
        $state->status = 'completed';

        $state->victor_ids = [$this->winner_id];

        if ($state->is_ranked) {
            $state->players()->each(fn($p) => $p->user()->rating = User::calculateNewRating($p, $state));
        }
    }

    public function handle()
    {
        $game = Game::find($this->game_id);
        $game->status = 'completed';
        $game->victor_ids = [$this->winner_id];
        $game->save();

        $game->players->each(function ($player) {
            $player->forfeits_at = null;
            $player->save();

            $player->user->rating = $player->user->state()->rating;
            $player->user->save();
        });

        GameEndedBroadcast::dispatch($game);
    }
}
