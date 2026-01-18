@php
    $customLayout = config('beartropy-saml2.layout');
@endphp

@if($customLayout)
    {{-- Custom component layout - render content as slot --}}
    <x-dynamic-component :component="$customLayout">
        @include('beartropy-saml2::admin.partials.styles', ['hasMaxWidth' => false])
        
        <div class="saml2-admin-wrapper">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            {{ $slot }}
        </div>

        @isset($scripts)
            {{ $scripts }}
        @endisset
    </x-dynamic-component>
@else
    {{-- Default standalone layout --}}
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? __('beartropy-saml2::saml2.admin.title') }}</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; min-height: 100vh; }
            .navbar { background: #1f2937; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
            .navbar h1 { color: #fff; font-size: 1.25rem; }
            .navbar a { color: #9ca3af; text-decoration: none; font-size: 0.875rem; }
            .navbar a:hover { color: #fff; }
            .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        </style>
        @include('beartropy-saml2::admin.partials.styles')
    </head>
    <body>
        <nav class="navbar">
            <h1>{{ __('beartropy-saml2::saml2.admin.title') }}</h1>
            <a href="{{ url('/') }}">← {{ __('beartropy-saml2::saml2.admin.back_to_app') }}</a>
        </nav>

        <div class="container">
            <div class="saml2-admin-wrapper">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                {{ $slot }}
            </div>
        </div>

        @isset($scripts)
            {{ $scripts }}
        @endisset
    </body>
    </html>
@endif

