<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('beartropy-saml2::saml2.setup.success_title') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; min-height: 100vh; padding: 2rem 1rem; }
        .container { max-width: 900px; margin: 0 auto; }
        .success-banner { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; padding: 2rem; border-radius: 0.75rem; text-align: center; margin-bottom: 2rem; }
        .success-banner h1 { font-size: 1.875rem; margin-bottom: 0.5rem; }
        .success-banner p { opacity: 0.9; }
        .card { background: #fff; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 1.5rem; overflow: hidden; }
        .card-header { background: #f9fafb; padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
        .card-header h2 { font-size: 1.125rem; color: #111827; display: flex; align-items: center; gap: 0.5rem; }
        .card-body { padding: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 0.25rem; }
        .value-box { background: #f3f4f6; padding: 0.75rem; border-radius: 0.5rem; font-family: monospace; font-size: 0.875rem; word-break: break-all; position: relative; }
        .copy-btn { position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.25rem 0.5rem; background: #e5e7eb; border: none; border-radius: 0.25rem; cursor: pointer; font-size: 0.75rem; }
        .copy-btn:hover { background: #d1d5db; }
        code { background: #f3f4f6; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.875rem; }
        pre { background: #1f2937; color: #f9fafb; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-size: 0.875rem; }
        .tips { list-style: none; }
        .tips li { padding: 0.75rem 0; border-bottom: 1px solid #e5e7eb; display: flex; gap: 0.75rem; }
        .tips li:last-child { border-bottom: none; }
        .tips .icon { font-size: 1.25rem; }
        .tips .content h4 { font-size: 0.875rem; color: #111827; margin-bottom: 0.25rem; }
        .tips .content p { font-size: 0.875rem; color: #6b7280; }
        .grid { display: grid; gap: 1.5rem; }
        @media (min-width: 768px) { .grid-2 { grid-template-columns: 1fr 1fr; } }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-outline { background: transparent; border: 1px solid #d1d5db; color: #374151; }
        .btn-outline:hover { background: #f3f4f6; }
        .actions { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 2rem; justify-content: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-banner">
            <h1>✅ {{ __('beartropy-saml2::saml2.setup.success_title') }}</h1>
            <p>{{ __('beartropy-saml2::saml2.setup.success_subtitle') }}</p>
        </div>

        <div class="grid grid-2">
            {{-- SP Metadata to share with IDP --}}
            <div class="card">
                <div class="card-header">
                    <h2>📋 {{ __('beartropy-saml2::saml2.setup.sp_metadata') }}</h2>
                </div>
                <div class="card-body">
                    <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">{{ __('beartropy-saml2::saml2.setup.sp_metadata_desc') }}</p>
                    
                    <div class="form-group">
                        <label>Entity ID</label>
                        <div class="value-box" id="sp-entity-id">
                            {{ $spMetadata['entityId'] }}
                            <button class="copy-btn" onclick="copy('sp-entity-id')">📋</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>ACS URL</label>
                        <div class="value-box" id="sp-acs-url">
                            {{ $spMetadata['acsUrl'] }}
                            <button class="copy-btn" onclick="copy('sp-acs-url')">📋</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('beartropy-saml2::saml2.setup.metadata_url') }}</label>
                        <div class="value-box" id="sp-metadata-url">
                            {{ $spMetadata['metadataUrl'] }}
                            <button class="copy-btn" onclick="copy('sp-metadata-url')">📋</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- IDP Configured --}}
            <div class="card">
                <div class="card-header">
                    <h2>🏢 {{ __('beartropy-saml2::saml2.setup.idp_configured') }}</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Key</label>
                        <div class="value-box">{{ $idp->key }}</div>
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <div class="value-box">{{ $idp->name }}</div>
                    </div>
                    <div class="form-group">
                        <label>Entity ID</label>
                        <div class="value-box">{{ $idp->entity_id }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Routes --}}
        <div class="card">
            <div class="card-header">
                <h2>🔗 {{ __('beartropy-saml2::saml2.setup.routes') }}</h2>
            </div>
            <div class="card-body">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label>{{ __('beartropy-saml2::saml2.setup.login_route') }}</label>
                        <div class="value-box" id="login-route">
                            {{ route('saml2.login', ['idp' => $idp->key]) }}
                            <button class="copy-btn" onclick="copy('login-route')">📋</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('beartropy-saml2::saml2.setup.logout_route') }}</label>
                        <div class="value-box" id="logout-route">
                            {{ route('saml2.logout', ['idp' => $idp->key]) }}
                            <button class="copy-btn" onclick="copy('logout-route')">📋</button>
                        </div>
                    </div>
                </div>
                <p style="color: #6b7280; font-size: 0.875rem; margin-top: 0.5rem;">{{ __('beartropy-saml2::saml2.setup.route_hint') }}</p>
            </div>
        </div>

        {{-- Next Steps --}}
        <div class="card">
            <div class="card-header">
                <h2>📝 {{ __('beartropy-saml2::saml2.setup.next_steps') }}</h2>
            </div>
            <div class="card-body">
                <ul class="tips">
                    <li>
                        <span class="icon">1️⃣</span>
                        <div class="content">
                            <h4>{{ __('beartropy-saml2::saml2.setup.step_listener_title') }}</h4>
                            <p>{{ __('beartropy-saml2::saml2.setup.step_listener_desc') }}</p>
                            <pre>php artisan saml2:publish-listener</pre>
                        </div>
                    </li>
                    <li>
                        <span class="icon">2️⃣</span>
                        <div class="content">
                            <h4>{{ __('beartropy-saml2::saml2.setup.step_config_title') }}</h4>
                            <p>{{ __('beartropy-saml2::saml2.setup.step_config_desc') }}</p>
                            <pre>config/beartropy-saml2.php</pre>
                        </div>
                    </li>
                    <li>
                        <span class="icon">3️⃣</span>
                        <div class="content">
                            <h4>{{ __('beartropy-saml2::saml2.setup.step_test_title') }}</h4>
                            <p>{{ __('beartropy-saml2::saml2.setup.step_test_desc') }}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="actions">
            <a href="{{ route('saml2.login', ['idp' => $idp->key]) }}" class="btn btn-primary">
                🔐 {{ __('beartropy-saml2::saml2.setup.test_login') }}
            </a>
            @if(config('beartropy-saml2.admin_enabled', true))
                <a href="{{ route('saml2.admin.index') }}" class="btn btn-outline">
                    ⚙️ {{ __('beartropy-saml2::saml2.setup.go_to_admin') }}
                </a>
            @endif
            <a href="{{ url('/') }}" class="btn btn-outline">
                🏠 {{ __('beartropy-saml2::saml2.setup.go_to_app') }}
            </a>
        </div>
    </div>

    <script>
        function copy(id) {
            const el = document.getElementById(id);
            const text = el.innerText.replace('📋', '').trim();
            navigator.clipboard.writeText(text);
        }
    </script>
</body>
</html>
