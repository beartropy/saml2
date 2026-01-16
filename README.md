# beartropy/saml2

Multi-IDP SAML2 Service Provider integration for Laravel using [onelogin/php-saml](https://github.com/onelogin/php-saml).

## Features

- 🔐 Configure your Laravel app as a SAML 2.0 Service Provider
- 🏢 Support for multiple Identity Providers (Azure AD, Okta, ADFS, etc.)
- 📦 IDP configuration from environment variables or database
- 🔄 Automatic metadata import from IDP URLs
- 🎯 Event-driven authentication (handle login your way)
- ⚙️ Artisan commands for IDP management

## Installation

```bash
composer require beartropy/saml2
```

Publish the configuration and migrations:

```bash
php artisan vendor:publish --tag=beartropy-saml2-config
php artisan vendor:publish --tag=beartropy-saml2-migrations
php artisan migrate
```

## Quick Start

### 1. Configure your Service Provider

Add these to your `.env`:

```env
SAML2_SP_ENTITY_ID=https://your-app.com
```

For signed assertions (recommended), generate SP certificates:

```bash
php artisan saml2:generate-cert
```

### 2. Add an Identity Provider

From a metadata URL:
```bash
php artisan saml2:create-idp azure --from-url=https://login.microsoftonline.com/.../federationmetadata.xml
```

Or interactively:
```bash
php artisan saml2:create-idp azure --interactive
```

### 3. Handle Authentication Events

Publica un listener standard:

```bash
php artisan saml2:publish-listener
```

Esto crea `app/Listeners/HandleSaml2Login.php` que puedes personalizar:

```php
// Busca/crea usuario y lo autentica
$user = User::firstOrCreate(
    ['email' => $event->getEmail()],
    ['name' => $event->getName()]
);
Auth::login($user);
```

> **Note**: En Laravel 11/12, los eventos se descubren automáticamente.

### 4. Integrate with Your Routes

**Simple** - un link de login:
```html
<a href="{{ route('saml2.login', ['idp' => 'azure']) }}">
    Login with Azure AD
</a>
```

**Integración completa** - reemplazando rutas de auth:
```php
// routes/auth.php

Route::middleware('guest')->group(function () {
    if (app()->environment('local')) {
        // Login local para desarrollo
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'authenticate']);
    } else {
        // SAML Login - redirige al IDP
        Route::get('/login', function () {
            return redirect()->route('saml2.login', ['idp' => 'tu-idp-key']);
        })->name('login');
    }
});

Route::middleware('auth')->group(function () {
    if (app()->environment('local')) {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    } else {
        // SAML Logout (SLO)
        Route::match(['get', 'post'], '/logout', function () {
            return redirect()->route('saml2.logout');
        })->name('logout');
    }
});
```

## Artisan Commands

| Command | Description |
|---------|-------------|
| `saml2:create-idp {key}` | Create a new IDP |
| `saml2:list-idps` | List all configured IDPs |
| `saml2:test-idp {key}` | Test IDP configuration |
| `saml2:delete-idp {key}` | Delete an IDP |
| `saml2:generate-cert` | Generate SP certificates |
| `saml2:refresh-metadata` | Refresh IDP metadata from URLs |
| `saml2:publish-listener` | Publish standard login listener |

## Routes

The package registers these routes:

| Route | Method | Description |
|-------|--------|-------------|
| `/saml2/login/{idp?}` | GET | Initiate SSO login (idp optional, defaults to first active) |
| `/saml2/acs` | POST | **Generic ACS** - auto-detects IDP from response |
| `/saml2/acs/{idp}` | POST | ACS with explicit IDP key |
| `/saml2/sls/{idp}` | GET/POST | Single Logout Service |
| `/saml2/metadata` | GET | SP Metadata XML (uses generic ACS URL) |
| `/saml2/logout/{idp?}` | GET | Initiate logout |

> **Note**: El metadata SP ahora usa `/saml2/acs` (genérico), por lo que puedes registrar un único SP en múltiples IDPs sin preocuparte por URLs específicas.

## Configuration

### IDP Source

```php
// config/beartropy-saml2.php

// 'env' - Single IDP from environment only
// 'database' - Multiple IDPs from database
// 'both' - Check env first, then database
'idp_source' => env('SAML2_IDP_SOURCE', 'database'),
```

### Environment-based IDP

For single IDP setups via `.env`:

```env
SAML2_IDP_SOURCE=env
SAML2_IDP_KEY=default
SAML2_IDP_NAME="My IDP"
SAML2_IDP_ENTITY_ID=https://idp.example.com
SAML2_IDP_SSO_URL=https://idp.example.com/sso
SAML2_IDP_SLO_URL=https://idp.example.com/slo
SAML2_IDP_CERT="MIICpDCCAYwCCQ..."
```

### Attribute Mapping (Optional)

El mapeo de atributos es **opcional**. Siempre tienes acceso a los atributos SAML originales via `$event->rawAttributes`. 

**Mapping global** (en config):
```php
'attribute_mapping' => [
    'email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
    'name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name',
],
```

**Mapping por IDP** (en base de datos):

Cada IDP puede tener su propio mapping guardado en la columna `attribute_mapping`. Si está vacío, usa el mapping global como fallback.

```php
// Configurar mapping específico para un IDP
$idp = Saml2Idp::where('key', 'azure')->first();
$idp->attribute_mapping = [
    'email' => 'http://schemas.microsoft.com/identity/claims/emailaddress',
    'name' => 'http://schemas.microsoft.com/identity/claims/displayname',
    'roles' => 'http://schemas.microsoft.com/ws/2008/06/identity/claims/groups',
];
$idp->save();
```

## Events

### Saml2LoginEvent

Dispatched after successful SAML authentication.

**Propiedades públicas:**
```php
$event->idpKey       // Key del IDP que autenticó
$event->nameId       // SAML NameID (usualmente email)
$event->attributes   // Atributos mapeados (según config)
$event->rawAttributes // Atributos SAML originales
$event->sessionIndex // Para SLO
```

**Métodos helper:**
```php
// Obtener email (busca en múltiples fuentes comunes)
$event->getEmail();

// Obtener nombre
$event->getName();

// Obtener atributo mapeado
$event->getAttribute('email');
$event->getAttribute('custom_field', 'default_value');

// Obtener atributo SAML original
$event->getRawAttribute('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/role');

// Obtener toda la data como array
$event->toArray();
// Retorna: idp_key, name_id, email, name, attributes, raw_attributes, session_index
```

### Saml2LogoutEvent

Dispatched after successful SAML logout.

## Security

For production environments, enable signature and encryption:

```php
'security' => [
    'authnRequestsSigned' => true,
    'wantAssertionsSigned' => true,
    'signMetadata' => true,
],
```

Generate SP certificates first with `php artisan saml2:generate-cert`.

## License

MIT License - see [LICENSE.md](LICENSE.md)
