<?php

namespace Beartropy\Saml2\Http\Controllers;

use Beartropy\Saml2\Models\Saml2Idp;
use Beartropy\Saml2\Services\MetadataParser;
use Beartropy\Saml2\Services\Saml2Service;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function __construct(
        protected Saml2Service $saml2Service,
        protected MetadataParser $metadataParser
    ) {}

    /**
     * Dashboard - list all IDPs.
     */
    public function index()
    {
        $idps = Saml2Idp::orderBy('name')->get();
        $spMetadata = $this->getSpMetadata();

        return view('beartropy-saml2::admin.index', [
            'idps' => $idps,
            'spMetadata' => $spMetadata,
        ]);
    }

    /**
     * Show create IDP form.
     */
    public function createIdp()
    {
        return view('beartropy-saml2::admin.idp-form', [
            'idp' => null,
            'isEdit' => false,
        ]);
    }

    /**
     * Store new IDP.
     */
    public function storeIdp(Request $request)
    {
        $validated = $this->validateIdp($request);

        try {
            Saml2Idp::create([
                'key' => $validated['idp_key'],
                'name' => $validated['idp_name'],
                'entity_id' => $validated['entity_id'],
                'sso_url' => $validated['sso_url'],
                'slo_url' => $validated['slo_url'] ?? null,
                'x509_cert' => $this->cleanCertificate($validated['x509_cert']),
                'metadata_url' => $validated['metadata_url'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return redirect()->route('saml2.admin.index')
                ->with('success', __('beartropy-saml2::saml2.admin.idp_created'));
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', __('beartropy-saml2::saml2.errors.save_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Show edit IDP form.
     */
    public function editIdp($id)
    {
        $idp = Saml2Idp::findOrFail($id);

        return view('beartropy-saml2::admin.idp-form', [
            'idp' => $idp,
            'isEdit' => true,
        ]);
    }

    /**
     * Update existing IDP.
     */
    public function updateIdp(Request $request, $id)
    {
        $idp = Saml2Idp::findOrFail($id);
        $validated = $this->validateIdp($request, $idp->id);

        try {
            $idp->update([
                'key' => $validated['idp_key'],
                'name' => $validated['idp_name'],
                'entity_id' => $validated['entity_id'],
                'sso_url' => $validated['sso_url'],
                'slo_url' => $validated['slo_url'] ?? null,
                'x509_cert' => $this->cleanCertificate($validated['x509_cert']),
                'metadata_url' => $validated['metadata_url'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return redirect()->route('saml2.admin.index')
                ->with('success', __('beartropy-saml2::saml2.admin.idp_updated'));
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', __('beartropy-saml2::saml2.errors.save_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Delete IDP.
     */
    public function deleteIdp($id)
    {
        $idp = Saml2Idp::findOrFail($id);
        $idp->delete();

        return redirect()->route('saml2.admin.index')
            ->with('success', __('beartropy-saml2::saml2.admin.idp_deleted'));
    }

    /**
     * Toggle IDP active status.
     */
    public function toggleIdp($id)
    {
        $idp = Saml2Idp::findOrFail($id);
        $idp->update(['is_active' => !$idp->is_active]);

        $message = $idp->is_active 
            ? __('beartropy-saml2::saml2.admin.idp_activated')
            : __('beartropy-saml2::saml2.admin.idp_deactivated');

        return redirect()->route('saml2.admin.index')->with('success', $message);
    }

    /**
     * Show attribute mapping editor.
     */
    public function editMapping($id)
    {
        $idp = Saml2Idp::findOrFail($id);
        $globalMapping = config('beartropy-saml2.attribute_mapping', []);

        return view('beartropy-saml2::admin.mapping', [
            'idp' => $idp,
            'globalMapping' => $globalMapping,
        ]);
    }

    /**
     * Update attribute mapping.
     */
    public function updateMapping(Request $request, $id)
    {
        $idp = Saml2Idp::findOrFail($id);

        if ($request->boolean('use_global')) {
            $idp->update(['attribute_mapping' => null]);
        } else {
            $mapping = [];
            $keys = $request->input('mapping_key', []);
            $values = $request->input('mapping_value', []);
            
            foreach ($keys as $index => $key) {
                if (!empty($key) && isset($values[$index])) {
                    $mapping[$key] = $values[$index];
                }
            }
            
            $idp->update(['attribute_mapping' => $mapping]);
        }

        return redirect()->route('saml2.admin.index')
            ->with('success', __('beartropy-saml2::saml2.admin.mapping_updated'));
    }

    /**
     * Refresh metadata from URL.
     */
    public function refreshMetadata($id)
    {
        $idp = Saml2Idp::findOrFail($id);

        if (!$idp->metadata_url) {
            return back()->with('error', __('beartropy-saml2::saml2.admin.no_metadata_url'));
        }

        try {
            $data = $this->metadataParser->parseFromUrl($idp->metadata_url);
            
            $idp->update([
                'entity_id' => $data['entity_id'],
                'sso_url' => $data['sso_url'],
                'slo_url' => $data['slo_url'] ?? $idp->slo_url,
                'x509_cert' => $data['x509_cert'] ?? $idp->x509_cert,
            ]);

            return redirect()->route('saml2.admin.index')
                ->with('success', __('beartropy-saml2::saml2.admin.metadata_refreshed'));
        } catch (\Throwable $e) {
            return back()->with('error', __('beartropy-saml2::saml2.errors.parse_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Parse metadata XML (AJAX endpoint for client-side fetch).
     */
    public function parseMetadata(Request $request)
    {
        $request->validate(['xml' => 'required|string']);

        try {
            $data = $this->metadataParser->parseXml($request->input('xml'));
            $host = parse_url($data['entity_id'] ?? '', PHP_URL_HOST) ?? '';

            return response()->json([
                'success' => true,
                'data' => [
                    'entity_id' => $data['entity_id'] ?? '',
                    'sso_url' => $data['sso_url'] ?? '',
                    'slo_url' => $data['slo_url'] ?? '',
                    'x509_cert' => $data['x509_cert'] ?? '',
                    'idp_key' => Str::slug($host),
                    'idp_name' => Str::title(str_replace(['.', '-'], ' ', $host)),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Validate IDP form data.
     */
    protected function validateIdp(Request $request, ?int $exceptId = null): array
    {
        $uniqueRule = 'unique:beartropy_saml2_idps,key';
        if ($exceptId) {
            $uniqueRule .= ',' . $exceptId;
        }

        return $request->validate([
            'idp_key' => ['required', 'string', 'alpha_dash', $uniqueRule],
            'idp_name' => 'required|string|max:255',
            'entity_id' => 'required|string',
            'sso_url' => 'required|url',
            'slo_url' => 'nullable|url',
            'x509_cert' => 'required|string',
            'metadata_url' => 'nullable|url',
        ]);
    }

    /**
     * Get SP metadata info.
     */
    protected function getSpMetadata(): array
    {
        try {
            return [
                'entityId' => config('beartropy-saml2.sp.entityId') ?: url('/'),
                'acsUrl' => route('saml2.acs.auto'),
                'metadataUrl' => route('saml2.metadata'),
            ];
        } catch (\Throwable $e) {
            return ['entityId' => '', 'acsUrl' => '', 'metadataUrl' => ''];
        }
    }

    /**
     * Clean certificate string.
     */
    protected function cleanCertificate(string $cert): string
    {
        $cert = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'], '', $cert);
        return preg_replace('/\s+/', '', $cert);
    }
}
