@php
    $hasMaxWidth = $hasMaxWidth ?? true;
@endphp
<style>
    .saml2-admin-wrapper * { box-sizing: border-box; }
    .saml2-admin-wrapper { margin: 0 auto; }
    @if($hasMaxWidth)
    .saml2-admin-wrapper { max-width: 1200px; }
    @endif

    /* Light mode (default) */
    .saml2-admin-wrapper .card { background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
    .saml2-admin-wrapper .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
    .saml2-admin-wrapper .card-header h2 { font-size: 1.125rem; color: #111827; margin: 0; }
    .saml2-admin-wrapper .card-body { padding: 1.5rem; }
    .saml2-admin-wrapper .alert { padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 1rem; }
    .saml2-admin-wrapper .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .saml2-admin-wrapper .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .saml2-admin-wrapper table { width: 100%; border-collapse: collapse; }
    .saml2-admin-wrapper th, .saml2-admin-wrapper td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
    .saml2-admin-wrapper th { background: #f9fafb; font-weight: 600; font-size: 0.75rem; color: #6b7280; text-transform: uppercase; }
    .saml2-admin-wrapper tr:hover { background: #f9fafb; }
    .saml2-admin-wrapper .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; text-decoration: none; cursor: pointer; border: none; }
    .saml2-admin-wrapper .btn-primary { background: #2563eb; color: #fff; }
    .saml2-admin-wrapper .btn-primary:hover { background: #1d4ed8; }
    .saml2-admin-wrapper .btn-success { background: #059669; color: #fff; }
    .saml2-admin-wrapper .btn-success:hover { background: #047857; }
    .saml2-admin-wrapper .btn-danger { background: #dc2626; color: #fff; }
    .saml2-admin-wrapper .btn-danger:hover { background: #b91c1c; }
    .saml2-admin-wrapper .btn-secondary { background: #6b7280; color: #fff; }
    .saml2-admin-wrapper .btn-secondary:hover { background: #4b5563; }
    .saml2-admin-wrapper .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
    .saml2-admin-wrapper .btn-outline { background: transparent; border: 1px solid #d1d5db; color: #374151; }
    .saml2-admin-wrapper .btn-outline:hover { background: #f3f4f6; }
    .saml2-admin-wrapper .badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
    .saml2-admin-wrapper .badge-success { background: #d1fae5; color: #065f46; }
    .saml2-admin-wrapper .badge-gray { background: #e5e7eb; color: #6b7280; }
    .saml2-admin-wrapper .form-group { margin-bottom: 1rem; }
    .saml2-admin-wrapper label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
    .saml2-admin-wrapper input, .saml2-admin-wrapper textarea, .saml2-admin-wrapper select { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; background: #fff; color: #111827; }
    .saml2-admin-wrapper input:focus, .saml2-admin-wrapper textarea:focus, .saml2-admin-wrapper select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .saml2-admin-wrapper textarea { font-family: monospace; resize: vertical; }
    .saml2-admin-wrapper .row { display: grid; gap: 1rem; }
    .saml2-admin-wrapper .row-2 { grid-template-columns: 1fr 1fr; }
    .saml2-admin-wrapper .row-3 { grid-template-columns: 1fr 1fr 1fr; }
    @media (max-width: 768px) { .saml2-admin-wrapper .row-2, .saml2-admin-wrapper .row-3 { grid-template-columns: 1fr; } }
    .saml2-admin-wrapper .actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .saml2-admin-wrapper .text-muted { color: #6b7280; font-size: 0.875rem; }
    .saml2-admin-wrapper .text-mono { font-family: monospace; font-size: 0.75rem; }
    .saml2-admin-wrapper .sp-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
    .saml2-admin-wrapper .sp-item label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; }
    .saml2-admin-wrapper .sp-item code { display: block; background: #f3f4f6; padding: 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; word-break: break-all; margin-top: 0.25rem; color: #111827; }
    .saml2-admin-wrapper .checkbox-label { display: flex; align-items: center; gap: 0.5rem; }
    .saml2-admin-wrapper .checkbox-label input[type="checkbox"] { width: auto; }
    .saml2-admin-wrapper .hidden { display: none; }
    .saml2-admin-wrapper #loading { display: none; }

    /* Dark mode - supports both prefers-color-scheme and .dark class */
    @media (prefers-color-scheme: dark) {
        .saml2-admin-wrapper .card { background: #1f2937; box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
        .saml2-admin-wrapper .card-header { border-bottom-color: #374151; }
        .saml2-admin-wrapper .card-header h2 { color: #f9fafb; }
        .saml2-admin-wrapper .alert-success { background: #064e3b; color: #a7f3d0; border-color: #065f46; }
        .saml2-admin-wrapper .alert-error { background: #7f1d1d; color: #fecaca; border-color: #991b1b; }
        .saml2-admin-wrapper th, .saml2-admin-wrapper td { border-bottom-color: #374151; }
        .saml2-admin-wrapper th { background: #111827; color: #9ca3af; }
        .saml2-admin-wrapper tr:hover { background: #111827; }
        .saml2-admin-wrapper .btn-outline { border-color: #4b5563; color: #d1d5db; }
        .saml2-admin-wrapper .btn-outline:hover { background: #374151; }
        .saml2-admin-wrapper .badge-success { background: #064e3b; color: #a7f3d0; }
        .saml2-admin-wrapper .badge-gray { background: #374151; color: #9ca3af; }
        .saml2-admin-wrapper label { color: #d1d5db; }
        .saml2-admin-wrapper input, .saml2-admin-wrapper textarea, .saml2-admin-wrapper select { background: #111827; border-color: #4b5563; color: #f9fafb; }
        .saml2-admin-wrapper input:focus, .saml2-admin-wrapper textarea:focus, .saml2-admin-wrapper select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.2); }
        .saml2-admin-wrapper .text-muted { color: #9ca3af; }
        .saml2-admin-wrapper .sp-item label { color: #9ca3af; }
        .saml2-admin-wrapper .sp-item code { background: #111827; color: #f9fafb; }
    }

    /* Dark mode via .dark class on html/body */
    .dark .saml2-admin-wrapper .card { background: #1f2937; box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
    .dark .saml2-admin-wrapper .card-header { border-bottom-color: #374151; }
    .dark .saml2-admin-wrapper .card-header h2 { color: #f9fafb; }
    .dark .saml2-admin-wrapper .alert-success { background: #064e3b; color: #a7f3d0; border-color: #065f46; }
    .dark .saml2-admin-wrapper .alert-error { background: #7f1d1d; color: #fecaca; border-color: #991b1b; }
    .dark .saml2-admin-wrapper th, .dark .saml2-admin-wrapper td { border-bottom-color: #374151; }
    .dark .saml2-admin-wrapper th { background: #111827; color: #9ca3af; }
    .dark .saml2-admin-wrapper tr:hover { background: #111827; }
    .dark .saml2-admin-wrapper .btn-outline { border-color: #4b5563; color: #d1d5db; }
    .dark .saml2-admin-wrapper .btn-outline:hover { background: #374151; }
    .dark .saml2-admin-wrapper .badge-success { background: #064e3b; color: #a7f3d0; }
    .dark .saml2-admin-wrapper .badge-gray { background: #374151; color: #9ca3af; }
    .dark .saml2-admin-wrapper label { color: #d1d5db; }
    .dark .saml2-admin-wrapper input, .dark .saml2-admin-wrapper textarea, .dark .saml2-admin-wrapper select { background: #111827; border-color: #4b5563; color: #f9fafb; }
    .dark .saml2-admin-wrapper input:focus, .dark .saml2-admin-wrapper textarea:focus, .dark .saml2-admin-wrapper select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.2); }
    .dark .saml2-admin-wrapper .text-muted { color: #9ca3af; }
    .dark .saml2-admin-wrapper .sp-item label { color: #9ca3af; }
    .dark .saml2-admin-wrapper .sp-item code { background: #111827; color: #f9fafb; }
</style>

