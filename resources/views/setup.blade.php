<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('beartropy-saml2::saml2.setup.title') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif; background: #f3f4f6; min-height: 100vh; padding: 2rem 1rem; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { text-align: center; color: #111827; font-size: 1.875rem; margin-bottom: 0.5rem; }
        .subtitle { text-align: center; color: #6b7280; margin-bottom: 2rem; }
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }
        .grid { display: grid; gap: 1.5rem; }
        @media (min-width: 768px) { .grid { grid-template-columns: 1fr 1fr; } }
        .card { background: #fff; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); padding: 1.5rem; }
        .card h2 { font-size: 1.25rem; color: #111827; margin-bottom: 0.5rem; }
        .card-desc { font-size: 0.875rem; color: #6b7280; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
        input, textarea, select { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; }
        input:focus, textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        input[readonly], textarea[readonly] { background: #f9fafb; }
        textarea { font-family: monospace; resize: vertical; }
        .input-group { display: flex; }
        .input-group input { border-radius: 0.5rem 0 0 0.5rem; }
        .input-group button, .input-group a { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-left: 0; background: #e5e7eb; cursor: pointer; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #374151; }
        .input-group button:last-child, .input-group a:last-child { border-radius: 0 0.5rem 0.5rem 0; }
        .input-group button:hover, .input-group a:hover { background: #d1d5db; }
        .tabs { display: flex; border-bottom: 1px solid #e5e7eb; margin-bottom: 1rem; }
        .tab { padding: 0.5rem 1rem; font-size: 0.875rem; color: #6b7280; cursor: pointer; border: none; background: none; }
        .tab:hover { color: #374151; }
        .tab.active { color: #2563eb; border-bottom: 2px solid #2563eb; margin-bottom: -1px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.625rem 1.25rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; border: none; width: 100%; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-success { background: #16a34a; color: #fff; }
        .btn-success:hover { background: #15803d; }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .hint { font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem; }
        .error-text { color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem; }
        .hidden { display: none; }
        #loading-spinner { display: inline-block; width: 1rem; height: 1rem; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; margin-right: 0.5rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ __('beartropy-saml2::saml2.setup.title') }}</h1>
        <p class="subtitle">{{ __('beartropy-saml2::saml2.setup.subtitle') }}</p>

        @if($error)
            <div class="alert alert-error">{{ $error }}</div>
        @endif

        @if($success)
            <div class="alert alert-success">{{ $success }}</div>
        @endif

        <div class="grid">
            {{-- SP Metadata Section --}}
            <div class="card">
                <h2>{{ __('beartropy-saml2::saml2.setup.sp_metadata') }}</h2>
                <p class="card-desc">{{ __('beartropy-saml2::saml2.setup.sp_metadata_desc') }}</p>

                <div class="form-group">
                    <label>Entity ID</label>
                    <div class="input-group">
                        <input type="text" readonly value="{{ $spEntityId }}" id="sp-entity-id">
                        <button type="button" onclick="copyToClipboard('sp-entity-id')" title="Copy">📋</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>ACS URL</label>
                    <div class="input-group">
                        <input type="text" readonly value="{{ $spAcsUrl }}" id="sp-acs-url">
                        <button type="button" onclick="copyToClipboard('sp-acs-url')" title="Copy">📋</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ __('beartropy-saml2::saml2.setup.metadata_url') }}</label>
                    <div class="input-group">
                        <input type="text" readonly value="{{ $spMetadataUrl }}" id="sp-metadata-url">
                        <a href="{{ $spMetadataUrl }}" target="_blank" title="Open">🔗</a>
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ __('beartropy-saml2::saml2.setup.metadata_xml') }}</label>
                    <div style="position: relative;">
                        <textarea readonly rows="4" id="sp-metadata-xml">{{ $spMetadataXml }}</textarea>
                        <button type="button" onclick="copyToClipboard('sp-metadata-xml')" style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.25rem 0.5rem; background: #e5e7eb; border: 1px solid #d1d5db; border-radius: 0.25rem; cursor: pointer;">📋</button>
                    </div>
                </div>
            </div>

            {{-- IDP Configuration Section --}}
            <div class="card">
                <h2>{{ __('beartropy-saml2::saml2.setup.idp_config') }}</h2>

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
                            <textarea name="metadata_text" rows="6" placeholder="&lt;?xml version=&quot;1.0&quot;?&gt;..."></textarea>
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
                            <label>SLO URL ({{ __('beartropy-saml2::saml2.setup.optional') }})</label>
                            <input type="url" name="slo_url" id="form-slo-url" value="{{ $formData['slo_url'] ?? '' }}" placeholder="https://idp.example.com/slo">
                        </div>

                        <div class="form-group">
                            <label>{{ __('beartropy-saml2::saml2.setup.x509_cert') }} *</label>
                            <textarea name="x509_cert" id="form-x509-cert" rows="4" placeholder="MIICx..." required>{{ $formData['x509_cert'] ?? '' }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success">{{ __('beartropy-saml2::saml2.setup.save_complete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(elementId) {
            const el = document.getElementById(elementId);
            navigator.clipboard.writeText(el.value);
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
                errorEl.textContent = '{{ __('beartropy-saml2::saml2.errors.url_required') }}';
                errorEl.classList.remove('hidden');
                return;
            }

            errorEl.classList.add('hidden');
            btn.disabled = true;
            spinner.classList.remove('hidden');
            text.textContent = '{{ __('beartropy-saml2::saml2.setup.loading') }}';

            try {
                // Fetch from client side
                const response = await fetch(url);
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const xml = await response.text();

                // Send to server for parsing
                const parseResponse = await fetch('{{ route('saml2.setup.parse-xml') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ xml: xml, source_url: url })
                });

                const result = await parseResponse.json();

                if (result.success) {
                    // Fill form fields
                    document.getElementById('form-entity-id').value = result.data.entity_id || '';
                    document.getElementById('form-sso-url').value = result.data.sso_url || '';
                    document.getElementById('form-slo-url').value = result.data.slo_url || '';
                    document.getElementById('form-x509-cert').value = result.data.x509_cert || '';
                    document.getElementById('form-idp-key').value = result.data.idp_key || '';
                    document.getElementById('form-idp-name').value = result.data.idp_name || '';
                    document.getElementById('form-metadata-url').value = result.data.metadata_url || url;

                    // Switch to form tab
                    showTab('form');
                } else {
                    throw new Error(result.error);
                }
            } catch (e) {
                errorEl.textContent = '{{ __('beartropy-saml2::saml2.errors.fetch_failed') }}: ' + e.message;
                errorEl.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                text.textContent = '{{ __('beartropy-saml2::saml2.setup.fetch') }}';
            }
        }
    </script>
</body>
</html>
