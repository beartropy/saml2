# Initial Setup (First Deploy)

This guide explains the initial configuration process for the `beartropy/saml2` package using the **Setup Wizard** or Artisan commands.

## Overview

Initial setup consists of:

1. **Configuring the Service Provider (SP)**: Your Laravel application
2. **Configuring an Identity Provider (IDP)**: Azure AD, Okta, ADFS, etc.
3. **Publishing the login listener**: To handle authentication
4. **Testing the login flow**

---

## Option A: Setup Wizard (Recommended)

The Setup Wizard is the easiest way to configure SAML2. It's available only **before configuring the first IDP**.

### Accessing the Wizard

```
https://your-app.com/saml2/setup
```

### Step 1: Review SP Metadata

The wizard automatically displays your Service Provider's information that you need to share with your IDP administrator:

| Field | Description | Example |
|-------|-------------|---------|
| **Entity ID** | Unique identifier for your application | `https://your-app.com` |
| **ACS URL** | URL where the IDP will send SAML responses | `https://your-app.com/saml2/acs` |
| **Metadata URL** | URL of the SP's metadata XML file | `https://your-app.com/saml2/metadata` |
| **Metadata XML** | Full XML content to copy | `<EntityDescriptor ...>` |

> **Required Action**: Send this data to your IDP administrator to configure the application on their end.

### Step 2: Configure IDP

The wizard offers three methods to configure your IDP:

#### Method 1: From URL (Recommended)

1. Select the **"From URL"** tab
2. Enter the IDP's metadata URL
3. Click **"Fetch"**
4. The wizard will automatically parse the XML and fill in the fields

**Common Metadata URLs:**

| Provider | Metadata URL |
|----------|--------------|
| **Azure AD** | `https://login.microsoftonline.com/{tenant-id}/federationmetadata/2007-06/federationmetadata.xml` |
| **Okta** | `https://{your-domain}.okta.com/app/{app-id}/sso/saml/metadata` |
| **ADFS** | `https://{your-server}/FederationMetadata/2007-06/FederationMetadata.xml` |
| **Google** | `https://accounts.google.com/gsiwebsdk/v3/downloadmetadata` |
| **Keycloak** | `https://{server}/realms/{realm}/protocol/saml/descriptor` |

> **Note**: If download fails due to CORS, the wizard offers to use the server as a proxy.

#### Method 2: Paste XML

1. Select the **"Paste XML"** tab
2. Copy and paste the XML metadata content from your IDP
3. Click **"Parse"**
4. The wizard will extract values automatically

#### Method 3: Manual Entry

1. Select the **"Manual"** tab
2. Complete the following fields:

| Field | Required | Description |
|-------|----------|-------------|
| **IDP Key** | ✅ | Unique identifier (slug) for this IDP. E.g., `azure`, `okta` |
| **IDP Name** | ✅ | Human-readable name. E.g., `Azure Active Directory` |
| **Entity ID** | ✅ | IDP identifier |
| **SSO URL** | ✅ | IDP's Single Sign-On URL |
| **SLO URL** | ❌ | Single Logout URL (optional) |
| **X.509 Certificate** | ✅ | IDP's public certificate |

### Step 3: Save and Complete

1. Click **"Save and Complete Setup"**
2. If everything is correct, you'll see the **success page** with:
   - Summary of configured SP and IDP
   - Login and logout routes
   - Recommended next steps

---

## Setup Success Page

After completing the wizard, you'll see a page with:

### SP Information

- Configured Entity ID
- ACS URL to share with IDP
- Metadata URL for automatic import

### Configured IDP

- IDP Key (e.g., `azure`)
- IDP Name
- IDP Entity ID

### Available Routes

```
Login:  https://your-app.com/saml2/login/azure
Logout: https://your-app.com/saml2/logout/azure
```

### Next Steps

1. **Publish Listener**: `php artisan saml2:publish-listener`
2. **Review Configuration**: `config/beartropy-saml2.php`
3. **Test Login**: Use the "Test Login" button

---

## Option B: Setup via Artisan Commands

If you prefer the command line, you can configure everything without using the wizard.

### 1. Configure .env

```env
# Your app's Entity ID
SAML2_SP_ENTITY_ID=https://your-app.com

# Redirects
SAML2_LOGIN_REDIRECT=/dashboard
SAML2_LOGOUT_REDIRECT=/
```

### 2. Generate SP Certificates (Recommended)

```bash
php artisan saml2:generate-cert
```

### 3. Create IDP

**From metadata URL:**

```bash
php artisan saml2:create-idp azure --from-url=https://login.microsoftonline.com/{tenant}/federationmetadata.xml
```

**Interactive mode:**

```bash
php artisan saml2:create-idp azure --interactive
```

Interactive mode will ask for:
- IDP Name
- Entity ID
- SSO URL
- SLO URL (optional)
- X.509 Certificate

### 4. Verify Configuration

```bash
php artisan saml2:list-idps
```

Expected output:
```
+-------+-----------------------+-------------------+--------+
| Key   | Name                  | Entity ID         | Active |
+-------+-----------------------+-------------------+--------+
| azure | Azure Active Directory| https://sts...    | Yes    |
+-------+-----------------------+-------------------+--------+
```

### 5. Test IDP

```bash
php artisan saml2:test-idp azure
```

This command verifies:
- ✅ IDP exists in database
- ✅ Entity ID is configured
- ✅ SSO URL is accessible
- ✅ Certificate is valid

---

## Publish Login Listener

The listener is **essential** to handle what happens after a successful SAML authentication.

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

        // Authenticate user
        Auth::login($user, remember: true);
    }
}
```

### Customizing the Listener

You can extend the listener to:

**Sync roles from SAML:**

```php
public function handle(Saml2LoginEvent $event): void
{
    $email = $event->getEmail();
    $name = $event->getName();
    $groups = $event->getAttribute('groups') ?? [];

    $user = User::firstOrCreate(
        ['email' => $email],
        ['name' => $name ?? $email]
    );

    // Sync roles (spatie/laravel-permission)
    if (!empty($groups)) {
        $user->syncRoles($groups);
    }

    Auth::login($user, remember: true);
}
```

**Update user data on every login:**

```php
public function handle(Saml2LoginEvent $event): void
{
    $user = User::updateOrCreate(
        ['email' => $event->getEmail()],
        [
            'name' => $event->getName(),
            'first_name' => $event->getAttribute('firstname'),
            'last_name' => $event->getAttribute('lastname'),
            'last_login_at' => now(),
        ]
    );

    Auth::login($user, remember: true);
}
```

**Validate email domain:**

```php
public function handle(Saml2LoginEvent $event): void
{
    $email = $event->getEmail();
    
    // Validate domain
    if (!str_ends_with($email, '@your-company.com')) {
        throw new \Exception('Email domain not allowed');
    }

    $user = User::firstOrCreate(
        ['email' => $email],
        ['name' => $event->getName()]
    );

    Auth::login($user, remember: true);
}
```

---

## Integrate SAML with Your Routes

### Simple Login Link

```html
<a href="{{ route('saml2.login', ['idp' => 'azure']) }}">
    Login with Azure AD
</a>
```

### Multiple IDPs

```html
<h3>Choose your identity provider:</h3>
<ul>
    <li><a href="{{ route('saml2.login', ['idp' => 'azure']) }}">Azure AD</a></li>
    <li><a href="{{ route('saml2.login', ['idp' => 'okta']) }}">Okta</a></li>
    <li><a href="{{ route('saml2.login', ['idp' => 'adfs']) }}">Corporate ADFS</a></li>
</ul>
```

### Replacing Default Auth Routes

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
            return redirect()->route('saml2.login', ['idp' => 'azure']);
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

## Resetting Setup

If you need to re-run the setup wizard:

```bash
# Reset setup state only (keeps IDPs)
php artisan saml2:reset-setup

# Reset setup state AND delete all IDPs
php artisan saml2:reset-setup --with-idps
```

After this, the wizard will be available again at `/saml2/setup`.

---

## Complete Authentication Flow

```
┌─────────┐     ┌─────────────┐     ┌─────────┐
│  User   │     │  Your App   │     │   IDP   │
└────┬────┘     └──────┬──────┘     └────┬────┘
     │                 │                  │
     │ Click Login     │                  │
     │────────────────>│                  │
     │                 │                  │
     │                 │ SAML Request     │
     │                 │─────────────────>│
     │                 │                  │
     │                 │   Login Form     │
     │<───────────────────────────────────│
     │                 │                  │
     │ Credentials     │                  │
     │───────────────────────────────────>│
     │                 │                  │
     │                 │  SAML Response   │
     │                 │<─────────────────│
     │                 │                  │
     │                 │ Validate Res     │
     │                 │ Dispatch Event   │
     │                 │ Listener creates │
     │                 │ session          │
     │                 │                  │
     │    Dashboard    │                  │
     │<────────────────│                  │
     │                 │                  │
```

---

## Post-Setup Checklist

- [ ] SP Entity ID configured in `.env`
- [ ] IDP created and active
- [ ] Login listener published and customized
- [ ] Login/logout routes integrated into your app
- [ ] SP certificates generated (production)
- [ ] Admin middleware configured (optional)
- [ ] Attribute mapping configured (if needed)
- [ ] Tested complete login flow

---

## Next Step

Learn about the [Admin UI](UI.md) to manage IDPs after setup.
