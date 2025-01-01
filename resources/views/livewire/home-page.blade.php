<div>
    <flux:tab.group class="mt-10">
        <flux:tabs class="px-4">
            @if($this->highlight_rules)
                <flux:tab name="rules">Rules</flux:tab>
            @endif
            @if($this->active_game)
                <flux:tab name="active_game">Active game</flux:tab>
            @endif
            @if($this->games->count() > 0)
                <flux:tab name="join">Join</flux:tab>
            @endif
            <flux:tab name="create">Create</flux:tab>
            @if(! $this->highlight_rules)
                <flux:tab name="rules">Rules</flux:tab>
            @endif
        </flux:tabs>

        @if($this->active_game)
            <flux:tab.panel name="active_game">
                <flux:table>
                    <flux:columns>
                        <flux:column></flux:column>
                        <flux:column></flux:column>
                    </flux:columns>

                    <flux:rows>
                        <flux:row>
                            <flux:cell>
                                <div class="flex items-center gap-2">
                                    {{ $this->active_opponent->user->name }}
                                    <flux:badge color="gray" size="sm" variant="outline">{{ $this->active_opponent->user->rating }}</flux:badge>
                                    @if ($this->active_opponent->user->is_friend)
                                        <flux:badge size="sm" color="green">Friend</flux:badge>
                                    @endif
                                    @if ($this->active_game->is_ranked)
                                        <flux:badge size="sm" color="fuchsia">Ranked</flux:badge>
                                    @endif
                                </div>
                            </flux:cell>
                            <flux:cell class="flex justify-end">
                                <flux:button href="{{ route('games.show', $this->active_game->id) }}" variant="primary" size="xs">Rejoin</flux:button>
                            </flux:cell>
                        </flux:row>
                    </flux:rows>
                </flux:table>
            </flux:tab.panel>
        @endif

        <flux:tab.panel name="create">
            <flux:fieldset>
                <div class="space-y-3">
                    <flux:field variant="inline" class="w-full flex justify-between">
                        <flux:switch wire:model.live="is_first_player" />
                        <flux:label>Play first</flux:label>
                    </flux:field>

                    <flux:field variant="inline" class="w-full flex justify-between">
                        <flux:switch 
                            wire:model.live="is_ranked_game" 
                            x-bind:disabled="$wire.is_bot_game"
                        />
                        <flux:label>Ranked</flux:label>
                    </flux:field>

                    <flux:field variant="inline" class="w-full flex justify-between">
                        <flux:switch 
                            wire:model.live="is_friends_only" 
                            x-bind:disabled="$wire.is_bot_game"
                        />
                        <flux:label>Friends only</flux:label>
                    </flux:field>
                </div>
                <flux:field variant="inline" class="w-full flex justify-between">
                    <flux:switch 
                        wire:model.live="is_bot_game" 
                        x-on:change="
                            if ($event.target.checked) {
                                $wire.is_ranked_game = false;
                                $wire.is_friends_only = false;
                            }
                        "
                    />
                    <flux:label>Practice against bot</flux:label>
                </flux:field>
            </flux:fieldset>
            <flux:button wire:click="newGame" variant="primary" class="mt-8">Start game</flux:button>
        </flux:tab.panel>

        <flux:tab.panel name="join">
            @if ($this->games->isEmpty())
                <flux:heading>No active games</flux:heading>
            @else
                <flux:table>
                    <flux:columns>
                        <flux:column></flux:column>
                        <flux:column></flux:column>
                    </flux:columns>

                    <flux:rows>
                        @foreach ($this->games as $game)
                            <div wire:key="game-{{ $game['id'] }}">
                                <flux:row>
                                    <flux:cell>
                                        <div class="flex items-center gap-2 text-xs">
                                            {{ $game['player'] }}
                                            <flux:badge color="gray" size="sm" variant="outline" icon="star">{{ $game['rating'] }}</flux:badge>
                                       
                                            @if ($game['is_ranked'])
                                                <flux:badge size="sm" color="fuchsia">Ranked</flux:badge>
                                            @endif
                                        </div>
                                    </flux:cell>
                                    <flux:cell class="flex justify-end">
                                        <flux:button wire:click="join('{{ $game['id'] }}')" variant="primary" size="xs">Join</flux:button>
                                    </flux:cell>
                                </flux:row>
                            </div>
                        @endforeach
                    </flux:rows>
                </flux:table>
            @endif
        </flux:tab.panel>

        <flux:tab.panel name="rules">
            <flux:card>
                <flux:subheading>
                    On your turn, you will slide a tile onto the board, then move the elephant. 
                </flux:subheading>

                <img 
                    src="{{ asset('gifs/basic-turn.gif') }}" 
                    alt="Game rules demonstration"
                    class="w-full rounded-lg shadow-lg my-4"
                />

                <flux:subheading>
                    Sliding a tile into other tiles pushes them into the next space. When one of your tiles is pushed off the board, it returns to your hand. 
                </flux:subheading>

                <img 
                    src="{{ asset('gifs/sliding-tiles.gif') }}" 
                    alt="Game rules demonstration"
                    class="w-full rounded-lg shadow-lg my-4"
                />

                <flux:subheading>
                    The elephant blocks slides.
                </flux:subheading>

                <img 
                    src="{{ asset('gifs/simple-elephant-block.gif') }}" 
                    alt="Game rules demonstration"
                    class="w-full rounded-lg shadow-lg my-4"
                />

                <img 
                    src="{{ asset('gifs/elephant-block-complex.gif') }}" 
                    alt="Game rules demonstration"
                    class="w-full rounded-lg shadow-lg my-4"
                />

                <flux:subheading>
                    If you are out of tiles, you skip your turn. 
                </flux:subheading>

                <img 
                    src="{{ asset('gifs/out-of-tiles.gif') }}" 
                    alt="Game rules demonstration"
                    class="w-full rounded-lg shadow-lg my-4"
                />

                <flux:subheading>
                    Each player has a victory shape. This is the shape you must create to win.
                </flux:subheading>

                <img 
                    src="{{ asset('images/victory-shapes.png') }}" 
                    alt="Game rules demonstration"
                    class="w-full rounded-lg shadow-lg my-4"
                />

                <flux:subheading>
                    The first player to create their victory shape wins.
                </flux:subheading>

                <img 
                    src="{{ asset('gifs/victory.gif') }}" 
                    alt="Game rules demonstration"
                    class="w-full rounded-lg shadow-lg my-4"
                />
            </flux:card>
        </flux:tab.panel>

    </flux:tab.group>
    <x-footer />
</div>
