<?php

use Thunk\Verbs\Facades\Verbs;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Verbs::commitImmediately();
});

it('cancels stale games', function () {
    $this->bootUnjoinedGame();
    $this->travel(6)->minutes();
    Artisan::call('games:cancel-games');
    $this->game->refresh();
    expect($this->game->status)->toBe('canceled');
});

it('does not cancel non-stale games', function () {
    $this->bootMultiplayerGame();
    Artisan::call('games:cancel-games');
    $this->game->refresh();
    expect($this->game->status)->toBe('active');
});

it('forfeits abandoned games', function () {
    $this->bootMultiplayerGame();
    $this->travel(2)->minutes();
    Artisan::call('games:forfeit-games');
    $this->game->refresh();
    expect($this->game->status)->toBe('complete');
    expect($this->game->victor_ids)->toContain($this->player_2->id);
    expect(count($this->game->winning_spaces))->toBe(0);
});

it('does not forfeit nonabandoned games', function () {
    $this->bootMultiplayerGame();
    // Artisan::call('games:forfeit-games');
    $this->game->refresh();
    expect($this->game->status)->toBe('active');

    $this->travel(59)->seconds();
    // Artisan::call('games:forfeit-games');
    $this->game->refresh();
    expect($this->game->status)->toBe('active');

    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(6);

    $this->travel(59)->seconds();
    // Artisan::call('games:forfeit-games');
    $this->game->refresh();
    expect($this->game->status)->toBe('active');

    $this->travel(200)->seconds();
    Artisan::call('games:forfeit-games');
    $this->game->refresh();
    expect($this->game->status)->toBe('complete');
    expect($this->game->victor_ids)->toContain($this->player_1->id);
    expect(count($this->game->winning_spaces))->toBe(0);
});

it('sets the expected forfeit timecodes for players', function () {
    $this->bootMultiplayerGame();
    $this->game->refresh();
    expect($this->player_1->fresh()->forfeits_at)->toBeGreaterThan(now()->addSeconds(30));
    expect($this->player_1->fresh()->forfeits_at)->toBeLessThan(now()->addSeconds(40));
    expect($this->player_2->fresh()->forfeits_at)->toBeNull();

    $this->player_1->playTile(1, 'right');
    $this->player_1->moveElephant(6);

    expect($this->player_1->fresh()->forfeits_at)->toBeNull();
    expect($this->player_2->fresh()->forfeits_at)->toBeGreaterThan(now()->addSeconds(30));
    expect($this->player_2->fresh()->forfeits_at)->toBeLessThan(now()->addSeconds(40));
});
