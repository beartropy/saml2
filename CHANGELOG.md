# Changelog

All notable changes to this project will be documented in this file.

## [v0.3.7] - 2026-06-25

### Fixed
- **Laravel 13 CSRF exclusion on ACS/SLS** — the SAML endpoints now also exclude `PreventRequestForgery`, Laravel 13's renamed CSRF middleware. `withoutMiddleware` matches by exact class (not ancestry), and L13 registers the parent `PreventRequestForgery` in the `web` group (the L11/L12 `ValidateCsrfToken` and legacy `VerifyCsrfToken` now extend it). As a result the cross-site, token-less SAML POSTs from the IdP were rejected with a CSRF error under Laravel 13. The route test was hardened to assert the *resolved* middleware stack contains no CSRF guard, instead of only checking the declared exclusion list (which missed this regression).

## [v0.3.6] - 2026-06-25

### Changed
- **Dev**: Test against Laravel 13 via Orchestra Testbench 11 (`orchestra/testbench: ^9.0|^10.0|^11.0`). No runtime changes.

## [v0.3.5] - 2026-06-04

### Added
- **Debug logging** — when `SAML2_DEBUG` (config `debug`) is enabled, the package now writes detailed debug logs of the SAML flow (login, ACS, received/mapped attributes, logout) through Laravel's logger. Previously the `debug` flag only fed the underlying `onelogin/php-saml` library and produced no application logs.
- **`SAML2_LOG_CHANNEL` config** (`log_channel`) — optional dedicated log channel for the debug output; defaults to the application's default logging channel.

## [v0.3.4] - 2026-04-10

### Added
- **Docs**: AI assistant documentation (`docs/components/`, `docs/llms/`, `docs/ai-assistants/`) with API reference, usage examples, AI reference, cursor rules, and code examples for facade, IDP resolver, metadata parser, events, configuration, and artisan commands.
- **Skills**: Claude Code skills (`bt-saml2-setup`, `bt-saml2-component`) and `skills.json` manifest for the `beartropy:skills` installer command.

## [v0.3.2] - 2026-04-07

### Changed
- Added Laravel 13 support (`illuminate/support: ^13.0`, `illuminate/database: ^13.0`, `illuminate/routing: ^13.0`)

## [v0.3.1] - 2026-03-05

### Added
- **Test suite** — 62 tests (138 assertions) covering units, features, admin CRUD, routes, login flow, metadata, session keys, model behavior, events, and security helpers
- **IDP resolution caching** — database lookups cached with configurable TTL (`cache_idp_ttl`, default 5 min)
- **SP metadata caching** — XML metadata cached with configurable TTL (`cache_metadata_ttl`, default 1 hour)
- **Configurable session prefix** — `session_prefix` config option prevents key collisions in multi-package setups
- **`IdpRequest` Form Request** — validation logic extracted from `AdminController` into a dedicated request class
- **Enriched `Saml2LogoutEvent`** — now includes `nameId` and `sessionIndex` alongside `idpKey`

### Changed
- Admin routes use **route model binding** (`{idp}` instead of `{id}` with manual `findOrFail`)
- `AdminController` uses `IdpRequest` Form Request instead of inline validation
- `AdminController` clears IDP cache on update, delete, toggle, and refresh operations
- `Saml2Controller` uses configurable session prefix for all session keys
- CSRF exclusion now lists both `VerifyCsrfToken` and `ValidateCsrfToken` for **Laravel 11/12 compatibility**
- `Saml2Idp` model enforces **key immutability** at the model level via `updating` event

### Removed
- Dead `user_model` config option (was never referenced in source code)

### Fixed
- README corrected: event listener is not auto-discovered, must be registered manually
- README updated with auth requirement for setup wizard and v0.3.0 security defaults

## [v0.3.0] - 2026-03-05

### ⚠ BREAKING — Security Hardening Release

This release fixes critical security vulnerabilities. **Upgrading requires action.**

### Upgrade Steps

1. **Run migrations** — A new migration widens `entity_id`, `sso_url`, `slo_url`, and `metadata_url` columns from `VARCHAR(255)` to `TEXT`:
   ```bash
   php artisan migrate
   ```

2. **Re-publish your config** (or merge manually):
   ```bash
   php artisan vendor:publish --tag=beartropy-saml2-config --force
   ```

3. **Signed assertions are now required by default.**
   `wantMessagesSigned` and `wantAssertionsSigned` now default to `true`. If your IDP does not send signed SAML assertions, authentication will fail.
   - **Recommended:** Configure your IDP to sign assertions (most IDPs support this).
   - **Temporary workaround:** Set in your `.env`:
     ```
     SAML2_WANT_MESSAGES_SIGNED=false
     SAML2_WANT_ASSERTIONS_SIGNED=false
     ```

4. **Setup wizard now requires authentication.**
   The setup routes now use `['web', 'auth', 'throttle:10,1']` middleware by default. Users must be logged in to access `/saml2/setup`. If you need unauthenticated setup access, customize `setup_middleware` in your config.

5. **SAML routes now include rate limiting.**
   `route_middleware` defaults to `['web', 'throttle:60,1']`. If your application does not define the `throttle` middleware alias, either register it or override `route_middleware` in your config.

6. **Metadata URL fetching now blocks private/reserved IPs.**
   Server-side metadata fetching rejects URLs pointing to private networks (10.x, 172.16.x, 192.168.x, 169.254.x, localhost). If your IDP metadata is on an internal network, set in your `.env`:
   ```
   SAML2_BLOCK_PRIVATE_URLS=false
   ```

7. **`processSlo()` parameter renamed.**
   If you call `processSlo()` using the named argument `keepLocalSession:`, rename it to `cbDeleteSession:`. Positional calls are unaffected.

### Fixed (Security)
- **XXE injection** in XML metadata parsing and SAML response issuer extraction — external entities are now disabled
- **Open redirect** via `returnTo` query parameter on login/logout routes — only relative paths are now accepted
- **SSRF** via server-side metadata URL fetching — private/reserved IP ranges are now blocked by default
- **Unauthenticated setup wizard** — setup routes now require `auth` middleware and rate limiting
- **Information disclosure** — error messages no longer expose internal exception details to users
- **Session fixation** — session ID is regenerated after successful SAML authentication
- **Unsigned assertions accepted** — `wantMessagesSigned` and `wantAssertionsSigned` now default to `true`

### Fixed (Bugs)
- `processSlo()` parameter name now matches its actual purpose (`cbDeleteSession` instead of `keepLocalSession`)
- `PublishListenerCommand` no longer claims the listener is auto-discovered (manual registration required)
- `updateIdp()` now enforces IDP key immutability server-side (was only enforced client-side via `readonly` attribute)
- Removed unused `$index` variable in attribute mapping Blade template
- Database columns `entity_id`, `sso_url`, `slo_url`, `metadata_url` widened from `VARCHAR(255)` to `TEXT`

### Added
- `CertificateHelper::clean()` shared utility (replaces duplicated `cleanCertificate` in 3 files)
- `UrlValidator::sanitizeRedirect()` for safe redirect URL handling
- `block_private_metadata_urls` config option for SSRF protection toggle
- `setup_middleware` config option for independent setup route middleware
- `$hidden` on `Saml2Idp` model to prevent `x509_cert` from leaking in JSON serialization
- Rate limiting on SAML routes (`throttle:60,1`) and setup routes (`throttle:10,1`)
- `SAML2_WANT_MESSAGES_SIGNED` and `SAML2_WANT_ASSERTIONS_SIGNED` env variable support

### Changed
- Service registration changed from `singleton()` to `bind()` (services are stateless)
- Metadata generation now uses first real IDP configuration instead of a hardcoded dummy certificate
- Inline security fallback defaults now match config defaults

## [v0.2.8] - 2026-01-18

### Added
- Dark mode support for admin views (supports `prefers-color-scheme` and `.dark` class)

### Changed  
- Removed `max-width` constraint when using a custom layout (full width)

## [v0.2.7] - 2026-01-18

### Changed
- Refactored admin views to use slot-based component layout system
- Admin views now work with custom component layouts that use `{{ $slot }}`
- Updated `layout` config to accept component names (e.g., `layouts.app`) instead of view paths
- Scoped admin styles with `.saml2-admin-wrapper` prefix to avoid conflicts with custom layouts


## [v0.2.6] - 2026-01-18

### Changed
- Added `getAttributes()` and `getRawAttributes()` to `Saml2LoginEvent`
- Improved `env` source support by making migrations optional
- Conditional setup routes based on `idp_source` configuration

## [v0.2.5] - 2026-01-17

### Added
- English translation for all documentation files.
- Language selector in `docs/README.md`.

### Changed
- Reorganized documentation folder structure:
    - `docs/en/`: English documentation.
    - `docs/es/`: Spanish documentation.
- Updated root `README.md` with links to multi-language documentation.

## [v0.2.4] - 2026-01-17

### Added
- Comprehensive documentation in `docs/` directory:
    - `CONFIG.md`: Configuration reference.
    - `INSTALL.md`: Installation and migration guide.
    - `SETUP.md`: First-deploy setup wizard and listener guide.
    - `UI.md`: Admin panel management guide.
    - `README.md`: Index for documentation navigation.

## [v0.2.3] - 2026-01-17

### Changed
- Modernized setup view aesthetics and improved UX.
- Improved layout (single column stack) for better consistency.
- Corrected copy button placement in textareas.

## [v0.2.2] - 2026-01-17

### Added
- Setup success page displaying SP metadata, configured routes, and next steps tips
- `setup-success` route and controller method
- English and Spanish translations for the success page

## [v0.2.1] - 2026-01-17

### Fixed
- Escape XML placeholder in setup view to prevent PHP parse error
- Hybrid client/server metadata fetch with CORS fallback prompt

## [v0.2.0] - 2026-01-17

### Added
- First-deploy setup wizard UI (`/saml2/setup`)
- Admin management panel (`/saml2/admin`) for IDP CRUD
- Attribute mapping editor per IDP
- Client-side metadata fetch for better network compatibility
- `Saml2Setting` model for setup state tracking
- `saml2:reset-setup` Artisan command
- English and Spanish translations
- Configurable admin middleware and routes

### Changed
- UI is now vanilla (no Livewire/Tailwind dependencies)

## [0.1.4] - 2026-01-16

### Changed
- Added error handling when user listener has errors.

## [0.1.3] - 2026-01-16

### Changed
- ACS route now generic, getting idp from DB.

## [0.1.2] - 2026-01-16

### Added
- Fallback to default IDP if no IDP is found in the database

## [0.1.1] - 2026-01-16

### Changed
- Attribute mapping is now stored in the database

### Fixed
- /metadata route now returns valid XML

## [0.1.0] - 2025-01-16

### Added
- Initial release
- Multi-IDP support with database storage
- Environment-based IDP configuration option
- Artisan commands: `saml2:create-idp`, `saml2:list-idps`, `saml2:test-idp`, `saml2:delete-idp`, `saml2:generate-cert`, `saml2:refresh-metadata`
- Event-driven authentication via `Saml2LoginEvent`
- Metadata import from URL
- SP metadata endpoint
- Single Sign-On (SSO) and Single Logout (SLO) support
