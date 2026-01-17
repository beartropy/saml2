<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('beartropy-saml2::saml2.admin.title'))</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; min-height: 100vh; }
        .navbar { background: #1f2937; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { color: #fff; font-size: 1.25rem; }
        .navbar a { color: #9ca3af; text-decoration: none; font-size: 0.875rem; }
        .navbar a:hover { color: #fff; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .card { background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { font-size: 1.125rem; color: #111827; }
        .card-body { padding: 1.5rem; }
        .alert { padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; font-size: 0.75rem; color: #6b7280; text-transform: uppercase; }
        tr:hover { background: #f9fafb; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; text-decoration: none; cursor: pointer; border: none; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-success { background: #059669; color: #fff; }
        .btn-success:hover { background: #047857; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-secondary { background: #6b7280; color: #fff; }
        .btn-secondary:hover { background: #4b5563; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
        .btn-outline { background: transparent; border: 1px solid #d1d5db; color: #374151; }
        .btn-outline:hover { background: #f3f4f6; }
        .badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-gray { background: #e5e7eb; color: #6b7280; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
        input, textarea, select { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        textarea { font-family: monospace; resize: vertical; }
        .row { display: grid; gap: 1rem; }
        .row-2 { grid-template-columns: 1fr 1fr; }
        .row-3 { grid-template-columns: 1fr 1fr 1fr; }
        @media (max-width: 768px) { .row-2, .row-3 { grid-template-columns: 1fr; } }
        .actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .text-muted { color: #6b7280; font-size: 0.875rem; }
        .text-mono { font-family: monospace; font-size: 0.75rem; }
        .sp-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .sp-item label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; }
        .sp-item code { display: block; background: #f3f4f6; padding: 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; word-break: break-all; margin-top: 0.25rem; }
        .checkbox-label { display: flex; align-items: center; gap: 0.5rem; }
        .checkbox-label input[type="checkbox"] { width: auto; }
        .hidden { display: none; }
        #loading { display: none; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>{{ __('beartropy-saml2::saml2.admin.title') }}</h1>
        <a href="{{ url('/') }}">← {{ __('beartropy-saml2::saml2.admin.back_to_app') }}</a>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
