<?php

namespace Beartropy\Saml2\Services;

use Beartropy\Saml2\Exceptions\Saml2Exception;
use Illuminate\Support\Facades\Http;

class MetadataParser
{
    /**
     * Parse IDP metadata from XML string.
     */
    public function parseXml(string $xml): array
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        
        if (!$doc->loadXML($xml)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new Saml2Exception('Invalid XML: ' . ($errors[0]->message ?? 'Unknown error'));
        }

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        // Find EntityDescriptor
        $entityDescriptor = $xpath->query('//md:EntityDescriptor')->item(0);
        if (!$entityDescriptor) {
            $entityDescriptor = $xpath->query('//EntityDescriptor')->item(0);
        }

        if (!$entityDescriptor) {
            throw new Saml2Exception('No EntityDescriptor found in metadata');
        }

        $entityId = $entityDescriptor->getAttribute('entityID');
        if (empty($entityId)) {
            throw new Saml2Exception('EntityID not found in metadata');
        }

        // Find IDPSSODescriptor
        $idpDescriptor = $xpath->query('.//md:IDPSSODescriptor', $entityDescriptor)->item(0);
        if (!$idpDescriptor) {
            $idpDescriptor = $xpath->query('.//IDPSSODescriptor', $entityDescriptor)->item(0);
        }

        if (!$idpDescriptor) {
            throw new Saml2Exception('No IDPSSODescriptor found in metadata');
        }

        // Get SSO URL
        $ssoUrl = $this->getSsoUrl($xpath, $idpDescriptor);
        if (!$ssoUrl) {
            throw new Saml2Exception('SingleSignOnService URL not found in metadata');
        }

        // Get SLO URL (optional)
        $sloUrl = $this->getSloUrl($xpath, $idpDescriptor);

        // Get certificates
        $certificates = $this->getCertificates($xpath, $idpDescriptor);
        if (empty($certificates)) {
            throw new Saml2Exception('No x509 certificate found in metadata');
        }

        return [
            'entity_id' => $entityId,
            'sso_url' => $ssoUrl,
            'slo_url' => $sloUrl,
            'x509_cert' => $certificates[0],
            'x509_cert_multi' => count($certificates) > 1 ? [
                'signing' => $certificates,
            ] : null,
        ];
    }

    /**
     * Fetch and parse metadata from a URL.
     */
    public function parseFromUrl(string $url): array
    {
        if (!config('beartropy-saml2.allow_metadata_import', true)) {
            throw new Saml2Exception('Metadata import from URL is disabled');
        }

        $response = Http::timeout(30)
            ->withOptions(['verify' => true])
            ->get($url);

        if (!$response->successful()) {
            throw new Saml2Exception("Failed to fetch metadata from URL: HTTP {$response->status()}");
        }

        $xml = $response->body();
        
        $result = $this->parseXml($xml);
        $result['metadata_url'] = $url;
        
        return $result;
    }

    /**
     * Get the SSO URL from the metadata.
     */
    protected function getSsoUrl(\DOMXPath $xpath, \DOMElement $idpDescriptor): ?string
    {
        // Prefer HTTP-Redirect binding
        $bindings = [
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ];

        foreach ($bindings as $binding) {
            $query = ".//md:SingleSignOnService[@Binding='{$binding}']/@Location";
            $nodes = $xpath->query($query, $idpDescriptor);
            
            if ($nodes->length === 0) {
                // Try without namespace
                $query = ".//SingleSignOnService[@Binding='{$binding}']/@Location";
                $nodes = $xpath->query($query, $idpDescriptor);
            }

            if ($nodes->length > 0) {
                return $nodes->item(0)->nodeValue;
            }
        }

        return null;
    }

    /**
     * Get the SLO URL from the metadata.
     */
    protected function getSloUrl(\DOMXPath $xpath, \DOMElement $idpDescriptor): ?string
    {
        $bindings = [
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ];

        foreach ($bindings as $binding) {
            $query = ".//md:SingleLogoutService[@Binding='{$binding}']/@Location";
            $nodes = $xpath->query($query, $idpDescriptor);
            
            if ($nodes->length === 0) {
                $query = ".//SingleLogoutService[@Binding='{$binding}']/@Location";
                $nodes = $xpath->query($query, $idpDescriptor);
            }

            if ($nodes->length > 0) {
                return $nodes->item(0)->nodeValue;
            }
        }

        return null;
    }

    /**
     * Get all certificates from the metadata.
     */
    protected function getCertificates(\DOMXPath $xpath, \DOMElement $idpDescriptor): array
    {
        $certificates = [];

        // Look for KeyDescriptor elements
        $keyDescriptors = $xpath->query('.//md:KeyDescriptor', $idpDescriptor);
        if ($keyDescriptors->length === 0) {
            $keyDescriptors = $xpath->query('.//KeyDescriptor', $idpDescriptor);
        }

        foreach ($keyDescriptors as $keyDescriptor) {
            $use = $keyDescriptor->getAttribute('use');
            
            // We want signing certs, or certs without a specified use
            if ($use && $use !== 'signing') {
                continue;
            }

            $certNodes = $xpath->query('.//ds:X509Certificate', $keyDescriptor);
            if ($certNodes->length === 0) {
                $certNodes = $xpath->query('.//X509Certificate', $keyDescriptor);
            }

            foreach ($certNodes as $certNode) {
                $cert = trim($certNode->nodeValue);
                $cert = preg_replace('/\s+/', '', $cert);
                
                if (!empty($cert)) {
                    $certificates[] = $cert;
                }
            }
        }

        return array_unique($certificates);
    }
}
