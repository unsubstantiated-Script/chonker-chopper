<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'URL Shortener') }} - @yield('title', 'Shorten Your URLs')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white min-h-screen">
<nav class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="{{ route('urls.upload') }}" class="text-xl font-bold text-gray-900">
                    {{ config('app.name', 'Short.ly') }}
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('urls.upload') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md">
                    New URL
                </a>
{{--                TODO: remove these comment outs to enable the conditional renderings   --}}
{{--                @if(session('batch_id'))--}}
                    <a href="{{ route('urls.view') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md">
                        My URLs
                    </a>
                    <a href="{{ route('urls.analytics') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md">
                        Analytics
                    </a>
{{--                @endif--}}
            </div>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('components.alert')
    @yield('content')
</main>

<footer class="bg-white border-t mt-12">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="text-center text-sm text-gray-500">
            Free URL shortener
        </div>
    </div>
</footer>
</body>
</html>
