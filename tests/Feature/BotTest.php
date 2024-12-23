<?php

use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
});

it('can move a tile and the elephant', function () {
    $this->bootSinglePlayerGame();

    $this->player_1->playTile(1, 'right');
    $this->game->refresh();

    expect($this->game->phase)->toBe('move');
    expect($this->game->current_player_id)->toBe((string) $this->player_1->id);

    $this->player_1->moveElephant(2);
    $this->game->refresh();

    expect($this->game->phase)->toBe('tile');
    expect($this->game->current_player_id)->toBe((string) $this->player_1->id);
});

it('blocks the player from winning', function() {
    $this->bootSinglePlayerGame();
    $this->player_1->playTile(16, 'up');
    $this->player_1->moveElephant(7, true);
    $this->player_2->playTile(1, 'right');
    $this->player_2->moveElephant(7, true);
    $this->player_1->playTile(16, 'up');
    $this->player_1->moveElephant(7, true);
    $this->player_2->playTile(1, 'right');
    $this->player_2->moveElephant(7, true);
    $this->player_1->playTile(16, 'left');
    $this->player_1->moveElephant(7, true);
    $this->player_2->playTile();

    expect($this->game->fresh()->board[16])
        ->toBe((string) $this->player_2->id);
});

it('wins if it can', function() {
    $this->bootSinglePlayerGame();
    $this->player_1->playTile(16, 'up');
    $this->player_1->moveElephant(7, true);
    $this->player_2->playTile(1, 'down');
    $this->player_2->moveElephant(7, true);
    $this->player_1->playTile(16, 'up');
    $this->player_1->moveElephant(7, true);
    $this->player_2->playTile(1, 'down');
    $this->player_2->moveElephant(7, true);
    $this->player_1->playTile(4, 'down');
    $this->player_1->moveElephant(7, true);
    $this->player_2->playTile(2, 'down');
    $this->player_2->moveElephant(7, true);
    $this->player_1->playTile(15, 'up');
    $this->player_1->moveElephant(7, true);
    $this->player_2->playTile();

    // player has check, but bot can just win the game

    expect($this->game->fresh()->board[1])
        ->toBe((string) $this->player_2->id);

    expect($this->game->fresh()->board[2])
        ->toBe((string) $this->player_2->id);

    expect($this->game->fresh()->board[5])
        ->toBe((string) $this->player_2->id);

    expect($this->game->fresh()->board[6])
        ->toBe((string) $this->player_2->id);
});

it('maximizes its adjacent tiles', function() {
    $this->bootSinglePlayerGame();

    $fake_board = [
        1 => $this->player_2->id,
        2 => null,
        3 => null,
        4 => null,
        5 => null,
        6 => null,
        7 => $this->player_2->id,
        8 => null,
        9 => null,
        10 => null,
        11 => null,
        12 => null,
        13 => null,
        14 => null,
        15 => $this->player_2->id,
        16 => null,
    ];

    $bot_move = $this->game->state()->selectBotTileMove($fake_board);

    expect(collect([15]))->toContain($bot_move['space']);
})->skip('replace this with realistic scenario');

it('prioritizes breaking up player check rather than creating check', function() {
    $this->bootSinglePlayerGame();

    $fake_board = [
        1 => $this->player_2->id,
        2 => $this->player_2->id,
        3 => null,
        4 => null,
        5 => null,
        6 => null,
        7 => null,
        8 => null,
        9 => null,
        10 => null,
        11 => null,
        12 => $this->player_1->id,
        13 => null,
        14 => null,
        15 => $this->player_1->id,
        16 => $this->player_1->id,
    ];

    $bot_move = $this->game->state()->selectBotTileMove($fake_board);

    expect(collect([16]))->toContain($bot_move['space']);
})->skip('replace this with realistic scenario');

it('creates check if it can', function() {
    $this->bootSinglePlayerGame();

    $fake_board = [
        1 => $this->player_2->id,
        2 => null,
        3 => $this->player_2->id,
        4 => null,
        5 => null,
        6 => null,
        7 => null,
        8 => null,
        9 => null,
        10 => null,
        11 => null,
        12 => $this->player_2->id,
        13 => null,
        14 => null,
        15 => $this->player_2->id,
        16 => null,
    ];

    $bot_move = $this->game->state()->selectBotTileMove($fake_board);

    expect(collect([16]))->toContain($bot_move['space']);
})->skip('replace this with realistic scenario');

it('handles empty hand states', function() {
    $this->bootSinglePlayerGame();

    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(10, true);
    $this->player_2->playTile(5, 'right');
    $this->player_2->moveElephant(10, true);
    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(10, true);
    $this->player_2->playTile(5, 'right');
    $this->player_2->moveElephant(10, true);
    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(10, true);
    $this->player_2->playTile(5, 'right');
    $this->player_2->moveElephant(10, true);
    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(10, true);
    $this->player_2->playTile(5, 'right');
    $this->player_2->moveElephant(10, true);

    // the board is now has two full rows. 
    // no matter how many times we push them off the end, we have the same number of tiles

    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(10, true);
    $this->player_2->playTile(5, 'right');
    $this->player_2->moveElephant(10, true);
    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(10, true);
    $this->player_2->playTile(5, 'right');
    $this->player_2->moveElephant(10, true);
    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(10, true);
    $this->player_2->playTile(5, 'right');
    $this->player_2->moveElephant(10, true);
    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(10, true);
    $this->player_2->playTile(5, 'right');
    $this->player_2->moveElephant(10, true);

    expect($this->player_1->fresh()->hand)->toBe(4);
    expect($this->player_2->fresh()->hand)->toBe(4);

    // player runs out of tiles:

    $this->player_1->playTile(9, 'right');
    $this->player_1->moveElephant(6, true);
    $this->player_2->playTile(13, 'right');
    $this->player_2->moveElephant(6, true);
    $this->player_1->playTile(9, 'right');
    $this->player_1->moveElephant(6, true);
    $this->player_2->playTile(13, 'right');
    $this->player_2->moveElephant(6, true);
    $this->player_1->playTile(9, 'right');
    $this->player_1->moveElephant(6, true);
    $this->player_2->playTile(13, 'right');
    $this->player_2->moveElephant(6, true);
    $this->player_1->playTile(9, 'right');
    $this->player_1->moveElephant(6, true);

    expect($this->player_1->fresh()->hand)->toBe(0);
    expect($this->player_2->fresh()->hand)->toBe(1);

    // the bot should take a normal turn, and move the elephant:

    $this->dumpBoard();
});
