<?php

namespace App\Events;

use App\Models\Game;
use App\Models\Move;
use App\Models\Player;
use Thunk\Verbs\Event;
use App\States\GameState;
use App\States\PlayerState;
use Illuminate\Support\Carbon;
use App\Events\PlayerMovedElephantBroadcast;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class PlayerMovedElephant extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $space;

    public ?array $bot_move_scores = null;

    public int $elephant_space_before;

    public function authorize()
    {
        $game = $this->state(GameState::class);

        $this->assert(
            $game->current_player_id === $this->player_id,
            'It is not player '.$this->player_id.' turn'
        );

        $this->assert(
            $game->phase === $game::PHASE_MOVE_ELEPHANT,
            'It is time to place a tile, not move the elephant'
        );

        $this->assert(
            $game->player_1_id === $this->player_id || $game->player_2_id === $this->player_id,
            'Player '.$this->player_id.' is not in this game'
        );

        $this->assert(
            $game->status === 'active',
            'Game '.$this->game_id.' is not active'
        );
    }

    public function validate()
    {
        $game = $this->state(GameState::class);

        $this->assert(
            collect(range(1, 16))->contains($this->space),
            'Invalid space'
        );

        $this->assert(
            collect($game->validElephantMoves())->contains($this->space),
            'Elephant cannot reach that space'
        );
    }

    public function applyToGame(GameState $state)
    {
        $state->moves[] = [
            'type' => 'elephant',
            'player_id' => $this->player_id,
            'origin_space' => $state->elephant_space,
            'destination_space' => $this->space,
        ];

        $state->elephant_space = $this->space;

        $state->phase = GameState::PHASE_PLACE_TILE;

        if ($state->idlePlayer()->hand > 0) {
            $state->current_player_id = $state->idlePlayer()->id;
        }
    }

    public function applyToPlayer(PlayerState $state)
    {
        // @todo why do I need this function
    }

    public function handle()
    {
        $game = $this->state(GameState::class);

        $game_model = Game::find($this->game_id);
        
        $game_model->update([
            'valid_elephant_moves' => $game->validElephantMoves(),
            'valid_slides' => $game->validSlides(),
            'elephant_space' => $game->elephant_space,
            'phase' => $game->phase,
            'current_player_id' => $game->current_player_id,
        ]);

       $move = Move::create([
            'game_id' => $this->game_id,
            'player_id' => $this->player_id,
            'type' => 'elephant',
            'board_before' => $game->board,
            'board_after' => $game->board,
            'elephant_before' => $this->elephant_space_before,
            'elephant_after' => $game->elephant_space,
            'bot_move_scores' => $this->bot_move_scores,
        ]);

        $previous_player = Player::find($this->player_id);

        $previous_player->update([
            'forfeits_at' => null,
        ]);

        $next_player = Player::find($game->current_player_id);

        $next_player->update([
            'forfeits_at' => Carbon::now()->addSeconds(35),
        ]);

        $prior_tile_move = $game_model->moves
            ->filter(fn($m) => $m->type === 'tile')
            ->last();

        PlayerMovedElephantBroadcast::dispatch($game_model, $move, $prior_tile_move);
    }
}
