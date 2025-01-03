<?php

namespace App\Events;

use App\Models\Game;
use App\Models\Player;
use Thunk\Verbs\Event;
use App\States\GameState;
use App\States\UserState;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class PlayerRequestedRematch extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(UserState::class)]
    public int $user_id;
    
    #[StateId(GameState::class)]
    public int $game_id;

    public function apply(PlayerState $state)
    {
        $state->wants_rematch = true;
    }

    public function handle()
    {
        Player::find($this->player_id)->update([
            'wants_rematch' => true,
        ]);
    }
}
