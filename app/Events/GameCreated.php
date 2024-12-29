<?php

namespace App\Events;

use App\Models\Game;
use App\Models\User;
use Thunk\Verbs\Event;
use App\States\GameState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class GameCreated extends Event
{
    #[StateId(GameState::class)]
    public ?int $game_id = null;

    public int $user_id;

    public bool $is_ranked;

    public bool $is_friends_only;

    public bool $is_single_player;

    public ?string $bot_difficulty = 'hard';

    public ?int $is_rematch_from_game_id = null;

    public function apply(GameState $state)
    {
        $state->status = 'created';

        $state->is_single_player = $this->is_single_player;

        $state->is_ranked = $this->is_ranked;

        $state->victor_ids = [];

        $state->moves = [];

        $state->phase = $state::PHASE_PLACE_TILE;

        $state->winning_spaces = [];
    }

    public function handle()
    {
        $game = $this->state(GameState::class);

        Game::create([
            'id' => $this->game_id,
            'status' => 'created',
            'board' => $game->board,
            'valid_elephant_moves' => $game->validElephantMoves(),
            'valid_slides' => $game->validSlides(),
            'elephant_space' => $game->elephant_space,
            'phase' => $game->phase,
            'victor_ids' => $game->victor_ids,
            'winning_spaces' => $game->winning_spaces,
            'is_ranked' => $this->is_ranked,
            'is_friends_only' => $this->is_friends_only,
        ]);

        if ($this->is_rematch_from_game_id) {
            $previous_game = Game::find($this->is_rematch_from_game_id);
            $previous_game->rematch_game_id = $this->game_id;
            $previous_game->save();
        }
    }
}
