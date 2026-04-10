# IdpResolver — AI Reference

## Architecture
- Namespace: `Beartropy\Saml2\Services`
- Resolution chain based on `config('beartropy-saml2.idp_source')`: `env`, `database`, or `both`
- Database lookups cached for `cache_idp_ttl` seconds (default: 300)

## Resolution Strategy

### `env` source
- Only checks `config('beartropy-saml2.default_idp')` — single IDP from env vars
- Creates `Saml2Idp` model instance (not persisted)

### `database` source
- Queries `beartropy_saml2_idps` table by `key` column
- Result cached with key `saml2.idp.{idpKey}`

### `both` source
- Tries env first, falls back to database

## Methods

| Method | Logic |
|--------|-------|
| `resolve(string $idpKey)` | Routes to env/database/both based on config |
| `resolveByEntityId(string $entityId)` | Database query by `entity_id` column — used by auto ACS |
| `all()` | Returns all active IDPs from configured sources |
| `clearCache(?string $idpKey)` | Clears specific or all IDP cache entries |

## Common Pitfalls
- `resolveFromEnv()` only works for the configured `default_idp.key` — returns null for any other key
- `resolveByEntityId()` always queries the database regardless of `idp_source` setting
- Cache is tagged — requires a cache driver that supports tags (not `file`)
