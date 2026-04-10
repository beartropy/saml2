# Saml2Idp Model — AI Reference

## Model
```php
Beartropy\Saml2\Models\Saml2Idp
```

## Table
`beartropy_saml2_idps`

## Fillable
`key`, `name`, `entity_id`, `sso_url`, `slo_url`, `x509_cert`, `x509_cert_multi`, `metadata_url`, `metadata`, `attribute_mapping`, `is_active`

## Casts
- `x509_cert_multi` → `array`
- `metadata` → `array`
- `attribute_mapping` → `array`
- `is_active` → `boolean`

## Key Methods

### `toIdpSettings(): array`
Converts to onelogin IDP settings format:
```php
['entityId' => ..., 'singleSignOnService' => ['url' => ...], 'x509cert' => ...]
```

### `getAttributeMapping(): array`
Returns per-IDP mapping if set, otherwise falls back to `config('beartropy-saml2.attribute_mapping')`.

### `isReady(): bool`
Returns true when `entity_id`, `sso_url`, and `x509_cert` are all set.

### `getLoginUrl(): string`
Returns `route('saml2.login', ['idp' => $this->key])`.

## Scopes
- `scopeActive($query)` — filters by `is_active = true`

## Common Pitfalls
- `key` must be URL-safe (used in route parameters)
- `x509_cert` should be stored without PEM headers — use `CertificateHelper::clean()`
- `attribute_mapping` at the model level overrides the global config entirely (not merged)
