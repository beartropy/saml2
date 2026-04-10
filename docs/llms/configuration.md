# Configuration — AI Reference

## Config File
`config/beartropy-saml2.php`

## SP Settings
| Key | Env Var | Default |
|-----|---------|---------|
| `sp.entityId` | `SAML2_SP_ENTITY_ID` | — |
| `sp.x509cert` | `SAML2_SP_CERT` | — |
| `sp.privateKey` | `SAML2_SP_PRIVATE_KEY` | — |
| `sp.acs_url` | `SAML2_SP_ACS_URL` | `null` (auto) |
| `sp.sls_url` | `SAML2_SP_SLS_URL` | `null` (auto) |
| `sp.metadata_url` | `SAML2_SP_METADATA_URL` | `null` (auto) |
| `sp.nameIdFormat` | `SAML2_SP_NAMEID_FORMAT` | `emailAddress` |

## IDP Source
| Key | Env Var | Default |
|-----|---------|---------|
| `idp_source` | `SAML2_IDP_SOURCE` | `'database'` |

## Default IDP (env source)
| Key | Env Var |
|-----|---------|
| `default_idp.key` | `SAML2_IDP_KEY` |
| `default_idp.name` | `SAML2_IDP_NAME` |
| `default_idp.entityId` | `SAML2_IDP_ENTITY_ID` |
| `default_idp.ssoUrl` | `SAML2_IDP_SSO_URL` |
| `default_idp.sloUrl` | `SAML2_IDP_SLO_URL` |
| `default_idp.x509cert` | `SAML2_IDP_CERT` |

## Routes & Middleware
| Key | Default |
|-----|---------|
| `route_prefix` | `'saml2'` |
| `route_middleware` | `['web', 'throttle:60,1']` |
| `setup_middleware` | `['web', 'auth', 'throttle:10,1']` |
| `admin_enabled` | `true` |
| `admin_route_prefix` | `'saml2/admin'` |
| `admin_middleware` | `['web', 'auth']` |

## Redirects
| Key | Env Var | Default |
|-----|---------|---------|
| `login_redirect` | `SAML2_LOGIN_REDIRECT` | `'/'` |
| `logout_redirect` | `SAML2_LOGOUT_REDIRECT` | `'/'` |
| `error_redirect` | `SAML2_ERROR_REDIRECT` | `'/login'` |

## Security
| Key | Env Var | Default |
|-----|---------|---------|
| `security.wantMessagesSigned` | `SAML2_WANT_MESSAGES_SIGNED` | `true` |
| `security.wantAssertionsSigned` | `SAML2_WANT_ASSERTIONS_SIGNED` | `true` |
| `security.nameIdEncrypted` | — | `false` |
| `security.authnRequestsSigned` | — | `false` |
| `security.signatureAlgorithm` | — | `rsa-sha256` |
| `security.digestAlgorithm` | — | `sha256` |

## Caching
| Key | Env Var | Default |
|-----|---------|---------|
| `cache_idp_ttl` | `SAML2_CACHE_IDP_TTL` | `300` |
| `cache_metadata_ttl` | `SAML2_CACHE_METADATA_TTL` | `3600` |

## Other
| Key | Env Var | Default |
|-----|---------|---------|
| `allow_metadata_import` | `SAML2_ALLOW_METADATA_IMPORT` | `true` |
| `block_private_metadata_urls` | `SAML2_BLOCK_PRIVATE_URLS` | `true` |
| `session_prefix` | `SAML2_SESSION_PREFIX` | `'saml2'` |
| `debug` | `SAML2_DEBUG` | `false` |
| `strict` | `SAML2_STRICT` | `true` |
| `layout` | `SAML2_ADMIN_LAYOUT` | `null` |

## Common Pitfalls
- `wantMessagesSigned` and `wantAssertionsSigned` are `true` by default — your IDP must sign assertions
- `idp_source = 'env'` only supports a single IDP — use `'database'` for multi-IDP
- `block_private_metadata_urls` prevents SSRF but blocks local development IDPs — disable in dev
- Cache requires a tag-supporting driver (Redis, Memcached) — `file` driver will not work
