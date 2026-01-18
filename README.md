<div align="center">
    <h1>🛡️ Beartropy SAML2</h1>
    <p><strong>Multi-IDP SAML2 Service Provider integration for Laravel using <a href="https://github.com/onelogin/php-saml">onelogin/php-saml</a></strong></p>
    <p>Integrate your Laravel app with multiple Identity Providers (Azure AD, Okta, ADFS, etc.)</p>
</div>

<div align="center">
    <a href="https://packagist.org/packages/beartropy/saml2"><img src="https://img.shields.io/packagist/v/beartropy/saml2.svg?style=flat-square&color=indigo" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/beartropy/saml2"><img src="https://img.shields.io/packagist/dt/beartropy/saml2.svg?style=flat-square&color=blue" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/beartropy/saml2"><img src="https://img.shields.io/packagist/l/beartropy/saml2?style=flat-square&color=slate" alt="License"></a>
</div>

## Features

- 🔐 Configure your Laravel app as a SAML 2.0 Service Provider
- 🏢 Support for multiple Identity Providers (Azure AD, Okta, ADFS, etc.)
- 🖥️ **Web UI** for first-deploy setup and admin management
- 📦 IDP configuration from environment variables or database
- 🔄 Automatic metadata import from IDP URLs
- 🎯 Event-driven authentication (handle login your way)
- ⚙️ Artisan commands for IDP management
- 🌐 English and Spanish translations included

## 📚 Documentation

�👉 **[Read the full documentation at beartropy.com/saml2](https://beartropy.com/saml2)**

## Installation

```bash
composer require beartropy/saml2
```

Publish the configuration and run migrations:

```bash
php artisan vendor:publish --tag=beartropy-saml2-config
php artisan migrate
```

## Quick Start

### Option A: Using the Setup Wizard (Recommended)

After installation, simply navigate to:

```
https://your-app.com/saml2/setup
```

The **First-Deploy Setup Wizard** will guide you through:
1. Displaying your SP metadata (Entity ID, ACS URL, Metadata URL) to share with your IDP administrator
2. Configuring your IDP via:
   - **Metadata URL** - Fetch and parse automatically
   - **Paste XML** - Copy/paste metadata from your IDP
   - **Manual Entry** - Enter Entity ID, SSO URL, Certificate manually

> **Note**: The setup wizard is only accessible before the first IDP is configured. After that, use the Admin Panel.

### Option B: Using Artisan Commands

#### 1. Configure your Service Provider

Add to your `.env`:

```env
SAML2_SP_ENTITY_ID=https://your-app.com
```

For signed assertions (recommended), generate SP certificates:

```bash
php artisan saml2:generate-cert
```

#### 2. Add an Identity Provider

From a metadata URL:
```bash
php artisan saml2:create-idp azure --from-url=https://login.microsoftonline.com/.../federationmetadata.xml
```

Or interactively:
```bash
php artisan saml2:create-idp azure --interactive
```

---

## Admin Panel

Once configured, manage your SAML2 settings through the web-based admin panel:

```
https://your-app.com/saml2/admin
```

### Features

| Feature | Description |
|---------|-------------|
| **Dashboard** | View SP metadata and list all configured IDPs |
| **Create IDP** | Add new Identity Providers with metadata import |
| **Edit IDP** | Modify IDP settings (Entity ID, SSO URL, Certificate, etc.) |
| **Attribute Mapping** | Configure per-IDP attribute mapping or use global config |
| **Toggle Status** | Activate/deactivate IDPs without deleting |
| **Refresh Metadata** | Auto-update IDP settings from metadata URL |
| **Delete IDP** | Remove an IDP from database |

### Admin Configuration

Customize the admin panel in `config/beartropy-saml2.php`:

```php
// Enable/disable admin panel
'admin_enabled' => env('SAML2_ADMIN_ENABLED', true),

// Route prefix (default: /saml2/admin)
'admin_route_prefix' => env('SAML2_ADMIN_PREFIX', 'saml2/admin'),

// Middleware to protect admin routes
'admin_middleware' => ['web', 'auth'],  // Add 'admin' or custom middleware as needed
```

#### Protecting Admin Routes

Add your own authorization middleware:

```php
// config/beartropy-saml2.php
'admin_middleware' => ['web', 'auth', 'can:manage-saml'],
```

Or use a Gate in your `AuthServiceProvider`:

```php
Gate::define('manage-saml', function ($user) {
    return $user->hasRole('admin');
});
```

---

## Handle Authentication Events

Publish a standard listener:

```bash
php artisan saml2:publish-listener
```

This creates `app/Listeners/HandleSaml2Login.php`:

```php
public function handle(Saml2LoginEvent $event): void
{
    // Find or create user
    $user = User::firstOrCreate(
        ['email' => $event->getEmail()],
        ['name' => $event->getName()]
    );
    
    // Log them in
    Auth::login($user);
}
```

> **Note**: In Laravel 11/12, events are auto-discovered.

---

## Integrate with Your Routes

**Simple** - a login link:
```html
<a href="{{ route('saml2.login', ['idp' => 'azure']) }}">
    Login with Azure AD
</a>
```

**Full Integration** - replacing auth routes:
```php
// routes/auth.php

Route::middleware('guest')->group(function () {
    if (app()->environment('local')) {
        // Local login for development
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'authenticate']);
    } else {
        // SAML Login - redirects to IDP
        Route::get('/login', function () {
            return redirect()->route('saml2.login', ['idp' => 'your-idp-key']);
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

---

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
| `saml2:reset-setup` | Reset to first-deploy state |

### Reset Setup Command

If you need to reconfigure from scratch:

```bash
# Reset setup state only (keeps IDPs)
php artisan saml2:reset-setup

# Reset setup state AND delete all IDPs
php artisan saml2:reset-setup --with-idps
```

After running this, the setup wizard at `/saml2/setup` will be accessible again.

---

## Routes

The package registers these routes:

### SAML Routes

| Route | Method | Description |
|-------|--------|-------------|
| `/saml2/setup` | GET | First-deploy setup wizard |
| `/saml2/login/{idp?}` | GET | Initiate SSO login |
| `/saml2/acs` | POST | Generic ACS - auto-detects IDP |
| `/saml2/acs/{idp}` | POST | ACS with explicit IDP key |
| `/saml2/sls/{idp}` | GET/POST | Single Logout Service |
| `/saml2/metadata` | GET | SP Metadata XML |
| `/saml2/logout/{idp?}` | GET | Initiate logout |

### Admin Routes

| Route | Method | Description |
|-------|--------|-------------|
| `/saml2/admin` | GET | Admin dashboard |
| `/saml2/admin/idp/create` | GET | Create IDP form |
| `/saml2/admin/idp` | POST | Store new IDP |
| `/saml2/admin/idp/{id}` | GET/PUT | Edit/update IDP |
| `/saml2/admin/idp/{id}` | DELETE | Delete IDP |
| `/saml2/admin/idp/{id}/toggle` | POST | Activate/deactivate |
| `/saml2/admin/idp/{id}/mapping` | GET/POST | Attribute mapping editor |
| `/saml2/admin/idp/{id}/refresh` | POST | Refresh metadata |

---

## Attribute Mapping

Attribute mapping allows you to normalize SAML attributes from different IDPs.

### Global Mapping (Config)

Set default mapping in `config/beartropy-saml2.php`:

```php
'attribute_mapping' => [
    'email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
    'name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name',
    'first_name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname',
    'last_name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname',
],
```

### Per-IDP Mapping (Admin Panel or Code)

Each IDP can have custom mapping that overrides the global config.

**Via Admin Panel:**
1. Go to `/saml2/admin`
2. Click "Mapping" on any IDP
3. Toggle "Use global mapping" off
4. Add your custom field mappings

**Via Code:**

```php
use Beartropy\Saml2\Models\Saml2Idp;

$idp = Saml2Idp::where('key', 'azure')->first();
$idp->attribute_mapping = [
    'email' => 'http://schemas.microsoft.com/identity/claims/emailaddress',
    'name' => 'http://schemas.microsoft.com/identity/claims/displayname',
    'roles' => 'http://schemas.microsoft.com/ws/2008/06/identity/claims/groups',
];
$idp->save();
```

### Using Attributes in Your Listener

```php
public function handle(Saml2LoginEvent $event): void
{
    // Mapped attributes (uses IDP-specific or falls back to global)
    $email = $event->getEmail();              // Shorthand
    $name = $event->getName();                // Shorthand
    $custom = $event->getAttribute('roles');  // Custom mapped field
    
    // Raw SAML attributes (always available)
    $rawEmail = $event->getRawAttribute('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress');
    
    // All data
    $allMapped = $event->attributes;
    $allRaw = $event->rawAttributes;
}
```

---

## Configuration Reference

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

### Redirects

```php
'login_redirect' => env('SAML2_LOGIN_REDIRECT', '/'),
'logout_redirect' => env('SAML2_LOGOUT_REDIRECT', '/'),
'error_redirect' => env('SAML2_ERROR_REDIRECT', '/login'),
```

---

## Events

### Saml2LoginEvent

Dispatched after successful SAML authentication.

**Properties:**
```php
$event->idpKey       // Key of the authenticating IDP
$event->nameId       // SAML NameID (usually email)
$event->attributes   // Mapped attributes (per config)
$event->rawAttributes // Original SAML attributes
$event->sessionIndex // For SLO
```

**Helper Methods:**
```php
$event->getEmail();                    // Get email
$event->getName();                     // Get name
$event->getAttribute('field');          // Get mapped attribute
$event->getAttribute('field', 'default'); // With default
$event->getRawAttribute('saml_uri');    // Get raw SAML attribute
$event->toArray();                      // All data as array
```

### Saml2LogoutEvent

Dispatched after successful SAML logout.

---

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

---

## Internationalization

The package includes English and Spanish translations. To publish and customize:

```bash
php artisan vendor:publish --tag=beartropy-saml2-lang
```

This creates files in `lang/vendor/beartropy-saml2/`.

---

## Customizing Views

To customize the setup wizard or admin panel views:

```bash
php artisan vendor:publish --tag=beartropy-saml2-views
```

This publishes views to `resources/views/vendor/beartropy-saml2/`.

> **Note**: The UI is vanilla HTML/CSS (no Tailwind or Livewire dependencies), so it works with any Laravel stack.

---

## Documentation

For more detailed information, please refer to the following guides:

- **[English Documentation](docs/en/README.md)**
- **[Documentación en Español](docs/es/README.md)**

Includes detailed guides for [Configuration](docs/en/CONFIG.md), [Installation](docs/en/INSTALL.md), [Initial Setup](docs/en/SETUP.md), and the [Admin UI](docs/en/UI.md).

---

## License

MIT License - see [LICENSE.md](LICENSE.md)
