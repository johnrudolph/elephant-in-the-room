<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Player;
use Livewire\Component;
use App\Events\GameStarted;
use App\Events\GameCanceled;
use App\Events\PlayerCreated;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

class PreGameLobby extends Component
{
    public Game $game;

    #[Computed]
    public function user()
    {
        return Auth::user();
    }

    #[Computed]
    public function player()
    {
        return $this->game->players()->where('user_id', $this->user->id)->first();
    }

    #[Computed]
    public function opponent(): ?Player
    {
        return $this->game->players->firstWhere('user_id', '!=', $this->user->id);
    }

    public function getListeners()
    {
        return [
            "echo-private:games.{$this->game->id}:PlayerCreatedBroadcast" => 'handlePlayerCreated',
            "echo-private:games.{$this->game->id}:GameStartedBroadcast" => 'handleGameStarted',
            "echo-private:games.{$this->game->id}:GameCanceledBroadcast" => 'handleGameCanceled',
        ];
    }

    public function mount(Game $game)
    {
        $this->game = $game;
        $this->checkIfCanceled();
    }

    public function checkIfCanceled()
    {
        if ($this->game->status === 'canceled') {
            return redirect()->route('dashboard');
        }
    }

    public function handlePlayerCreated($event)
    {
        unset($this->opponent);
    }

    public function handleGameStarted($event)
    {
        return redirect()->route('games.show', $this->game->id);
    }

    public function join()
    {
        $victory_shape = $this->game->players->first()->victory_shape;

        PlayerCreated::fire(
            game_id: $this->game->id,
            user_id: $this->user->id,
            is_host: false,
            is_bot: false,
            victory_shape: $victory_shape,
        );

        $this->user->closeInactiveGamesBefore($this->game);
    }

    public function start()
    {
        GameStarted::fire(game_id: $this->game->id);

        return redirect()->route('games.show', $this->game->id);
    }

    public function leave()
    {
        GameCanceled::fire(game_id: $this->game->id);

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.pre-game-lobby');
    }
}
