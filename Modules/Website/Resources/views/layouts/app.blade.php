<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('website::layouts.partials.head')
</head>
<body data-preload="hover">
    @include('website::layouts.partials.preloader')
    
    @if(request()->is('/') || request()->is('home'))
        @include('website::components.navigation.header')
    @else
        @include('website::components.navigation.page-header')
    @endif

    <main class="app">
        @yield('content')
    </main>

    @include('website::components.navigation.footer')

    @include('website::layouts.partials.scripts')
    @stack('scripts')
</body>
</html>