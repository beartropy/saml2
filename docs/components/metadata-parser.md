# Metadata Parser

Parses IDP metadata from XML strings or URLs.

## Usage

```php
$parser = Saml2::getMetadataParser();

// From XML string
$data = $parser->parseXml($xmlString);

// From URL
$data = $parser->parseFromUrl('https://idp.example.com/metadata');
```

## Methods

| Method | Return | Description |
|--------|--------|-------------|
| `parseXml(string $xml)` | `array` | Parse IDP metadata from XML string |
| `parseFromUrl(string $url)` | `array` | Fetch and parse metadata from URL |

## Return Format

```php
[
    'entity_id' => 'https://idp.example.com',
    'sso_url' => 'https://idp.example.com/sso',
    'slo_url' => 'https://idp.example.com/slo',
    'x509_cert' => '-----BEGIN CERTIFICATE-----...',
    'x509_cert_multi' => [...],
]
```

## Security

- SSRF protection on URL fetching when `block_private_metadata_urls` is enabled
- Namespace-aware XML parsing with fallback to non-namespaced elements
