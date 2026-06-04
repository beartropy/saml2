<?php

namespace Beartropy\Saml2\Services;

use Beartropy\Saml2\Events\Saml2LoginEvent;
use Beartropy\Saml2\Events\Saml2LogoutEvent;
use Beartropy\Saml2\Exceptions\InvalidIdpException;
use Beartropy\Saml2\Exceptions\Saml2Exception;
use Beartropy\Saml2\Models\Saml2Idp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Settings;

class Saml2Service
{
    public function __construct(
        protected IdpResolver $idpResolver,
        protected MetadataParser $metadataParser
    ) {}

    /**
     * Write a debug log entry for the SAML flow when debug mode is enabled.
     *
     * Gated by the "beartropy-saml2.debug" config flag. Optionally routed to
     * a dedicated channel via "beartropy-saml2.log_channel".
     */
    protected function debug(string $message, array $context = []): void
    {
        if (!config('beartropy-saml2.debug', false)) {
            return;
        }

        $channel = config('beartropy-saml2.log_channel');
        $logger = $channel ? Log::channel($channel) : Log::getFacadeRoot();

        $logger->debug('[beartropy-saml2] ' . $message, $context);
    }

    /**
     * Build onelogin/php-saml settings array for an IDP.
     */
    public function buildSettings(Saml2Idp|string $idp): array
    {
        if (is_string($idp)) {
            $idp = $this->idpResolver->resolve($idp);
        }

        if (!$idp) {
            throw new InvalidIdpException('IDP not found');
        }

        if (!$idp->isReady()) {
            throw new InvalidIdpException("IDP '{$idp->key}' is not properly configured");
        }

        $config = config('beartropy-saml2');

        return [
            'strict' => $config['strict'] ?? true,
            'debug' => $config['debug'] ?? false,
            'baseurl' => url($config['route_prefix']),
            
            'sp' => [
                'entityId' => $config['sp']['entityId'] ?? url('/'),
                'assertionConsumerService' => [
                    'url' => $config['sp']['acs_url'] ?? route('saml2.acs', ['idp' => $idp->key]),
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ],
                'singleLogoutService' => [
                    'url' => $config['sp']['sls_url'] ?? route('saml2.sls', ['idp' => $idp->key]),
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'NameIDFormat' => $config['sp']['nameIdFormat'] ?? 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                'x509cert' => $config['sp']['x509cert'] ?? '',
                'privateKey' => $config['sp']['privateKey'] ?? '',
            ],
            
            'idp' => $idp->toIdpSettings(),
            
            'security' => $config['security'] ?? [
                'nameIdEncrypted' => false,
                'authnRequestsSigned' => false,
                'logoutRequestSigned' => false,
                'logoutResponseSigned' => false,
                'signMetadata' => false,
                'wantMessagesSigned' => true,
                'wantAssertionsSigned' => true,
                'wantAssertionsEncrypted' => false,
                'wantNameIdEncrypted' => false,
            ],
        ];
    }

    /**
     * Get a configured Auth instance for an IDP.
     */
    public function getAuth(Saml2Idp|string $idp): Auth
    {
        $settings = $this->buildSettings($idp);
        return new Auth($settings);
    }

    /**
     * Initiate SSO login for an IDP.
     */
    public function login(string $idpKey, ?string $returnTo = null): string
    {
        $auth = $this->getAuth($idpKey);

        $returnTo = $returnTo ?? config('beartropy-saml2.login_redirect', '/');

        $url = $auth->login($returnTo, [], false, false, true);

        $this->debug('Initiating SSO login', [
            'idp' => $idpKey,
            'return_to' => $returnTo,
            'redirect_url' => $url,
        ]);

        return $url;
    }

    /**
     * Process the ACS (Assertion Consumer Service) response.
     * 
     * Returns the parsed SAML data and dispatches Saml2LoginEvent.
     */
    public function processAcsResponse(string $idpKey): array
    {
        $idp = $this->idpResolver->resolve($idpKey);
        
        if (!$idp) {
            throw new InvalidIdpException("IDP '{$idpKey}' not found");
        }
        
        return $this->processAcsWithIdp($idp);
    }

    /**
     * Process ACS response by extracting the Issuer (IDP EntityID) from the SAML response.
     * This allows a single ACS URL for all IDPs.
     */
    public function processAcsResponseAuto(): array
    {
        // Extract the Issuer from the SAML response without full processing
        $samlResponse = request()->input('SAMLResponse');
        if (!$samlResponse) {
            throw new Saml2Exception('No SAMLResponse found in request');
        }

        // Decode and parse to get Issuer
        $xml = base64_decode($samlResponse);
        $issuer = $this->extractIssuerFromResponse($xml);
        
        if (!$issuer) {
            throw new Saml2Exception('Could not extract Issuer from SAML response');
        }

        // Find IDP by entity_id
        $idp = $this->idpResolver->resolveByEntityId($issuer);
        
        if (!$idp) {
            throw new InvalidIdpException("No IDP found with entity_id: {$issuer}");
        }

        return $this->processAcsWithIdp($idp);
    }

    /**
     * Process ACS with a resolved IDP.
     */
    protected function processAcsWithIdp(Saml2Idp $idp): array
    {
        $auth = $this->getAuth($idp);

        $this->debug('Processing ACS response', ['idp' => $idp->key]);

        $auth->processResponse();

        $errors = $auth->getErrors();
        if (!empty($errors)) {
            $errorReason = $auth->getLastErrorReason();
            $this->debug('ACS response has errors', [
                'idp' => $idp->key,
                'errors' => $errors,
                'reason' => $errorReason,
            ]);
            throw new Saml2Exception(
                'SAML Response Error: ' . implode(', ', $errors) .
                ($errorReason ? " - $errorReason" : '')
            );
        }

        if (!$auth->isAuthenticated()) {
            $this->debug('ACS response: user not authenticated', ['idp' => $idp->key]);
            throw new Saml2Exception('SAML Response: User is not authenticated');
        }

        $nameId = $auth->getNameId();
        $attributes = $auth->getAttributes();
        $sessionIndex = $auth->getSessionIndex();

        // Use IDP-specific mapping with fallback to global config
        $mappedAttributes = $this->mapAttributes($attributes, $idp);

        $this->debug('ACS response authenticated', [
            'idp' => $idp->key,
            'name_id' => $nameId,
            'session_index' => $sessionIndex,
            'raw_attributes' => $attributes,
            'mapped_attributes' => $mappedAttributes,
        ]);

        // Dispatch event for the user to handle authentication
        event(new Saml2LoginEvent(
            idpKey: $idp->key,
            nameId: $nameId,
            attributes: $mappedAttributes,
            rawAttributes: $attributes,
            sessionIndex: $sessionIndex
        ));

        return [
            'idpKey' => $idp->key,
            'nameId' => $nameId,
            'attributes' => $mappedAttributes,
            'rawAttributes' => $attributes,
            'sessionIndex' => $sessionIndex,
        ];
    }

    /**
     * Extract the Issuer element from a SAML response XML.
     */
    protected function extractIssuerFromResponse(string $xml): ?string
    {
        try {
            $doc = new \DOMDocument();
            $doc->substituteEntities = false;
            $doc->loadXML($xml, LIBXML_NONET | LIBXML_NOENT);
            
            $xpath = new \DOMXPath($doc);
            $xpath->registerNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');
            $xpath->registerNamespace('samlp', 'urn:oasis:names:tc:SAML:2.0:protocol');
            
            // Try to find Issuer in the response
            $issuers = $xpath->query('//saml:Issuer');
            if ($issuers->length > 0) {
                return trim($issuers->item(0)->textContent);
            }
            
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Initiate SLO logout.
     */
    public function logout(string $idpKey, ?string $returnTo = null, ?string $nameId = null, ?string $sessionIndex = null): string
    {
        $auth = $this->getAuth($idpKey);

        $returnTo = $returnTo ?? config('beartropy-saml2.logout_redirect', '/');

        $url = $auth->logout($returnTo, [], $nameId, $sessionIndex, true);

        $this->debug('Initiating SLO logout', [
            'idp' => $idpKey,
            'name_id' => $nameId,
            'session_index' => $sessionIndex,
            'return_to' => $returnTo,
            'redirect_url' => $url,
        ]);

        return $url;
    }

    /**
     * Process the SLS (Single Logout Service) response/request.
     *
     * @param string $idpKey
     * @param callable|null $cbDeleteSession Callback to handle session deletion
     */
    public function processSlo(string $idpKey, ?callable $cbDeleteSession = null, ?string $nameId = null, ?string $sessionIndex = null): ?string
    {
        $auth = $this->getAuth($idpKey);

        $this->debug('Processing SLO', [
            'idp' => $idpKey,
            'name_id' => $nameId,
            'session_index' => $sessionIndex,
        ]);

        $redirectUrl = $auth->processSLO(
            keepLocalSession: false,
            requestId: null,
            retrieveParametersFromServer: false,
            cbDeleteSession: $cbDeleteSession
        );

        $errors = $auth->getErrors();
        if (!empty($errors)) {
            $this->debug('SLO has errors', ['idp' => $idpKey, 'errors' => $errors]);
            throw new Saml2Exception('SAML SLO Error: ' . implode(', ', $errors));
        }

        // Dispatch logout event with user context
        event(new Saml2LogoutEvent($idpKey, $nameId, $sessionIndex));

        return $redirectUrl;
    }

    /**
     * Generate SP metadata XML, with optional caching.
     */
    public function getMetadataXml(): string
    {
        $ttl = (int) config('beartropy-saml2.cache_metadata_ttl', 3600);

        if ($ttl > 0) {
            return Cache::remember('saml2_sp_metadata', $ttl, fn () => $this->generateMetadataXml());
        }

        return $this->generateMetadataXml();
    }

    /**
     * Generate SP metadata XML.
     */
    protected function generateMetadataXml(): string
    {
        $config = config('beartropy-saml2');

        // Try to use a real IDP for metadata generation; fall back to placeholder
        $firstIdp = $this->idpResolver->all()->first();
        $idpConfig = $firstIdp
            ? $firstIdp->toIdpSettings()
            : [
                'entityId' => 'https://placeholder.example.com',
                'singleSignOnService' => ['url' => 'https://placeholder.example.com/sso'],
                'x509cert' => 'placeholder',
            ];

        // Non-strict mode for metadata generation (IDP config may be a placeholder)
        $settings = [
            'strict' => false,
            'debug' => $config['debug'] ?? false,
            'sp' => [
                'entityId' => $config['sp']['entityId'] ?? url('/'),
                'assertionConsumerService' => [
                    'url' => $config['sp']['acs_url'] ?? route('saml2.acs.auto'),
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ],
                'singleLogoutService' => [
                    'url' => $config['sp']['sls_url'] ?? route('saml2.sls', ['idp' => 'default']),
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'NameIDFormat' => $config['sp']['nameIdFormat'] ?? 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                'x509cert' => $config['sp']['x509cert'] ?? '',
                'privateKey' => $config['sp']['privateKey'] ?? '',
            ],
            'idp' => $idpConfig,
        ];

        $samlSettings = new Settings($settings);
        $metadata = $samlSettings->getSPMetadata();

        $errors = $samlSettings->validateMetadata($metadata);
        if (!empty($errors)) {
            throw new Saml2Exception('Invalid SP Metadata: ' . implode(', ', $errors));
        }

        return $metadata;
    }

    /**
     * Map SAML attributes using the IDP-specific or global mapping.
     */
    protected function mapAttributes(array $attributes, ?Saml2Idp $idp = null): array
    {
        // Get mapping: IDP-specific first, then fallback to global config
        $mapping = $idp?->getAttributeMapping() 
            ?? config('beartropy-saml2.attribute_mapping', []);
        
        $mapped = [];

        foreach ($mapping as $localKey => $samlKey) {
            if (isset($attributes[$samlKey])) {
                $value = $attributes[$samlKey];
                // SAML attributes are often arrays, get first value
                $mapped[$localKey] = is_array($value) ? ($value[0] ?? null) : $value;
            }
        }

        return $mapped;
    }

    /**
     * Get the IDP resolver instance.
     */
    public function getIdpResolver(): IdpResolver
    {
        return $this->idpResolver;
    }

    /**
     * Get the metadata parser instance.
     */
    public function getMetadataParser(): MetadataParser
    {
        return $this->metadataParser;
    }
}
