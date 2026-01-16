# Changelog

All notable changes to this project will be documented in this file.

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
