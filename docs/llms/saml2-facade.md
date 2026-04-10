# Saml2 Facade — AI Reference

## Facade Access
```php
use Beartropy\Saml2\Facades\Saml2;
Saml2::login('azure');
```

## Architecture
- Facade accessor: `'saml2'`
- Resolves to: `Saml2Service`
- Uses `onelogin/php-saml` (`Auth` class) for SAML protocol handling
- Dependencies: `IdpResolver`, `MetadataParser`

## Saml2Service Methods

| Method | Signature |
|--------|-----------|
| `buildSettings` | `(Saml2Idp\|string $idp): array` |
| `getAuth` | `(Saml2Idp\|string $idp): Auth` |
| `login` | `(string $idpKey, ?string $returnTo = null): string` |
| `processAcsResponse` | `(string $idpKey): array` |
| `processAcsResponseAuto` | `(): array` |
| `logout` | `(string $idpKey, ?string $returnTo, ?string $nameId, ?string $sessionIndex): string` |
| `processSlo` | `(string $idpKey, ?callable $cbDeleteSession, ?string $nameId, ?string $sessionIndex): ?string` |
| `getMetadataXml` | `(): string` |
| `getIdpResolver` | `(): IdpResolver` |
| `getMetadataParser` | `(): MetadataParser` |

## Authentication Flow
1. `login()` → builds onelogin settings → `Auth::login()` → returns redirect URL to IDP
2. IDP POSTs to ACS endpoint → `processAcsResponse()` or `processAcsResponseAuto()`
3. Validates SAML assertion → dispatches `Saml2LoginEvent`
4. Session regenerated → user redirected to `login_redirect`

## Auto ACS Detection
`processAcsResponseAuto()` extracts the Issuer from the SAML response XML, resolves the IDP by entity ID via `IdpResolver::resolveByEntityId()`, then processes the response.

## Common Pitfalls
- `login()` returns a URL string — you must `redirect()` to it
- ACS routes must be excluded from CSRF protection
- `processAcsResponseAuto()` requires the IDP entity ID to be stored in the database
- SP metadata is cached for `cache_metadata_ttl` seconds
