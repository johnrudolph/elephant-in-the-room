<div>
    <div class="flex space-x-4 mt-10">
        <div class="w-full">
            <flux:input wire:model="invitee_email" placeholder="Invite a friend by email" />
            <flux:error name="invitee_email" />
        </div>
        <flux:button variant="primary" wire:click="inviteFriend">Invite</flux:button>
    </div>  

    @if ($this->incomingRequests->count() > 0)

        <div class="h-8"></div>

        <flux:table>
            <flux:columns>
                <flux:column>Incoming invitations</flux:column>
                <flux:column></flux:column>
            </flux:columns>

            <flux:rows>
                @foreach ($this->incomingRequests as $request)
                    <flux:row wire:key="incoming-request-{{ $request->id }}">
                        <flux:cell>{{ $request->initiator->name }}</flux:cell>
                        <flux:cell variant="strong" class="flex gap-2 justify-end">
                            <flux:button variant="primary" size="xs" icon="check" wire:click="acceptFriendship('{{ $request->initiator->email }}')" />
                            <flux:button variant="danger" size="xs" icon="x-mark" wire:click="rejectFriendship('{{ $request->initiator->email }}')" />
                        </flux:cell>
                    </flux:row>
                @endforeach
            </flux:rows>
        </flux:table>
    @endif

    @if ($this->friends->count() > 0 || $this->outgoingRequests->count() > 0)

        <div class="h-8"></div>

        <flux:table>
            <flux:columns>
                <flux:column>Friends</flux:column>
                <flux:column></flux:column>
            </flux:columns>

            <span class="h-8"></span>

            <flux:rows>
                @foreach ($this->friends as $friend)
                    <flux:row>
                        <flux:cell>{{ $friend->name }}</flux:cell>
                        <flux:cell class="flex justify-end">
                            <flux:badge color="green" size="xs" inset="top bottom">Friend</flux:badge>
                        </flux:cell>
                    </flux:row>
                @endforeach
                @foreach ($this->outgoingRequests as $request)
                    <flux:row>
                        <flux:cell>{{ $request->recipient->name }}</flux:cell>
                        <flux:cell class="flex justify-end"><flux:badge color="gray" size="xs" inset="top bottom">Requested</flux:badge></flux:cell>
                    </flux:row>
                @endforeach
            </flux:rows>
        </flux:table>
    @endif

    <flux:toast />
</div>
