<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\User;
use App\Models\Player;
use Livewire\Component;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\PlayerCreated;
use Thunk\Verbs\Facades\Verbs;
use Livewire\Attributes\Computed;

class HomePage extends Component
{
    public bool $is_bot_game = false;

    public bool $is_ranked_game = true;

    public bool $is_friends_only = false;

    #[Computed]
    public function user()
    {
        return auth()->user();
    }

    #[Computed]
    public function friends()
    {
        return $this->user->friends();
    }

    #[Computed]
    public function highlight_rules()
    {
        return $this->user->games->where('status', 'complete')->count() === 0;
    }

    #[Computed]
    public function active_game()
    {
        $active_game = $this->user->games->where('status', 'active')->last();

        if ($active_game) {
            return $active_game;
        }

        $upcoming_game = $this->user->games->where('status', 'created')
            ->filter(fn ($g) => $g->players->count() === 2)
            ->first();

        if ($upcoming_game) {
            return $upcoming_game;
        }

        return null;
    }

    #[Computed]
    public function active_opponent()
    {
        return $this->active_game->players->firstWhere('user_id', '!=', $this->user->id);
    }

    #[Computed]
    public function games()
    {
        return Game::where('status', 'created')
            ->with(['players.user'])
            ->get()
            ->filter(function ($game) {
                return ! $game->is_friends_only
                || $game->players->first()->user->friendship_status_with($this->user);
            })
            ->reject(function ($game) {
                return $game->players->first()->user->id === $this->user->id;
            })
            ->reject(fn ($g) => $g->players->count() === 2)
            ->sortByDesc('created_at')
            ->map(function ($game) {
                return [
                    'id' => (string) $game->id,
                    'player' => $game->players->first()->user->name,
                    'is_friend' => $game->players->first()->user->friendship_status_with($this->user) === 'friends',
                    'is_ranked' => $game->is_ranked,
                    'rating' => $game->players->first()->user->rating,
                ];
            });
    }

    public function newGame()
    {
        $game = Game::fromTemplate(
            user: $this->user,
            is_bot_game: $this->is_bot_game,
            is_friends_only: $this->is_friends_only,
            is_ranked: $this->is_ranked_game,
            is_rematch_from_game_id: null,
        );

        return redirect()->route('games.show', $game->id);
    }

    public function join(string $game_id)
    {
        $victory_shape = Game::find($game_id)->players->first()->victory_shape;

        PlayerCreated::fire(
            game_id: (int) $game_id,
            user_id: $this->user->id,
            is_host: false,
            is_bot: false,
            victory_shape: $victory_shape,
        );

        $this->user->closeInactiveGames();

        Verbs::commit();

        return redirect()->route('games.show', (int) $game_id);
    }

    public function render()
    {
        return view('livewire.home-page');
    }
}
