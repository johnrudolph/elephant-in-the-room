<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Elephant in the Room</title>
        <link rel="icon" href="{{ asset('favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxStyles
        @include('partials.social-meta')
    </head>
    <body class="antialiased font-sans bg-white dark:bg-slate-900 dark:text-white">
        <flux:container class="flex flex-col items-center justify-center">
            <div class="max-w-sm flex flex-col mt-8 items-center justify-center">
                <flux:heading size="xl">
                    Elephant in the Room
                </flux:heading>
                <flux:subheading class="text-center max-w-xs">
                    Your mission: build a simple shape. The catch? Your opponent can push your tiles around, and there's an elephant in the room.
                </flux:subheading>
            </div>
            <div class="my-8">
                <x-demo />
            </div>

            <div class="mt-4 flex flex-row space-x-4 justify-center">
                @if (auth()->user())
                    <flux:button variant="primary" href="{{ route('dashboard') }}">Play</flux:button>
                @else
                    <flux:button variant="primary" href="{{ route('register') }}">Register</flux:button>
                    <flux:button variant="filled" href="{{ route('login') }}">Login</flux:button>
                @endif
            </div>
            <x-footer />
        </flux:container>
        @fluxScripts
    </body>
</html>
