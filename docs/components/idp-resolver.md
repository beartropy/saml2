# IDP Resolver

Resolves Identity Providers from environment variables, database, or both.

## Usage

```php
$resolver = Saml2::getIdpResolver();

$idp = $resolver->resolve('azure');
$allIdps = $resolver->all();
$exists = $resolver->exists('azure');
```

## Methods

| Method | Return | Description |
|--------|--------|-------------|
| `resolve(string $idpKey)` | `?Saml2Idp` | Resolve IDP by key |
| `resolveFromEnv(string $idpKey)` | `?Saml2Idp` | Resolve from environment config |
| `resolveFromDatabase(string $idpKey)` | `?Saml2Idp` | Resolve from database (cached) |
| `all()` | `Collection` | Get all active IDPs |
| `exists(string $idpKey)` | `bool` | Check if IDP exists |
| `resolveByEntityId(string $entityId)` | `?Saml2Idp` | Resolve by SAML entity ID |
| `clearCache(?string $idpKey = null)` | `void` | Clear cached IDP data |

## IDP Sources

Configured via `config('beartropy-saml2.idp_source')`:

| Source | Description |
|--------|-------------|
| `env` | Resolve from `config('beartropy-saml2.default_idp')` only |
| `database` | Resolve from `beartropy_saml2_idps` table |
| `both` | Try env first, fall back to database |

## Caching

Database lookups are cached for `cache_idp_ttl` seconds (default: 300). Use `clearCache()` to invalidate.
