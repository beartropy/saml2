# Changelog

All notable changes to this project will be documented in this file.

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
