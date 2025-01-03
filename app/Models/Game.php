<?php

namespace App\Models;

use App\Models\Move;
use App\States\GameState;
use App\Events\GameCreated;
use App\Events\GameStarted;
use App\Events\PlayerCreated;
use Thunk\Verbs\Facades\Verbs;
use Glhd\Bits\Database\HasSnowflakes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory, HasSnowflakes;

    protected $guarded = [];

    protected $casts = [
        'board' => 'array',
        'valid_slides' => 'array',
        'valid_elephant_moves' => 'array',
        'victor_ids' => 'array',
        'winning_spaces' => 'array',
    ];

    public static function fromTemplate(User $user, bool $is_bot_game, bool $is_friends_only, bool $is_ranked, ?int $is_rematch_from_game_id, ?bool $is_first_player)
    {
        $game_id = GameCreated::fire(
            user_id: $user->id,
            is_single_player: $is_bot_game,
            bot_difficulty: 'hard',
            is_ranked: $is_ranked,
            is_friends_only: $is_friends_only,
            is_rematch_from_game_id: $is_rematch_from_game_id,
        )->game_id;
        
        $victory_shape = $is_bot_game 
            ? collect(['square', 'line', 'el'])->random()
            : collect(['square', 'line', 'el', 'zig', 'pyramid'])->random();

        PlayerCreated::fire(
            game_id: $game_id,
            user_id: $user->id,
            is_host: true,
            is_bot: false,
            victory_shape: $victory_shape,
            is_first_player: $is_first_player,
        );

        Verbs::commit();

        $game = Game::find($game_id);

        if ($is_bot_game) {
            $bot_id = User::where('email', 'bot@bot.bot')->first()->id;

            $bot_player_id = PlayerCreated::fire(
                game_id: $game_id,
                user_id: $bot_id,
                is_host: false,
                is_bot: true,
                victory_shape: $victory_shape,
                is_first_player: !$is_first_player,
            )->player_id;

            Verbs::commit();

            $bot_player = Player::find($bot_player_id);

            GameStarted::commit(game_id: $game_id);

            $bot_player->takeBotTurnIfNecessary();
        }

        if ($is_rematch_from_game_id && !$is_bot_game) {
            $opponent_user = Game::find($is_rematch_from_game_id)->players()->where('user_id', '!=', $user->id)->first()->user;

            PlayerCreated::fire(
                game_id: $game_id,
                user_id: $opponent_user->id,
                is_host: false,
                is_bot: false,
                victory_shape: $victory_shape,
                is_first_player: !$is_first_player,
            );

            GameStarted::fire(game_id: $game_id);

            Verbs::commit();

            $opponent_user->closeInactiveGamesBefore($game);
        }

        $user->closeInactiveGamesBefore($game);

        Verbs::commit();

        return $game;
    }

    public function state()
    {
        return GameState::load($this->id);
    }

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function moves()
    {
        return $this->hasMany(Move::class);
    }
}
