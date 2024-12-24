<?php

use App\Models\User;
use Thunk\Verbs\Facades\Verbs;
use Illuminate\Foundation\Testing\DatabaseMigrations;

beforeEach(function () {
    Verbs::commitImmediately();

    $this->bootMultiplayerGame();
});

it('can move a tile', function () {
    $this->player_1->playTile(1, 'right');

    expect($this->game->state()->board[1])->toBe((string) $this->player_1->id);
});

it('can push existing tiles to the adjacent space', function () {
    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(6);

    $this->player_2->playTile(1, 'right');
    $this->player_2->moveElephant(6);

    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(6);

    $this->player_2->playTile(1, 'right');

    expect($this->game->state()->board[1])->toBe((string) $this->player_2->id);
    expect($this->game->state()->board[2])->toBe((string) $this->player_1->id);
    expect($this->game->state()->board[3])->toBe((string) $this->player_2->id);
    expect($this->game->state()->board[4])->toBe((string) $this->player_1->id);
});

it('pushes tiles off the board and returns them to their owners hand', function () {
    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(6);

    $this->player_2->playTile(1, 'right');
    $this->player_2->moveElephant(6);

    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(6);

    $this->player_2->playTile(1, 'right');
    $this->player_2->moveElephant(6);

    $this->assertEquals(6, $this->game->state()->currentPlayer()->hand);
    $this->assertEquals(6, $this->game->state()->idlePlayer()->hand);

    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(6);

    $this->player_2->playTile(1, 'right');

    $this->assertEquals(6, $this->game->state()->currentPlayer()->hand);
    $this->assertEquals(6, $this->game->state()->idlePlayer()->hand);
});

it('validates that the space and direction are valid', function () {
    expect(function () {
        $this->player_1->playTile(0, 'right');
    })->toThrow('Invalid space');

    expect(function () {
        $this->player_1->playTile(1, 'foo');
    })->toThrow('Invalid direction');
});

it('does not allow a player to play when it is not their turn', function () {
    expect(function () {
        $this->player_2->playTile(1, 'right');
    })->toThrow('It is not player '.$this->player_2->id.' turn');

    $this->player_1->playTile(1, 'right');

    expect(function () {
        $this->player_1->playTile(1, 'right');
    })->toThrow('It is time to move the elephant, not play a tile');
});

it('handles empty hand states', function() {
    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(10, true);
    $this->player_2->playTile(1, 'right');
    $this->player_2->moveElephant(10, true);
    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(10, true);
    $this->player_2->playTile(1, 'right');
    $this->player_2->moveElephant(10, true);

    expect($this->player_1->fresh()->hand)->toBe(6);
    expect($this->player_2->fresh()->hand)->toBe(6);

    // player_1 pushes their opponent's tile off the board
    $this->player_1->playTile(4, 'left');
    $this->player_1->moveElephant(10, true);

    expect($this->player_1->fresh()->hand)->toBe(5);
    expect($this->player_2->fresh()->hand)->toBe(7);
});