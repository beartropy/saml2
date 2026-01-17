@extends('beartropy-saml2::admin.partials.layout')

@section('title', __('beartropy-saml2::saml2.admin.edit_mapping'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>{{ __('beartropy-saml2::saml2.admin.edit_mapping') }}: {{ $idp->name }}</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('saml2.admin.idp.mapping.update', $idp->id) }}">
                @csrf

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="use_global" id="use_global" value="1" {{ !$idp->hasCustomAttributeMapping() ? 'checked' : '' }} onchange="toggleMapping()">
                        {{ __('beartropy-saml2::saml2.admin.use_global_mapping') }}
                    </label>
                    <p class="text-muted">{{ __('beartropy-saml2::saml2.admin.global_mapping_hint') }}</p>
                </div>

                {{-- Global Mapping Display --}}
                <div id="global-mapping" class="{{ $idp->hasCustomAttributeMapping() ? 'hidden' : '' }}" style="margin-bottom: 1.5rem;">
                    <h4 style="font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('beartropy-saml2::saml2.admin.current_global_mapping') }}</h4>
                    <table style="width: auto;">
                        <thead>
                            <tr>
                                <th>{{ __('beartropy-saml2::saml2.admin.local_field') }}</th>
                                <th>{{ __('beartropy-saml2::saml2.admin.saml_attribute') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($globalMapping as $key => $value)
                                <tr>
                                    <td><code>{{ $key }}</code></td>
                                    <td class="text-mono text-muted">{{ $value }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-muted">{{ __('beartropy-saml2::saml2.admin.no_mapping_defined') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Custom Mapping Editor --}}
                <div id="custom-mapping" class="{{ !$idp->hasCustomAttributeMapping() ? 'hidden' : '' }}">
                    <h4 style="font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('beartropy-saml2::saml2.admin.custom_mapping') }}</h4>
                    
                    <table id="mapping-table" style="width: auto; margin-bottom: 1rem;">
                        <thead>
                            <tr>
                                <th>{{ __('beartropy-saml2::saml2.admin.local_field') }}</th>
                                <th>{{ __('beartropy-saml2::saml2.admin.saml_attribute') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $mapping = $idp->attribute_mapping ?? $globalMapping;
                                $index = 0;
                            @endphp
                            @forelse($mapping as $key => $value)
                                <tr>
                                    <td><input type="text" name="mapping_key[]" value="{{ $key }}" placeholder="email" style="width: 150px;"></td>
                                    <td><input type="text" name="mapping_value[]" value="{{ $value }}" placeholder="http://schemas..." style="width: 350px;"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">×</button></td>
                                </tr>
                                @php $index++; @endphp
                            @empty
                                <tr>
                                    <td><input type="text" name="mapping_key[]" value="" placeholder="email" style="width: 150px;"></td>
                                    <td><input type="text" name="mapping_value[]" value="" placeholder="http://schemas..." style="width: 350px;"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">×</button></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <button type="button" class="btn btn-outline btn-sm" onclick="addRow()">
                        + {{ __('beartropy-saml2::saml2.admin.add_mapping') }}
                    </button>
                </div>

                <div class="actions" style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-success">
                        {{ __('beartropy-saml2::saml2.admin.save_mapping') }}
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
    function toggleMapping() {
        const useGlobal = document.getElementById('use_global').checked;
        document.getElementById('global-mapping').classList.toggle('hidden', !useGlobal);
        document.getElementById('custom-mapping').classList.toggle('hidden', useGlobal);
    }

    function addRow() {
        const tbody = document.querySelector('#mapping-table tbody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" name="mapping_key[]" placeholder="field_name" style="width: 150px;"></td>
            <td><input type="text" name="mapping_value[]" placeholder="http://schemas..." style="width: 350px;"></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">×</button></td>
        `;
        tbody.appendChild(row);
    }

    function removeRow(btn) {
        const tbody = document.querySelector('#mapping-table tbody');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
        }
    }
</script>
@endsection
