<?php

namespace App\Events;

use App\Models\Game;
use App\Models\User;
use App\Models\Player;
use Thunk\Verbs\Event;
use App\States\GameState;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class PlayerCreated extends Event
{
    #[StateId(PlayerState::class)]
    public ?int $player_id = null;

    #[StateId(GameState::class)]
    public int $game_id;

    public int $user_id;

    public string $victory_shape;

    public bool $is_host;

    public bool $is_bot;

    public function apply(PlayerState $state)
    {
        $state->game_id = $this->game_id;

        $state->user_id = $this->user_id;

        $state->is_bot = $this->is_bot;

        $state->is_host = $this->is_host;

        $state->victory_shape = $this->victory_shape;

        $state->wants_rematch = $this->is_bot
            ? true
            : false;
    }

    public function applyToGame(GameState $state)
    {
        $this->is_host
            ? $state->player_1_id = $this->player_id
            : $state->player_2_id = $this->player_id;

        $this->is_host
            ? $state->player_1_victory_shape = $this->victory_shape
            : $state->player_2_victory_shape = $this->victory_shape;
    }

    public function handle()
    {
        Player::create([
            'id' => $this->player_id,
            'game_id' => $this->game_id,
            'user_id' => $this->user_id,
            'is_host' => $this->is_host,
            'is_bot' => $this->is_bot,
            'victory_shape' => $this->victory_shape,
            'wants_rematch' => $this->is_bot,
        ]);

        $game = Game::find($this->game_id);

        if ($this->is_host) {
            $game->current_player_id = $this->player_id;
            $game->save();
        }

        PlayerCreatedBroadcast::dispatch($game);
    }
}
