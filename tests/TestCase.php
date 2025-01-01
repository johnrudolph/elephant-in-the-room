<?php

namespace Tests;

use App\Models\Game;
use App\Models\User;
use App\Models\Player;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\UserCreated;
use App\Events\PlayerCreated;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public User $john;

    public User $lindsey;

    public Game $game;

    public Player $player_1;

    public Player $player_2;

    public function bootTwoHumans()
    {
        $john_id = UserCreated::fire(
            name: "John",
            email: "john@thunk.dev",
            password: "password",
        )->user_id;

        $lindsey_id = UserCreated::fire(
            name: "Lindsey",
            email: "lindsey@thunk.dev",
            password: "password",
        )->user_id;

        $this->john = User::find($john_id);
        $this->lindsey = User::find($lindsey_id);
    }

    public function bootMultiplayerGame(?string $victory_shape = 'square')
    {
        $this->bootTwoHumans();

        $game_id = GameCreated::fire(
            user_id: $this->john->id,
            victory_shape: $victory_shape,
            is_ranked: true,
            is_friends_only: false,
            is_single_player: false,
        )->game_id;

        $this->game = Game::find($game_id);

        $player_1_id = PlayerCreated::fire(
            game_id: $game_id,
            user_id: $this->john->id,
            is_host: true,
            is_bot: false,
            victory_shape: $victory_shape,
            is_first_player: true,
        )->player_id;

        $this->player_1 = Player::find($player_1_id);

        $player_2_id = PlayerCreated::fire(
            game_id: $game_id,
            user_id: $this->lindsey->id,
            is_host: false,
            is_bot: false,
            victory_shape: $victory_shape,
        )->player_id;

        $this->player_2 = Player::find($player_2_id);

        GameStarted::fire(game_id: $game_id);
    }

    public function bootSinglePlayerGame(?string $bot_difficulty = 'hard', ?string $victory_shape = 'square')
    {
        $bot_id = UserCreated::fire(
            name: 'Bot 5000',
            email: 'bot@bot.bot',
            password: bcrypt('password'),  
        )->user_id;

        $john_id = UserCreated::fire(
            name: "John",
            email: "john@thunk.dev",
            password: "password",
        )->user_id;

        $game_id = GameCreated::fire(
            user_id: $john_id,
            is_single_player: true,
            bot_difficulty: $bot_difficulty,
            victory_shape: $victory_shape,
            is_ranked: false,
            is_friends_only: false,
        )->game_id;

        $player_1_id = PlayerCreated::fire(
            game_id: $game_id,
            user_id: $john_id,
            is_host: true,
            is_bot: false,
            victory_shape: $victory_shape,
            is_first_player: true,
        )->player_id;

        $player_2_id = PlayerCreated::fire(
            game_id: $game_id,
            user_id: $bot_id,
            is_host: false,
            is_bot: true,
            victory_shape: $victory_shape,
        )->player_id;

        $this->game = Game::find($game_id);

        $this->player_1 = Player::find($player_1_id);

        $this->player_2 = Player::find($player_2_id);

        GameStarted::fire(game_id: $game_id);
    }

    public function bootUnjoinedGame()
    {
        $this->bootTwoHumans();

        $game_id = GameCreated::fire(
            user_id: $this->john->id,
            is_single_player: false,
            is_ranked: false,
            is_friends_only: false,
        )->game_id;

        $this->game = Game::find($game_id);
    }

    public function dumpBoard()
    {
        $elephant_space = $this->game->state()->elephant_space;

        $b = collect($this->game->state()->board)
            ->mapWithKeys(function($occupant, $space) use ($elephant_space) {
                if ($occupant === (string) $this->player_1->id) {
                    return $elephant_space === $space
                        ? [$space => '1E'] 
                        : [$space => '1'];
                }

                if ($occupant === (string) $this->player_2->id) {
                    return $elephant_space === $space
                        ? [$space => '2E'] 
                        : [$space => '2'];
                }

                return $elephant_space === $space
                    ? [$space => 'E'] 
                    : [$space => '-'];
            });

        dump(
            $b[1] . '   ' . $b[2] . '   ' . $b[3] . '   ' . $b[4],
            $b[5] . '   ' . $b[6] . '   ' . $b[7] . '   ' . $b[8],
            $b[9] . '   ' . $b[10] . '   ' . $b[11] . '   ' . $b[12],
            $b[13] . '   ' . $b[14] . '   ' . $b[15] . '   ' . $b[16],
        );
    }
}
