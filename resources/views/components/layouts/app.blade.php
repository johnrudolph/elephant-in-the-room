<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ config('app.name', 'Elephant in the Room') }}{{ !empty($title) ? ' - ' . $title : '' }}</title>
        <link rel="icon" href="{{ asset('favicon.ico') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @fluxStyles
        @livewireScripts
        @include('partials.social-meta')
    </head>
    <body class="min-h-screen bg-white dark:bg-slate-900 text-zinc-800 dark:text-zinc-200">
        @if (auth()->user())
            <flux:sidebar sticky stashable class="border-r bg-white dark:bg-slate-900 border-zinc-200 dark:border-zinc-700 z-20">
                <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
        
                <div class="flex flex-row space-x-4 items-center">
                    <x-svg.elephant class="w-11 h-11 dark:text-white text-gray-900"/>
                    <flux:heading size="lg">Elephant in the Room</flux:heading>
                </div>
        
                <flux:navlist variant="outline">
                    <flux:navlist.item icon="home" href="/dashboard">Home</flux:navlist.item>
                    <flux:navlist.item icon="users" href="/friends">Friends</flux:navlist.item>
                </flux:navlist>
        
                <flux:spacer />
        
                <flux:navlist variant="outline">
                    <flux:navlist.item icon="cog-6-tooth" href="/profile" label="Profile">Profile</flux:navlist.item>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <flux:navlist.item 
                            icon="power" 
                            as="button"
                            type="submit"
                            label="Logout" 
                        >
                            Logout
                    </flux:navlilst.item>
                    </form>
                </flux:navlist>
            </flux:sidebar>

            <div class="lg:hidden">
                <div class="fixed top-4 left-4">
                    <flux:sidebar.toggle class="lg:hidden" variant="ghost" icon="bars-2" inset="left" />
                </div>
            </div>
        @endif

        <flux:main container class="max-w-screen-sm">
            <div class="flex flex-col pb-16">
                {{ $slot }}
            </div>
        </flux:main>

        @fluxScripts
    </body>
</html>
