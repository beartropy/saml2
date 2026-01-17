<?php

namespace Beartropy\Saml2\Http\Controllers;

use Beartropy\Saml2\Models\Saml2Idp;
use Beartropy\Saml2\Models\Saml2Setting;
use Beartropy\Saml2\Services\MetadataParser;
use Beartropy\Saml2\Services\Saml2Service;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class SetupController extends Controller
{
    public function __construct(
        protected Saml2Service $saml2Service,
        protected MetadataParser $metadataParser
    ) {}

    /**
     * Show the setup wizard page.
     */
    public function index()
    {
        // If setup is complete, redirect
        if (Saml2Setting::isSetupComplete()) {
            return redirect(config('beartropy-saml2.login_redirect', '/'));
        }

        // Generate SP metadata
        $spMetadata = $this->getSpMetadata();

        return view('beartropy-saml2::setup', [
            'spMetadataXml' => $spMetadata['xml'],
            'spMetadataUrl' => $spMetadata['url'],
            'spEntityId' => $spMetadata['entityId'],
            'spAcsUrl' => $spMetadata['acsUrl'],
            'inputMethod' => session('saml2_input_method', 'url'),
            'formData' => session('saml2_form_data', []),
            'error' => session('saml2_error'),
            'success' => session('saml2_success'),
        ]);
    }

    /**
     * Show the success page after setup.
     */
    public function success(int $idp)
    {
        $idp = Saml2Idp::findOrFail($idp);
        $spMetadata = $this->getSpMetadata();

        return view('beartropy-saml2::setup-success', [
            'idp' => $idp,
            'spMetadata' => $spMetadata,
        ]);
    }

    /**
     * Parse metadata from XML text.
     */
    public function parseText(Request $request)
    {
        $request->validate([
            'metadata_text' => 'required|string',
        ]);

        try {
            $data = $this->metadataParser->parseXml($request->input('metadata_text'));
            
            return redirect()->route('saml2.setup')
                ->with('saml2_input_method', 'form')
                ->with('saml2_form_data', $this->prepareFormData($data))
                ->with('saml2_success', __('beartropy-saml2::saml2.setup.metadata_parsed'));
        } catch (\Throwable $e) {
            return redirect()->route('saml2.setup')
                ->with('saml2_input_method', 'text')
                ->with('saml2_error', __('beartropy-saml2::saml2.errors.parse_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Parse metadata XML sent from client-side fetch.
     */
    public function parseXml(Request $request)
    {
        $request->validate([
            'xml' => 'required|string',
            'source_url' => 'nullable|url',
        ]);

        try {
            $data = $this->metadataParser->parseXml($request->input('xml'));
            
            $formData = $this->prepareFormData($data);
            if ($request->input('source_url')) {
                $formData['metadata_url'] = $request->input('source_url');
            }
            
            return response()->json([
                'success' => true,
                'data' => $formData,
                'message' => __('beartropy-saml2::saml2.setup.metadata_parsed'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => __('beartropy-saml2::saml2.errors.parse_failed') . ': ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Fetch metadata from URL server-side (proxy for CORS issues).
     */
    public function fetchFromUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        try {
            $data = $this->metadataParser->parseFromUrl($request->input('url'));
            
            $formData = $this->prepareFormData($data);
            $formData['metadata_url'] = $request->input('url');
            
            return response()->json([
                'success' => true,
                'data' => $formData,
                'message' => __('beartropy-saml2::saml2.setup.metadata_parsed'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => __('beartropy-saml2::saml2.errors.fetch_failed') . ': ' . $e->getMessage(),
            ], 422);
        }
    }


    /**
     * Save the IDP configuration.
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'idp_key' => 'required|string|alpha_dash|unique:beartropy_saml2_idps,key',
            'idp_name' => 'required|string|max:255',
            'entity_id' => 'required|string',
            'sso_url' => 'required|url',
            'slo_url' => 'nullable|url',
            'x509_cert' => 'required|string',
            'metadata_url' => 'nullable|url',
        ]);

        try {
            $idp = Saml2Idp::create([
                'key' => $validated['idp_key'],
                'name' => $validated['idp_name'],
                'entity_id' => $validated['entity_id'],
                'sso_url' => $validated['sso_url'],
                'slo_url' => $validated['slo_url'] ?? null,
                'x509_cert' => $this->cleanCertificate($validated['x509_cert']),
                'metadata_url' => $validated['metadata_url'] ?? null,
                'is_active' => true,
            ]);

            Saml2Setting::markSetupComplete();

            return redirect()->route('saml2.setup.success', ['idp' => $idp->id]);
        } catch (\Throwable $e) {
            return redirect()->route('saml2.setup')
                ->with('saml2_input_method', 'form')
                ->with('saml2_form_data', $request->except('_token'))
                ->with('saml2_error', __('beartropy-saml2::saml2.errors.save_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Get SP metadata information.
     */
    protected function getSpMetadata(): array
    {
        try {
            return [
                'xml' => $this->saml2Service->getMetadataXml(),
                'url' => route('saml2.metadata'),
                'metadataUrl' => route('saml2.metadata'),
                'entityId' => config('beartropy-saml2.sp.entityId') ?: url('/'),
                'acsUrl' => route('saml2.acs.auto'),
            ];
        } catch (\Throwable $e) {
            return [
                'xml' => '',
                'url' => route('saml2.metadata'),
                'metadataUrl' => route('saml2.metadata'),
                'entityId' => config('beartropy-saml2.sp.entityId') ?: url('/'),
                'acsUrl' => route('saml2.acs.auto'),
            ];
        }
    }

    /**
     * Prepare form data from parsed metadata.
     */
    protected function prepareFormData(array $data): array
    {
        $entityId = $data['entity_id'] ?? '';
        $host = parse_url($entityId, PHP_URL_HOST) ?? $entityId;

        return [
            'entity_id' => $entityId,
            'sso_url' => $data['sso_url'] ?? '',
            'slo_url' => $data['slo_url'] ?? '',
            'x509_cert' => $data['x509_cert'] ?? '',
            'idp_key' => Str::slug($host),
            'idp_name' => Str::title(str_replace(['.', '-'], ' ', $host)),
            'metadata_url' => $data['metadata_url'] ?? '',
        ];
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
