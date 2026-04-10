# Configuration

Full reference for `config/beartropy-saml2.php`.

## Service Provider (SP)

| Key | Default | Description |
|-----|---------|-------------|
| `sp.entityId` | `env('SAML2_SP_ENTITY_ID')` | Your app URL / entity ID |
| `sp.x509cert` | `env('SAML2_SP_CERT')` | SP public certificate |
| `sp.privateKey` | `env('SAML2_SP_PRIVATE_KEY')` | SP private key |
| `sp.acs_url` | `null` | Custom ACS URL (auto-generated if null) |
| `sp.sls_url` | `null` | Custom SLS URL (auto-generated if null) |
| `sp.nameIdFormat` | `emailAddress` | SAML NameID format |

## IDP Source

| Key | Default | Description |
|-----|---------|-------------|
| `idp_source` | `database` | IDP source: `env`, `database`, or `both` |

## Routes

| Key | Default | Description |
|-----|---------|-------------|
| `route_prefix` | `saml2` | Route prefix |
| `route_middleware` | `['web', 'throttle:60,1']` | SAML route middleware |
| `setup_middleware` | `['web', 'auth', 'throttle:10,1']` | Setup wizard middleware |

## Admin Panel

| Key | Default | Description |
|-----|---------|-------------|
| `admin_enabled` | `true` | Enable admin panel |
| `admin_route_prefix` | `saml2/admin` | Admin route prefix |
| `admin_middleware` | `['web', 'auth']` | Admin middleware |
| `layout` | `null` | Custom Blade layout |

## Redirects

| Key | Default | Description |
|-----|---------|-------------|
| `login_redirect` | `/` | Redirect after login |
| `logout_redirect` | `/` | Redirect after logout |
| `error_redirect` | `/login` | Redirect on error |

## Security

| Key | Default | Description |
|-----|---------|-------------|
| `security.wantMessagesSigned` | `true` | Require signed messages |
| `security.wantAssertionsSigned` | `true` | Require signed assertions |
| `security.signatureAlgorithm` | `rsa-sha256` | Signature algorithm |

## Caching

| Key | Default | Description |
|-----|---------|-------------|
| `cache_idp_ttl` | `300` | IDP cache TTL (seconds) |
| `cache_metadata_ttl` | `3600` | Metadata cache TTL (seconds) |

## Attribute Mapping

```php
'attribute_mapping' => [
    'email' => 'email',
    'name' => 'displayname',
    'firstname' => 'firstname',
    'lastname' => 'lastname',
    'username' => 'username',
    'roles' => 'roles',
    'groups' => 'groups',
]
```
