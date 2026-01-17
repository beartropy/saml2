# Installation Guide

This guide covers the complete installation process for the `beartropy/saml2` package in your Laravel application.

## Prerequisites

- **PHP** 8.1 or higher
- **Laravel** 10.x, 11.x, or 12.x
- PHP **openssl** extension
- PHP **dom** extension

---

## Installation via Composer

```bash
composer require beartropy/saml2
```

---

## Publishing Assets

The package includes several assets that you can publish according to your needs.

### 1. Publish Configuration (Required)

```bash
php artisan vendor:publish --tag=beartropy-saml2-config
```

This creates the `config/beartropy-saml2.php` file where you can customize all package options.

> **Important**: This step is required to configure your Service Provider (SP).

---

### 2. Run Migrations (Required)

Migrations create the necessary tables to store IDPs and configuration.

```bash
php artisan migrate
```

#### Created Tables

| Table | Description |
|-------|-------------|
| `beartropy_saml2_idps` | Stores Identity Provider configurations |
| `beartropy_saml2_settings` | Stores general package settings |

#### `beartropy_saml2_idps` Structure

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Autoincremental ID |
| `key` | string | Unique IDP identifier (slug) |
| `name` | string | Human-readable IDP name |
| `entity_id` | string | IDP Entity ID |
| `sso_url` | string | Single Sign-On URL |
| `slo_url` | string (nullable) | Single Logout URL |
| `x509_cert` | text | IDP x509 certificate |
| `x509_cert_multi` | json (nullable) | Multiple certificates |
| `metadata_url` | string (nullable) | Metadata URL for refreshing |
| `metadata` | json (nullable) | Additional configuration data |
| `attribute_mapping` | json (nullable) | Custom attribute mapping |
| `is_active` | boolean | IDP active/inactive status |
| `created_at` | timestamp | Creation date |
| `updated_at` | timestamp | Update date |

#### `beartropy_saml2_settings` Structure

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Autoincremental ID |
| `key` | string | Setting key |
| `value` | text (nullable) | Setting value |
| `created_at` | timestamp | Creation date |
| `updated_at` | timestamp | Update date |

---

### 3. Publish Views (Optional)

If you wish to customize the look of the setup wizard or admin panel:

```bash
php artisan vendor:publish --tag=beartropy-saml2-views
```

This creates files in `resources/views/vendor/beartropy-saml2/`.

#### View Structure

```
resources/views/vendor/beartropy-saml2/
├── setup.blade.php              # Initial configuration wizard
├── setup-success.blade.php      # Success page after setup
└── admin/
    ├── index.blade.php          # Admin panel dashboard
    ├── idp-form.blade.php       # IDP create/edit form
    ├── mapping.blade.php        # Attribute mapping editor
    └── partials/
        └── layout.blade.php     # Admin panel base layout
```

> **Note**: Views use vanilla HTML/CSS without Tailwind or Livewire dependencies, working with any Laravel stack.

---

### 4. Publish Translations (Optional)

To customize interface text:

```bash
php artisan vendor:publish --tag=beartropy-saml2-lang
```

This creates files in `lang/vendor/beartropy-saml2/`.

#### Included Languages

- **English** (`en`)
- **Spanish** (`es`)

#### Translation Structure

```
lang/vendor/beartropy-saml2/
├── en/
│   └── saml2.php
└── es/
    └── saml2.php
```

---

### 5. Publish Login Listener (Recommended)

This command creates a base listener that you can customize to handle SAML login:

```bash
php artisan saml2:publish-listener
```

This creates `app/Listeners/HandleSaml2Login.php`:

```php
<?php

namespace App\Listeners;

use Beartropy\Saml2\Events\Saml2LoginEvent;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class HandleSaml2Login
{
    public function handle(Saml2LoginEvent $event): void
    {
        $email = $event->getEmail();
        $name = $event->getName();

        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name ?? $email]
        );

        // Optional: update user data
        // $user->update(['name' => $name]);

        // Optional: assign roles (if using spatie/laravel-permission)
        // $roles = $event->getRawAttribute('http://schemas.xmlsoap.org/claims/Group');
        // if ($roles) {
        //     $user->syncRoles($roles);
        // }

        // Authenticate user
        Auth::login($user, remember: true);
    }
}
```

> **Note**: In Laravel 11/12, events are auto-discovered. The listener will be registered without additional configuration.

---

## Publish Everything at Once

To publish all assets with a single command:

```bash
# Configuration
php artisan vendor:publish --tag=beartropy-saml2-config

# Views
php artisan vendor:publish --tag=beartropy-saml2-views

# Translations
php artisan vendor:publish --tag=beartropy-saml2-lang

# Migrations
php artisan migrate
```

Or use the provider directly:

```bash
php artisan vendor:publish --provider="Beartropy\Saml2\Saml2ServiceProvider"
```

---

## Generate SP Certificates (Recommended)

For production environments, generate certificates to sign SAML requests:

```bash
php artisan saml2:generate-cert
```

This command:
1. Generates an RSA public/private key pair
2. Creates a self-signed X.509 certificate
3. Saves the values in your `.env` file

### Generated Environment Variables

```env
SAML2_SP_CERT="-----BEGIN CERTIFICATE-----
MIIDqTCCApGg...
-----END CERTIFICATE-----"

SAML2_SP_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBg...
-----END PRIVATE KEY-----"
```

> **Security**: Never share the private key. Ensure `.env` is in `.gitignore`.

---

## Minimum .env Configuration

After installation, add these variables to your `.env`:

```env
# Your app's entity identifier (required)
SAML2_SP_ENTITY_ID=https://your-app.com

# Optional but recommended
SAML2_LOGIN_REDIRECT=/dashboard
SAML2_LOGOUT_REDIRECT=/
```

---

## Verify Installation

### 1. Verify routes are registered:

```bash
php artisan route:list --name=saml2
```

You should see routes like:
- `saml2.login`
- `saml2.acs`
- `saml2.metadata`
- `saml2.logout`
- `saml2.setup`
- `saml2.admin.*`

### 2. Verify migrations were executed:

```bash
php artisan migrate:status
```

Look for:
- `create_beartropy_saml2_idps_table`
- `create_beartropy_saml2_settings_table`

### 3. Verify access to Setup Wizard:

Open in your browser:
```
https://your-app.com/saml2/setup
```

You should see the initial configuration wizard.

---

## Available Artisan Commands

The package includes several commands to manage SAML2:

| Command | Description |
|---------|-------------|
| `saml2:create-idp {key}` | Create a new IDP |
| `saml2:list-idps` | List all configured IDPs |
| `saml2:test-idp {key}` | Test an IDP's configuration |
| `saml2:delete-idp {key}` | Delete an IDP |
| `saml2:generate-cert` | Generate SP certificates |
| `saml2:refresh-metadata` | Refresh IDP metadata from URLs |
| `saml2:publish-listener` | Publish login listener |
| `saml2:reset-setup` | Reset to initial setup state |

### Usage Examples

```bash
# Create IDP from metadata URL
php artisan saml2:create-idp azure --from-url=https://login.microsoftonline.com/{tenant}/federationmetadata/2007-06/federationmetadata.xml

# Create IDP interactively
php artisan saml2:create-idp okta --interactive

# List IDPs
php artisan saml2:list-idps

# Test connection with IDP
php artisan saml2:test-idp azure

# Refresh metadata for all IDPs
php artisan saml2:refresh-metadata

# Reset setup (access the wizard again)
php artisan saml2:reset-setup
```

---

## Troubleshooting

### Error: "Route [saml2.xxx] not found"

Run `php artisan route:clear` and verify the package is correctly installed.

### Error: "Table beartropy_saml2_idps doesn't exist"

Run the migrations: `php artisan migrate`

### Setup Wizard is not accessible

Verify that:
1. No IDP is already configured (wizard only appears before the first IDP)
2. If you need to access it again: `php artisan saml2:reset-setup`

### Certificate/Signature Error

Generate SP certificates: `php artisan saml2:generate-cert`

---

## Next Step

After completing installation, continue with [Configuration](CONFIG.md) and [Initial Setup](SETUP.md).
