# Artisan Commands — AI Reference

## Commands

### `saml2:create-idp`
Creates a new IDP configuration. Interactive prompts for key, name, entity ID, SSO URL, certificate. Supports `--url` flag to auto-import from metadata URL.

### `saml2:list-idps`
Lists all configured IDPs with key, name, active status, and entity ID.

### `saml2:test-idp`
Tests connectivity to an IDP by attempting to build onelogin settings and validate the configuration.

### `saml2:delete-idp`
Deletes an IDP by key. Prompts for confirmation.

### `saml2:generate-cert`
Generates a self-signed X.509 certificate pair for the SP. Outputs `SAML2_SP_CERT` and `SAML2_SP_PRIVATE_KEY` environment variable values.

### `saml2:refresh-metadata`
Refreshes metadata for all IDPs that have a `metadata_url` configured. Updates `entity_id`, `sso_url`, `slo_url`, and certificates from the remote metadata.

### `saml2:publish-listener`
Scaffolds a `HandleSaml2Login` listener class in `app/Listeners/` with boilerplate for handling `Saml2LoginEvent`.

### `saml2:reset-setup`
Resets the setup wizard state via `Saml2Setting::resetToFirstDeploy()`. Used when you need to re-run the first-deploy wizard.

## Common Pitfalls
- `saml2:create-idp --url` requires `allow_metadata_import = true` in config
- `saml2:generate-cert` requires OpenSSL PHP extension
- `saml2:refresh-metadata` respects SSRF protection settings
