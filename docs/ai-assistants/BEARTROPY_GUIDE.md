# Beartropy SAML2 - Universal AI Assistant Guide

> This guide helps AI assistants generate correct code for SAML2 SSO integration in Laravel.

## Overview

**Beartropy SAML2** is a multi-IDP SAML2 Service Provider package for Laravel, built on `onelogin/php-saml`.

- **Multi-IDP**: Configure multiple Identity Providers
- **IDP Sources**: Environment variables, database, or both
- **Admin Panel**: Web UI for IDP management
- **Setup Wizard**: First-deploy configuration wizard
- **8 Artisan Commands**: CLI management

## Authentication Flow

1. User visits `/saml2/login/{idp}` → redirect to IDP
2. IDP authenticates → POSTs to `/saml2/acs/{idp}`
3. `Saml2LoginEvent` dispatched with user attributes
4. Your listener creates/finds the user and calls `Auth::login()`
5. Redirect to `login_redirect`

## Facade API

```php
use Beartropy\Saml2\Facades\Saml2;

$url = Saml2::login('azure');             // Get SSO redirect URL
$result = Saml2::processAcsResponse('azure');  // Process ACS
$xml = Saml2::getMetadataXml();           // SP metadata
```

## Event Listener

```php
use Beartropy\Saml2\Events\Saml2LoginEvent;

class HandleSaml2Login
{
    public function handle(Saml2LoginEvent $event): void
    {
        $user = User::firstOrCreate(
            ['email' => $event->getEmail()],
            ['name' => $event->getName()]
        );
        Auth::login($user);
    }
}
```

### Event Helpers
- `$event->getEmail()` — Intelligent email extraction
- `$event->getName()` — Intelligent name extraction
- `$event->getAttribute('key')` — Get mapped attribute (array when multi-valued, scalar when single)
- `$event->getAttributeAll('key')` — Get mapped attribute as an array of ALL values (use for roles/groups)
- `$event->getRawAttribute('key')` — Get raw SAML attribute (first value only)
- `$event->getRawAttributeAll('key')` — Get raw SAML attribute as an array of ALL values

## Configuration Essentials

```env
SAML2_SP_ENTITY_ID=https://your-app.com
SAML2_IDP_SOURCE=database
SAML2_LOGIN_REDIRECT=/dashboard
SAML2_LOGOUT_REDIRECT=/
```

## IDP Sources

| Source | Description |
|--------|-------------|
| `env` | Single IDP from `.env` variables |
| `database` | Multiple IDPs from `beartropy_saml2_idps` table |
| `both` | Try env first, fall back to database |

## Routes

### SAML
- `GET /saml2/login/{idp?}` — Initiate SSO
- `POST /saml2/acs/{idp}` — Assertion Consumer Service
- `POST /saml2/acs` — Auto-detect IDP from response
- `GET /saml2/metadata` — SP Metadata XML
- `GET /saml2/logout/{idp?}` — Initiate SLO

### Admin (if enabled)
- `GET /saml2/admin` — Dashboard
- CRUD for IDPs, attribute mapping, metadata refresh

## Artisan Commands

```bash
php artisan saml2:create-idp          # Create IDP
php artisan saml2:list-idps           # List IDPs
php artisan saml2:test-idp            # Test connectivity
php artisan saml2:generate-cert       # Generate SP certs
php artisan saml2:publish-listener    # Scaffold listener
php artisan saml2:refresh-metadata    # Refresh all metadata
```

## Attribute Mapping

Global mapping in config, overridable per-IDP:

```php
'attribute_mapping' => [
    'email' => 'email',
    'name' => 'displayname',
    'firstname' => 'firstname',
    'lastname' => 'lastname',
],
```

## Security Defaults

- `wantMessagesSigned`: true
- `wantAssertionsSigned`: true
- SSRF protection on metadata URLs
- Session regeneration after login
- Rate limiting on SAML routes
