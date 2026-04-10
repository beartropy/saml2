# MetadataParser — AI Reference

## Architecture
- Namespace: `Beartropy\Saml2\Services`
- DOM-based XML parsing with namespace-aware XPath queries
- SSRF protection on URL fetching via `block_private_metadata_urls` config

## Methods

### `parseXml(string $xml): array`
- Loads XML into DOMDocument
- Extracts: `entity_id`, `sso_url`, `slo_url`, `x509_cert`, `x509_cert_multi`
- Handles both namespaced (`md:EntityDescriptor`) and non-namespaced elements
- Certificate cleaning via `CertificateHelper::clean()`

### `parseFromUrl(string $url): array`
- Validates URL against SSRF blocklist (private IPs) if enabled
- Fetches metadata via HTTP GET
- Delegates to `parseXml()`

## Return Format
```php
[
    'entity_id' => 'https://idp.example.com',
    'sso_url' => 'https://idp.example.com/sso',
    'slo_url' => 'https://idp.example.com/slo',  // nullable
    'x509_cert' => 'MII...',                       // cleaned, no headers
    'x509_cert_multi' => ['MII...', 'MII...'],    // nullable
]
```

## Common Pitfalls
- SSRF protection blocks private/reserved IP ranges by default — disable for local development
- SLO URL is optional in SAML metadata — may return null
- Multiple certificates are extracted when the IDP has key rotation configured
