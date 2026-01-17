@extends('beartropy-saml2::admin.partials.layout')

@section('title', $isEdit ? __('beartropy-saml2::saml2.admin.edit_idp') : __('beartropy-saml2::saml2.admin.create_idp'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>{{ $isEdit ? __('beartropy-saml2::saml2.admin.edit_idp') : __('beartropy-saml2::saml2.admin.create_idp') }}</h2>
        </div>
        <div class="card-body">
            {{-- Import from URL section --}}
            @if(!$isEdit)
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <h4 style="font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('beartropy-saml2::saml2.admin.import_from_url') }}</h4>
                    <div class="row row-2" style="align-items: flex-end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>{{ __('beartropy-saml2::saml2.setup.idp_metadata_url') }}</label>
                            <input type="url" id="import-url" placeholder="https://idp.example.com/metadata">
                        </div>
                        <div>
                            <button type="button" id="import-btn" class="btn btn-secondary" onclick="fetchMetadata()">
                                {{ __('beartropy-saml2::saml2.setup.fetch') }}
                            </button>
                        </div>
                    </div>
                    <p id="import-error" class="text-muted" style="margin-top: 0.5rem; color: #dc2626; display: none;"></p>
                    <p class="text-muted" style="margin-top: 0.5rem;">{{ __('beartropy-saml2::saml2.setup.fetch_client_note') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ $isEdit ? route('saml2.admin.idp.update', $idp->id) : route('saml2.admin.idp.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row row-2">
                    <div class="form-group">
                        <label>{{ __('beartropy-saml2::saml2.setup.idp_key') }} *</label>
                        <input type="text" name="idp_key" id="idp_key" value="{{ old('idp_key', $idp->key ?? '') }}" placeholder="my-idp" required {{ $isEdit ? 'readonly' : '' }}>
                        @error('idp_key') <p style="color: #dc2626; font-size: 0.875rem;">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label>{{ __('beartropy-saml2::saml2.setup.idp_name') }} *</label>
                        <input type="text" name="idp_name" id="idp_name" value="{{ old('idp_name', $idp->name ?? '') }}" placeholder="My Identity Provider" required>
                        @error('idp_name') <p style="color: #dc2626; font-size: 0.875rem;">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Entity ID *</label>
                    <input type="text" name="entity_id" id="entity_id" value="{{ old('entity_id', $idp->entity_id ?? '') }}" placeholder="https://idp.example.com/entity" required>
                    @error('entity_id') <p style="color: #dc2626; font-size: 0.875rem;">{{ $message }}</p> @enderror
                </div>

                <div class="row row-2">
                    <div class="form-group">
                        <label>SSO URL *</label>
                        <input type="url" name="sso_url" id="sso_url" value="{{ old('sso_url', $idp->sso_url ?? '') }}" placeholder="https://idp.example.com/sso" required>
                        @error('sso_url') <p style="color: #dc2626; font-size: 0.875rem;">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label>SLO URL ({{ __('beartropy-saml2::saml2.setup.optional') }})</label>
                        <input type="url" name="slo_url" id="slo_url" value="{{ old('slo_url', $idp->slo_url ?? '') }}" placeholder="https://idp.example.com/slo">
                        @error('slo_url') <p style="color: #dc2626; font-size: 0.875rem;">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ __('beartropy-saml2::saml2.setup.x509_cert') }} *</label>
                    <textarea name="x509_cert" id="x509_cert" rows="4" placeholder="MIICx..." required>{{ old('x509_cert', $idp->x509_cert ?? '') }}</textarea>
                    @error('x509_cert') <p style="color: #dc2626; font-size: 0.875rem;">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label>{{ __('beartropy-saml2::saml2.setup.metadata_url') }} ({{ __('beartropy-saml2::saml2.setup.optional') }})</label>
                    <input type="url" name="metadata_url" id="metadata_url" value="{{ old('metadata_url', $idp->metadata_url ?? '') }}" placeholder="https://idp.example.com/metadata">
                    <p class="text-muted">{{ __('beartropy-saml2::saml2.admin.metadata_url_hint') }}</p>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $idp->is_active ?? true) ? 'checked' : '' }}>
                        {{ __('beartropy-saml2::saml2.admin.is_active') }}
                    </label>
                </div>

                <div class="actions" style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-success">
                        {{ $isEdit ? __('beartropy-saml2::saml2.admin.save_changes') : __('beartropy-saml2::saml2.admin.create_idp') }}
                    </button>
                    <a href="{{ route('saml2.admin.index') }}" class="btn btn-outline">
                        {{ __('beartropy-saml2::saml2.admin.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    async function fetchMetadata() {
        const url = document.getElementById('import-url').value;
        const errorEl = document.getElementById('import-error');
        const btn = document.getElementById('import-btn');

        if (!url) {
            errorEl.textContent = '{{ __('beartropy-saml2::saml2.errors.url_required') }}';
            errorEl.style.display = 'block';
            return;
        }

        errorEl.style.display = 'none';
        btn.disabled = true;
        btn.textContent = '{{ __('beartropy-saml2::saml2.setup.loading') }}';

        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const xml = await response.text();

            const parseResponse = await fetch('{{ route('saml2.admin.parse-metadata') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ xml: xml })
            });

            const result = await parseResponse.json();

            if (result.success) {
                document.getElementById('entity_id').value = result.data.entity_id || '';
                document.getElementById('sso_url').value = result.data.sso_url || '';
                document.getElementById('slo_url').value = result.data.slo_url || '';
                document.getElementById('x509_cert').value = result.data.x509_cert || '';
                document.getElementById('idp_key').value = result.data.idp_key || '';
                document.getElementById('idp_name').value = result.data.idp_name || '';
                document.getElementById('metadata_url').value = url;
            } else {
                throw new Error(result.error);
            }
        } catch (e) {
            errorEl.textContent = '{{ __('beartropy-saml2::saml2.errors.fetch_failed') }}: ' + e.message;
            errorEl.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.textContent = '{{ __('beartropy-saml2::saml2.setup.fetch') }}';
        }
    }
</script>
@endsection
