# Configuration File

This document describes all the available options in the `config/beartropy-saml2.php` configuration file.

## Publishing the Configuration

To publish the configuration file in your Laravel application:

```bash
php artisan vendor:publish --tag=beartropy-saml2-config
```

This will create the `config/beartropy-saml2.php` file in your project.

---

## Service Provider (SP) Configuration

The Service Provider is your Laravel application. This section defines how your application identifies itself to Identity Providers.

```php
'sp' => [
    // Unique identifier for your SP (usually your app URL)
    'entityId' => env('SAML2_SP_ENTITY_ID'),
    
    // SP x509 certificate and private key
    'x509cert' => env('SAML2_SP_CERT'),
    'privateKey' => env('SAML2_SP_PRIVATE_KEY'),
    
    // Custom URLs (null = auto-generate based on routes)
    'acs_url' => env('SAML2_SP_ACS_URL'),
    'sls_url' => env('SAML2_SP_SLS_URL'),
    'metadata_url' => env('SAML2_SP_METADATA_URL'),
    
    // NameID format
    'nameIdFormat' => env('SAML2_SP_NAMEID_FORMAT', 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress'),
],
```

### Detailed Options

| Option | Environment Variable | Description | Example |
|--------|----------------------|-------------|---------|
| `entityId` | `SAML2_SP_ENTITY_ID` | Unique identifier of your application. IDPs will use this value to identify requests. | `https://your-app.com` |
| `x509cert` | `SAML2_SP_CERT` | Public X.509 certificate of the SP for signing requests. | Certificate content |
| `privateKey` | `SAML2_SP_PRIVATE_KEY` | Private key corresponding to the certificate. | Key content |
| `acs_url` | `SAML2_SP_ACS_URL` | Assertion Consumer Service URL. If `null`, it's auto-generated. | `https://your-app.com/saml2/acs` |
| `sls_url` | `SAML2_SP_SLS_URL` | Single Logout Service URL. If `null`, it's auto-generated. | `https://your-app.com/saml2/sls/{idp}` |
| `metadata_url` | `SAML2_SP_METADATA_URL` | URL where the SP metadata XML is exposed. | `https://your-app.com/saml2/metadata` |
| `nameIdFormat` | `SAML2_SP_NAMEID_FORMAT` | NameID format expected in SAML responses. | See formats below |

### Available NameID Formats

- `urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress` (default, recommended)
- `urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified`
- `urn:oasis:names:tc:SAML:2.0:nameid-format:persistent`
- `urn:oasis:names:tc:SAML:2.0:nameid-format:transient`

---

## IDP Source

This configuration defines where Identity Provider configurations are loaded from.

```php
'idp_source' => env('SAML2_IDP_SOURCE', 'database'),
```

### Available Options

| Value | Description | Recommended Use |
|-------|-------------|-----------------|
| `env` | Only from environment variables (single IDP) | Simple environments with a single IDP |
| `database` | Only from database (multiple IDPs) | Most production cases |
| `both` | Checks env first, then database | Local development with a test IDP |

---

## Default IDP (via environment)

When using `env` or `both` as `idp_source`, configure the default IDP here.

```php
'default_idp' => [
    'key' => env('SAML2_IDP_KEY', 'default'),
    'name' => env('SAML2_IDP_NAME', 'Default IDP'),
    'entityId' => env('SAML2_IDP_ENTITY_ID'),
    'ssoUrl' => env('SAML2_IDP_SSO_URL'),
    'sloUrl' => env('SAML2_IDP_SLO_URL'),
    'x509cert' => env('SAML2_IDP_CERT'),
],
```

### Environment Variables for IDP

```env
SAML2_IDP_SOURCE=env
SAML2_IDP_KEY=azure
SAML2_IDP_NAME="Azure Active Directory"
SAML2_IDP_ENTITY_ID=https://sts.windows.net/{tenant-id}/
SAML2_IDP_SSO_URL=https://login.microsoftonline.com/{tenant-id}/saml2
SAML2_IDP_SLO_URL=https://login.microsoftonline.com/{tenant-id}/saml2
SAML2_IDP_CERT="MIICpDCCAYwCCQ..."
```

---

## Route Configuration

```php
'route_prefix' => env('SAML2_ROUTE_PREFIX', 'saml2'),
'route_middleware' => ['web'],
```

### Options

| Option | Environment Variable | Description | Default |
|--------|----------------------|-------------|---------|
| `route_prefix` | `SAML2_ROUTE_PREFIX` | Prefix for all SAML2 routes | `saml2` |
| `route_middleware` | - | Middleware applied to SAML2 routes | `['web']` |

### Resulting Routes

With the default `saml2` prefix:

| Route | Method | Description |
|-------|--------|-------------|
| `/saml2/setup` | GET | Initial configuration wizard |
| `/saml2/login/{idp?}` | GET | Initiate SSO login |
| `/saml2/acs` | POST | Generic ACS (auto-detects IDP) |
| `/saml2/acs/{idp}` | POST | ACS with explicit IDP key |
| `/saml2/sls/{idp}` | GET/POST | Single Logout Service |
| `/saml2/metadata` | GET | SP Metadata XML |
| `/saml2/logout/{idp?}` | GET | Initiate logout |

---

## Admin Panel

```php
'admin_enabled' => env('SAML2_ADMIN_ENABLED', true),
'admin_route_prefix' => env('SAML2_ADMIN_PREFIX', 'saml2/admin'),
'admin_middleware' => ['web', 'auth'],
'layout' => env('SAML2_ADMIN_LAYOUT', 'beartropy-saml2::admin.partials.layout'),
```

### Admin Panel Options

| Option | Environment Variable | Description | Default |
|--------|----------------------|-------------|---------|
| `admin_enabled` | `SAML2_ADMIN_ENABLED` | Enable/disable the admin panel | `true` |
| `admin_route_prefix` | `SAML2_ADMIN_PREFIX` | Prefix for administration routes | `saml2/admin` |
| `admin_middleware` | - | Middleware to protect admin routes | `['web', 'auth']` |
| `layout` | `SAML2_ADMIN_LAYOUT` | Blade layout used for the panel | Internal package layout |

### Protecting the Admin Panel

To restrict access to administrators only:

```php
// config/beartropy-saml2.php
'admin_middleware' => ['web', 'auth', 'can:manage-saml'],
```

Define the Gate in your `AuthServiceProvider`:

```php
// app/Providers/AuthServiceProvider.php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('manage-saml', function ($user) {
        return $user->hasRole('admin'); // or your permission logic
    });
}
```

### Using a Custom Layout

To integrate the admin panel with your application's layout:

```php
// config/beartropy-saml2.php
'layout' => 'layouts.admin', // Your custom layout
```

Your layout must include:
- `@yield('title')` for the page title
- `@yield('content')` for the main content
- `@yield('scripts')` for additional scripts

---

## Redirect URLs

```php
'login_redirect' => env('SAML2_LOGIN_REDIRECT', '/'),
'logout_redirect' => env('SAML2_LOGOUT_REDIRECT', '/'),
'error_redirect' => env('SAML2_ERROR_REDIRECT', '/login'),
```

| Option | Environment Variable | Description | Default |
|--------|----------------------|-------------|---------|
| `login_redirect` | `SAML2_LOGIN_REDIRECT` | Redirect URL after successful login | `/` |
| `logout_redirect` | `SAML2_LOGOUT_REDIRECT` | Redirect URL after logout | `/` |
| `error_redirect` | `SAML2_ERROR_REDIRECT` | Redirect URL in case of SAML error | `/login` |

---

## User Model

```php
'user_model' => \App\Models\User::class,
```

Specifies your application's user model class. Used internally for authentication lookups.

---

## Metadata Import

```php
'allow_metadata_import' => env('SAML2_ALLOW_METADATA_IMPORT', true),
```

| Option | Environment Variable | Description | Default |
|--------|----------------------|-------------|---------|
| `allow_metadata_import` | `SAML2_ALLOW_METADATA_IMPORT` | Allow importing IDP metadata from URLs | `true` |

> **Security Note**: In high-security environments, consider disabling this option and configuring IDPs manually.

---

## Attribute Mapping

Defines how SAML attributes map to user fields in your application.

```php
'attribute_mapping' => [
    'email' => 'email',
    'name' => 'displayname',
    'firstname' => 'firstname',
    'lastname' => 'lastname',
    'username' => 'username',
    'roles' => 'roles',
    'groups' => 'groups',
],
```

### Global vs Per-IDP Mapping

This mapping is **global** and applies to all IDPs. However, each IDP can have its own custom mapping that overrides the global one.

### Mapping Examples for Common Providers

**Azure AD:**
```php
'attribute_mapping' => [
    'email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
    'name' => 'http://schemas.microsoft.com/identity/claims/displayname',
    'firstname' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname',
    'lastname' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname',
    'roles' => 'http://schemas.microsoft.com/ws/2008/06/identity/claims/groups',
],
```

**Okta:**
```php
'attribute_mapping' => [
    'email' => 'email',
    'name' => 'name',
    'firstname' => 'firstName',
    'lastname' => 'lastName',
],
```

**ADFS:**
```php
'attribute_mapping' => [
    'email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
    'name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name',
    'groups' => 'http://schemas.xmlsoap.org/claims/Group',
],
```

---

## Security Settings

Advanced settings for secure SAML communication.

```php
'security' => [
    'nameIdEncrypted' => false,
    'authnRequestsSigned' => false,
    'logoutRequestSigned' => false,
    'logoutResponseSigned' => false,
    'signMetadata' => false,
    'wantMessagesSigned' => false,
    'wantAssertionsSigned' => false,
    'wantAssertionsEncrypted' => false,
    'wantNameIdEncrypted' => false,
    'requestedAuthnContext' => true,
    'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
    'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',
],
```

### Security Options

| Option | Description | Production Recommendation |
|--------|-------------|---------------------------|
| `nameIdEncrypted` | Encrypt NameID in requests | `false` (rarely needed) |
| `authnRequestsSigned` | Sign authentication requests | `true` |
| `logoutRequestSigned` | Sign logout requests | `true` |
| `logoutResponseSigned` | Sign logout responses | `true` |
| `signMetadata` | Sign SP metadata XML | `true` |
| `wantMessagesSigned` | Require signed messages from IDP | `true` |
| `wantAssertionsSigned` | Require signed assertions | `true` |
| `wantAssertionsEncrypted` | Require encrypted assertions | `false` (IDP dependent) |
| `wantNameIdEncrypted` | Require encrypted NameID | `false` |
| `requestedAuthnContext` | Request authentication context | `true` |
| `signatureAlgorithm` | Signature algorithm | RSA-SHA256 |
| `digestAlgorithm` | Digest algorithm | SHA256 |

### Recommended Production Configuration

```php
'security' => [
    'authnRequestsSigned' => true,
    'logoutRequestSigned' => true,
    'logoutResponseSigned' => true,
    'signMetadata' => true,
    'wantMessagesSigned' => true,
    'wantAssertionsSigned' => true,
    // ... rest as default values
],
```

> **Important**: To use signatures, you must first generate SP certificates with:
> ```bash
> php artisan saml2:generate-cert
> ```

---

## Debug Mode

```php
'debug' => env('SAML2_DEBUG', false),
'strict' => env('SAML2_STRICT', true),
```

| Option | Environment Variable | Description | Default |
|--------|----------------------|-------------|---------|
| `debug` | `SAML2_DEBUG` | Enable debug mode (detailed logs) | `false` |
| `strict` | `SAML2_STRICT` | Strict mode (validates signatures/schemas) | `true` |

> **Warning**: Never enable `debug` in production. It may expose sensitive information.

---

## Full .env Example

```env
# Service Provider
SAML2_SP_ENTITY_ID=https://your-app.com

# IDP Source
SAML2_IDP_SOURCE=database

# Routes
SAML2_ROUTE_PREFIX=saml2
SAML2_ADMIN_PREFIX=saml2/admin
SAML2_ADMIN_ENABLED=true

# Redirects
SAML2_LOGIN_REDIRECT=/dashboard
SAML2_LOGOUT_REDIRECT=/
SAML2_ERROR_REDIRECT=/login

# Security
SAML2_DEBUG=false
SAML2_STRICT=true

# Metadata Import
SAML2_ALLOW_METADATA_IMPORT=true
```

---

## Next Step

After configuring the file, proceed with [Installation](INSTALL.md) and then the [Initial Setup](SETUP.md).
