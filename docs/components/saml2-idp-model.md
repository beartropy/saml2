# Saml2Idp Model

The Eloquent model representing a configured Identity Provider.

## Table

`beartropy_saml2_idps`

## Properties

| Column | Type | Description |
|--------|------|-------------|
| `key` | `string` | Unique IDP identifier (slug) |
| `name` | `string` | Human-readable name |
| `entity_id` | `text` | SAML IDP Entity ID |
| `sso_url` | `text` | Single Sign-On URL |
| `slo_url` | `text\|null` | Single Logout URL |
| `x509_cert` | `text` | IDP signing certificate |
| `x509_cert_multi` | `JSON\|null` | Multiple certificates |
| `metadata_url` | `text\|null` | URL to fetch metadata |
| `metadata` | `JSON\|null` | Additional metadata |
| `attribute_mapping` | `JSON\|null` | Custom SAML attribute mapping |
| `is_active` | `bool` | IDP active status (indexed) |

## Methods

| Method | Return | Description |
|--------|--------|-------------|
| `scopeActive($query)` | `Builder` | Query scope for active IDPs |
| `getLoginUrl()` | `string` | Get login route URL |
| `toIdpSettings()` | `array` | Convert to onelogin settings format |
| `isReady()` | `bool` | Check if properly configured |
| `getAttributeMapping()` | `array` | Get effective attribute mapping |
| `hasCustomAttributeMapping()` | `bool` | Check if custom mapping exists |
| `getMetadataValue(string $key, $default)` | `mixed` | Get metadata value |
| `setMetadataValue(string $key, $value)` | `self` | Set metadata value |
