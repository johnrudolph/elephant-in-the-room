<div wire:poll.10000ms="checkIfCanceled" class="mt-10">
    @if($this->game->players->count() === 1 && $this->player)
        <flux:card class="w-full">
            <flux:heading>Waiting for opponent</flux:heading>
            <flux:subheading>Share this link to invite a friend.</flux:subheading>
            {{-- @todo sometimes this doesn't work?? --}}
            <flux:input class="mt-4" value="{{ route('games.pre-game-lobby.show', $game->id) }}" readonly copyable />
            <flux:subheading class="mt-4">This game will close if no opponent joins within 5 minutes.</flux:subheading>
            <flux:button class="mt-4" variant="primary" wire:click="leave">Leave</flux:button>
        </flux:card>
    @endif

    @if($this->game->players->count() === 1 && ! $this->player)
        <flux:card class="w-full">
            <flux:heading>Awaiting opponent</flux:heading>
            <flux:button class="mt-4" variant="primary" wire:click="join">Join</flux:button>
        </flux:card>
    @endif

    @if($this->game->players->count() === 2 && $this->player)
        <flux:card class="w-full">
            <flux:heading>Everyone's here!</flux:heading>
            <flux:button class="mt-4" variant="primary" wire:click="start">Start</flux:button>
        </flux:card>
    @endif

    <flux:card class="w-full mt-4">
        <flux:heading>Players</flux:heading>
        <div class="mt-4 flex flex-col gap-2">
            @foreach($this->game->players as $player)
                <div class="flex flex-row space-x-2 items-center">
                    <p class="text-sm">{{ $player->user->name }}</p>
                    <flux:badge color="gray" size="sm" variant="outline">{{ $player->user->rating }}</flux:badge>
                </div>
            @endforeach
        </div>
    </flux:card>
</div>
