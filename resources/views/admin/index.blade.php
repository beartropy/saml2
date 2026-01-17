@extends(config('beartropy-saml2.layout', 'beartropy-saml2::admin.partials.layout'))

@section('title', __('beartropy-saml2::saml2.admin.dashboard'))

@section('content')
    {{-- SP Metadata Card --}}
    <div class="card">
        <div class="card-header">
            <h2>{{ __('beartropy-saml2::saml2.admin.sp_info') }}</h2>
            <a href="{{ $spMetadata['metadataUrl'] }}" target="_blank" class="btn btn-outline btn-sm">
                {{ __('beartropy-saml2::saml2.admin.view_metadata') }}
            </a>
        </div>
        <div class="card-body">
            <div class="sp-info">
                <div class="sp-item">
                    <label>Entity ID</label>
                    <code>{{ $spMetadata['entityId'] }}</code>
                </div>
                <div class="sp-item">
                    <label>ACS URL</label>
                    <code>{{ $spMetadata['acsUrl'] }}</code>
                </div>
                <div class="sp-item">
                    <label>Metadata URL</label>
                    <code>{{ $spMetadata['metadataUrl'] }}</code>
                </div>
            </div>
        </div>
    </div>

    {{-- IDPs List --}}
    <div class="card">
        <div class="card-header">
            <h2>{{ __('beartropy-saml2::saml2.admin.idps') }} ({{ $idps->count() }})</h2>
            <a href="{{ route('saml2.admin.idp.create') }}" class="btn btn-primary btn-sm">
                + {{ __('beartropy-saml2::saml2.admin.add_idp') }}
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($idps->isEmpty())
                <div style="padding: 3rem; text-align: center;">
                    <p class="text-muted">{{ __('beartropy-saml2::saml2.admin.no_idps') }}</p>
                    <a href="{{ route('saml2.admin.idp.create') }}" class="btn btn-primary" style="margin-top: 1rem;">
                        {{ __('beartropy-saml2::saml2.admin.add_first_idp') }}
                    </a>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('beartropy-saml2::saml2.admin.key') }}</th>
                            <th>{{ __('beartropy-saml2::saml2.admin.name') }}</th>
                            <th>{{ __('beartropy-saml2::saml2.admin.entity_id') }}</th>
                            <th>{{ __('beartropy-saml2::saml2.admin.status') }}</th>
                            <th>{{ __('beartropy-saml2::saml2.admin.mapping') }}</th>
                            <th style="text-align: right;">{{ __('beartropy-saml2::saml2.admin.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($idps as $idp)
                            <tr>
                                <td><code class="text-mono">{{ $idp->key }}</code></td>
                                <td>{{ $idp->name }}</td>
                                <td class="text-mono text-muted" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">{{ $idp->entity_id }}</td>
                                <td>
                                    @if($idp->is_active)
                                        <span class="badge badge-success">{{ __('beartropy-saml2::saml2.admin.active') }}</span>
                                    @else
                                        <span class="badge badge-gray">{{ __('beartropy-saml2::saml2.admin.inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($idp->hasCustomAttributeMapping())
                                        <span class="badge badge-success">{{ __('beartropy-saml2::saml2.admin.custom') }}</span>
                                    @else
                                        <span class="badge badge-gray">{{ __('beartropy-saml2::saml2.admin.global') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="actions" style="justify-content: flex-end;">
                                        <a href="{{ route('saml2.admin.idp.edit', $idp->id) }}" class="btn btn-outline btn-sm">
                                            {{ __('beartropy-saml2::saml2.admin.edit') }}
                                        </a>
                                        <a href="{{ route('saml2.admin.idp.mapping', $idp->id) }}" class="btn btn-outline btn-sm">
                                            {{ __('beartropy-saml2::saml2.admin.mapping') }}
                                        </a>
                                        <form method="POST" action="{{ route('saml2.admin.idp.toggle', $idp->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                {{ $idp->is_active ? __('beartropy-saml2::saml2.admin.deactivate') : __('beartropy-saml2::saml2.admin.activate') }}
                                            </button>
                                        </form>
                                        @if($idp->metadata_url)
                                            <form method="POST" action="{{ route('saml2.admin.idp.refresh', $idp->id) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline btn-sm" title="{{ __('beartropy-saml2::saml2.admin.refresh_metadata') }}">
                                                    ↻
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('saml2.admin.idp.delete', $idp->id) }}" style="display: inline;" onsubmit="return confirm('{{ __('beartropy-saml2::saml2.admin.confirm_delete') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                {{ __('beartropy-saml2::saml2.admin.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
