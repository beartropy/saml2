<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('beartropy-saml2::saml2.setup.title') }}</title>
    <style>
        :root {
            --bg-color: #f8fafc;
            --text-color: #334155;
            --heading-color: #0f172a;
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --success-color: #10b981;
            --success-hover: #059669;
            --danger-color: #ef4444;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --input-bg: #ffffff;
            --input-border: #cbd5e1;
            --input-focus: #3b82f6;
            --ring-color: rgba(59, 130, 246, 0.5);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        header {
            text-align: center;
            margin-bottom: 3rem;
        }

        h1 {
            color: var(--heading-color);
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            margin-bottom: 0.75rem;
        }

        .subtitle {
            color: #64748b;
            font-size: 1.125rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.95rem;
        }

        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }

        .grid {
            display: grid;
            gap: 2rem;
            grid-template-columns: 1fr;
        }
        @media (min-width: 1024px) {
            .grid { grid-template-columns: 400px 1fr; align-items: start; }
        }

        .card {
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border-color);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .card-header {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
            background: #f8fafc;
        }

        .card-header h2 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--heading-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
            flex: 1;
        }

        .card-desc {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .form-group { margin-bottom: 1.25rem; }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        input, textarea, select {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--input-border);
            border-radius: 0.5rem;
            background-color: var(--input-bg);
            font-size: 0.95rem;
            color: var(--heading-color);
            transition: all 0.2s;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--input-focus);
            box-shadow: 0 0 0 3px var(--ring-color);
        }

        input[readonly], textarea[readonly] {
            background-color: #f1f5f9;
            color: #64748b;
            cursor: default;
        }

        textarea {
            font-family: 'Menlo', 'Monaco', 'Courier New', monospace;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .input-group {
            display: flex;
            position: relative;
        }

        .input-group input {
            padding-right: 3rem;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        .input-action-btn {
            position: absolute;
            right: 0.25rem;
            top: 0.25rem;
            bottom: 0.25rem;
            padding: 0 0.75rem;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            transition: all 0.2s;
            font-size: 1rem;
            text-decoration: none;
        }

        .input-action-btn:hover {
            background: #f8fafc;
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .textarea-action-btn {
            position: absolute;
            right: 1rem;
            top: 0.25rem;
            height: 2.5rem;
            padding: 0 0.75rem;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            transition: all 0.2s;
            font-size: 1rem;
            text-decoration: none;
        }

        .textarea-action-btn:hover {
            background: #f8fafc;
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Tabs */
        .tabs {
            display: flex;
            background: #f1f5f9;
            padding: 0.25rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .tab {
            flex: 1;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            border: none;
            background: transparent;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .tab:hover { color: #334155; }

        .tab.active {
            background: #ffffff;
            color: var(--primary-color);
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .tab-content { display: none; animation: fadeIn 0.3s ease; }
        .tab-content.active { display: block; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            width: 100%;
            transition: all 0.2s;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--primary-color);
            color: #fff;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3);
        }
        .btn-primary:active { transform: translateY(0); }

        .btn-success {
            background: var(--success-color);
            color: #fff;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }
        .btn-success:hover {
            background: var(--success-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
        }

        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .row {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: 1fr;
        }
        @media (min-width: 640px) {
            .row { grid-template-columns: 1fr 1fr; }
        }

        .hint {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.5rem;
            font-style: italic;
        }

        .error-text {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .hidden { display: none !important; }

        /* Spinner */
        #loading-spinner {
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* SVG Icons */
        .icon-copy {
            width: 1.25em; height: 1.25em; stroke-width: 2; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>{{ __('beartropy-saml2::saml2.setup.title') }}</h1>
            <p class="subtitle">{{ __('beartropy-saml2::saml2.setup.subtitle') }}</p>
        </header>

        @if($error)
            <div class="alert alert-error">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="min-width: 24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $error }}</span>
            </div>
        @endif

        @if($success)
            <div class="alert alert-success">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="min-width: 24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $success }}</span>
            </div>
        @endif

        <div class="grid">
            {{-- SP Metadata Section --}}
            <div class="card">
                <div class="card-header">
                    <h2>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        {{ __('beartropy-saml2::saml2.setup.sp_metadata') }}
                    </h2>
                </div>
                <div class="card-body">
                    <p class="card-desc">{{ __('beartropy-saml2::saml2.setup.sp_metadata_desc') }}</p>

                    <div class="form-group">
                        <label>Entity ID</label>
                        <div class="input-group">
                            <input type="text" readonly value="{{ $spEntityId }}" id="sp-entity-id">
                            <button type="button" class="input-action-btn" onclick="copyToClipboard('sp-entity-id')" title="Copy">
                                <svg class="icon-copy" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>ACS URL</label>
                        <div class="input-group">
                            <input type="text" readonly value="{{ $spAcsUrl }}" id="sp-acs-url">
                            <button type="button" class="input-action-btn" onclick="copyToClipboard('sp-acs-url')" title="Copy">
                                <svg class="icon-copy" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('beartropy-saml2::saml2.setup.metadata_url') }}</label>
                        <div class="input-group">
                            <input type="text" readonly value="{{ $spMetadataUrl }}" id="sp-metadata-url">
                            <a href="{{ $spMetadataUrl }}" target="_blank" class="input-action-btn" title="Open">
                                <svg class="icon-copy" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            </a>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label>{{ __('beartropy-saml2::saml2.setup.metadata_xml') }}</label>
                        <div style="position: relative;">
                            <textarea readonly rows="6" id="sp-metadata-xml" style="resize: none;">{{ $spMetadataXml }}</textarea>
                            <button type="button" class="textarea-action-btn" onclick="copyToClipboard('sp-metadata-xml')" title="Copy">
                                <svg class="icon-copy" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- IDP Configuration Section --}}
            <div class="card">
                <div class="card-header">
                    <h2>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.131A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.2-2.858.567-4.171"></path></svg>
                        {{ __('beartropy-saml2::saml2.setup.idp_config') }}
                    </h2>
                </div>
                <div class="card-body">
                    <div class="tabs">
                        <button type="button" class="tab {{ $inputMethod === 'url' ? 'active' : '' }}" onclick="showTab('url')">{{ __('beartropy-saml2::saml2.setup.from_url') }}</button>
                        <button type="button" class="tab {{ $inputMethod === 'text' ? 'active' : '' }}" onclick="showTab('text')">{{ __('beartropy-saml2::saml2.setup.from_text') }}</button>
                        <button type="button" class="tab {{ $inputMethod === 'form' || $inputMethod === 'interactive' ? 'active' : '' }}" onclick="showTab('form')">{{ __('beartropy-saml2::saml2.setup.manual') }}</button>
                    </div>

                    {{-- URL Tab --}}
                    <div id="tab-url" class="tab-content {{ $inputMethod === 'url' ? 'active' : '' }}">
                        <div class="form-group">
                            <label>{{ __('beartropy-saml2::saml2.setup.idp_metadata_url') }}</label>
                            <input type="url" id="metadata-url" placeholder="https://idp.example.com/metadata">
                            <p id="url-error" class="error-text hidden"></p>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="fetchMetadata()" id="fetch-btn">
                            <span id="fetch-spinner" class="hidden"><span id="loading-spinner"></span></span>
                            <span id="fetch-text">{{ __('beartropy-saml2::saml2.setup.fetch') }}</span>
                        </button>
                        <p class="hint">{{ __('beartropy-saml2::saml2.setup.fetch_client_note') }}</p>
                    </div>

                    {{-- Text Tab --}}
                    <div id="tab-text" class="tab-content {{ $inputMethod === 'text' ? 'active' : '' }}">
                        <form method="POST" action="{{ route('saml2.setup.parse-text') }}">
                            @csrf
                            <div class="form-group">
                                <label>{{ __('beartropy-saml2::saml2.setup.paste_xml') }}</label>
                                <textarea name="metadata_text" rows="10" placeholder="<EntityDescriptor xmlns=...>&#10;  ... &#10;</EntityDescriptor>"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('beartropy-saml2::saml2.setup.parse') }}</button>
                        </form>
                    </div>

                    {{-- Form Tab (Manual or after parsing) --}}
                    <div id="tab-form" class="tab-content {{ $inputMethod === 'form' || $inputMethod === 'interactive' ? 'active' : '' }}">
                        <form method="POST" action="{{ route('saml2.setup.save') }}" id="idp-form">
                            @csrf
                            <input type="hidden" name="metadata_url" id="form-metadata-url" value="{{ $formData['metadata_url'] ?? '' }}">

                            <div class="row">
                                <div class="form-group">
                                    <label>{{ __('beartropy-saml2::saml2.setup.idp_key') }} *</label>
                                    <input type="text" name="idp_key" id="form-idp-key" value="{{ $formData['idp_key'] ?? '' }}" placeholder="my-idp" required>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('beartropy-saml2::saml2.setup.idp_name') }} *</label>
                                    <input type="text" name="idp_name" id="form-idp-name" value="{{ $formData['idp_name'] ?? '' }}" placeholder="My Identity Provider" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Entity ID *</label>
                                <input type="text" name="entity_id" id="form-entity-id" value="{{ $formData['entity_id'] ?? '' }}" placeholder="https://idp.example.com/entity" required>
                            </div>

                            <div class="form-group">
                                <label>SSO URL *</label>
                                <input type="url" name="sso_url" id="form-sso-url" value="{{ $formData['sso_url'] ?? '' }}" placeholder="https://idp.example.com/sso" required>
                            </div>

                            <div class="form-group">
                                <label>SLO URL <span style="font-weight: normal; color: #94a3b8;">({{ __('beartropy-saml2::saml2.setup.optional') }})</span></label>
                                <input type="url" name="slo_url" id="form-slo-url" value="{{ $formData['slo_url'] ?? '' }}" placeholder="https://idp.example.com/slo">
                            </div>

                            <div class="form-group">
                                <label>{{ __('beartropy-saml2::saml2.setup.x509_cert') }} *</label>
                                <textarea name="x509_cert" id="form-x509-cert" rows="6" placeholder="MIICx..." required>{{ $formData['x509_cert'] ?? '' }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-success" style="margin-top: 1rem;">{{ __('beartropy-saml2::saml2.setup.save_complete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(elementId) {
            const el = document.getElementById(elementId);
            navigator.clipboard.writeText(el.value);

            // Visual feedback
            const btn = document.querySelector(`button[onclick="copyToClipboard('${elementId}')"]`);
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<svg class="icon-copy" viewBox="0 0 24 24" style="color: var(--success-color);"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 1000);
        }

        function showTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelector('[onclick="showTab(\'' + tab + '\')"]').classList.add('active');
            document.getElementById('tab-' + tab).classList.add('active');
        }

        async function fetchMetadata() {
            const url = document.getElementById('metadata-url').value;
            const errorEl = document.getElementById('url-error');
            const btn = document.getElementById('fetch-btn');
            const spinner = document.getElementById('fetch-spinner');
            const text = document.getElementById('fetch-text');

            if (!url) {
                errorEl.innerHTML = `
                    <svg width="16" height="16" fill="none" class="icon-copy" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    {{ __('beartropy-saml2::saml2.errors.url_required') }}`;
                errorEl.classList.remove('hidden');
                return;
            }

            errorEl.classList.add('hidden');
            btn.disabled = true;
            spinner.classList.remove('hidden');
            text.textContent = '{{ __('beartropy-saml2::saml2.setup.loading') }}';

            try {
                // Try client-side fetch first (uses user's network)
                const response = await fetch(url);
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const xml = await response.text();

                // Send to server for parsing only
                const parseResponse = await fetch('{{ route('saml2.setup.parse-xml') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ xml: xml, source_url: url })
                });

                const result = await parseResponse.json();
                handleFetchResult(result, url);
            } catch (e) {
                // If CORS or network error, offer server-side fallback
                if (confirm('{{ __('beartropy-saml2::saml2.errors.cors_fallback') }}')) {
                    try {
                        const response = await fetch('{{ route('saml2.setup.fetch-url') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ url: url })
                        });
                        const result = await response.json();
                        handleFetchResult(result, url);
                    } catch (e2) {
                        errorEl.textContent = '{{ __('beartropy-saml2::saml2.errors.fetch_failed') }}: ' + e2.message;
                        errorEl.classList.remove('hidden');
                    }
                } else {
                    errorEl.textContent = '{{ __('beartropy-saml2::saml2.errors.fetch_failed') }}: ' + e.message;
                    errorEl.classList.remove('hidden');
                }
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                text.textContent = '{{ __('beartropy-saml2::saml2.setup.fetch') }}';
            }
        }

        function handleFetchResult(result, url) {
            const errorEl = document.getElementById('url-error');
            if (result.success) {
                document.getElementById('form-entity-id').value = result.data.entity_id || '';
                document.getElementById('form-sso-url').value = result.data.sso_url || '';
                document.getElementById('form-slo-url').value = result.data.slo_url || '';
                document.getElementById('form-x509-cert').value = result.data.x509_cert || '';
                document.getElementById('form-idp-key').value = result.data.idp_key || '';
                document.getElementById('form-idp-name').value = result.data.idp_name || '';
                document.getElementById('form-metadata-url').value = result.data.metadata_url || url;
                showTab('form');
            } else {
                throw new Error(result.error);
            }
        }
    </script>
</body>
</html>
