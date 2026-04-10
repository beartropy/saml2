---
name: bt-saml2-setup
description: Help users install and configure Beartropy SAML2 SSO in their Laravel projects
version: 1.0.0
author: Beartropy
tags: [beartropy, saml2, sso, installation, setup, configuration, azure, okta]
---

# Beartropy SAML2 Setup Guide

You are an expert in helping users install and configure Beartropy SAML2 for Single Sign-On in their Laravel applications.

---

## Requirements

- PHP >= 8.2
- Laravel >= 11.x
- onelogin/php-saml (installed automatically)
- OpenSSL PHP extension (for certificate generation)

---

## Installation

### Step 1: Install via Composer

```bash
composer require beartropy/saml2
```

### Step 2: Publish Config and Migrations

```bash
php artisan vendor:publish --provider="Beartropy\Saml2\Saml2ServiceProvider"
php artisan migrate
```

### Step 3: Generate SP Certificates

```bash
php artisan saml2:generate-cert
```

Add the output to your `.env` file.

### Step 4: Configure Environment

```env
SAML2_SP_ENTITY_ID=https://your-app.com
SAML2_SP_CERT="-----BEGIN CERTIFICATE-----..."
SAML2_SP_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----..."
SAML2_IDP_SOURCE=database
SAML2_LOGIN_REDIRECT=/dashboard
```

### Step 5: Exclude SAML Routes from CSRF

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: ['saml2/*']);
})
```

### Step 6: Create Your First IDP

Use the setup wizard at `/saml2/setup` or the CLI:

```bash
php artisan saml2:create-idp
```

### Step 7: Scaffold Login Listener

```bash
php artisan saml2:publish-listener
```

This creates `app/Listeners/HandleSaml2Login.php` where you handle user authentication.

---

## Authentication Flow

1. User visits `/saml2/login/{idp}` → redirected to IDP
2. IDP authenticates → POSTs SAML response to `/saml2/acs/{idp}`
3. `Saml2LoginEvent` dispatched with user attributes
4. Your listener authenticates/creates the Laravel user
5. User redirected to `login_redirect`

---

## Key Routes

| Route | Description |
|---|---|
| `GET /saml2/login/{idp?}` | Initiate SSO login |
| `POST /saml2/acs/{idp}` | Assertion Consumer Service |
| `GET /saml2/metadata` | SP Metadata XML |
| `GET /saml2/logout/{idp?}` | Initiate logout |
| `GET /saml2/setup` | First-deploy setup wizard |
| `GET /saml2/admin` | Admin panel |

---

## Artisan Commands

| Command | Description |
|---|---|
| `saml2:create-idp` | Create IDP (interactive or `--url=metadata_url`) |
| `saml2:list-idps` | List configured IDPs |
| `saml2:test-idp` | Test IDP connectivity |
| `saml2:generate-cert` | Generate SP certificates |
| `saml2:publish-listener` | Scaffold login listener |
| `saml2:refresh-metadata` | Refresh all IDP metadata |
| `saml2:delete-idp` | Delete an IDP |
| `saml2:reset-setup` | Reset setup wizard |

---

## IDP Sources

| Source | Description |
|---|---|
| `env` | Single IDP from `.env` variables |
| `database` | Multiple IDPs from database (recommended) |
| `both` | Try env first, fall back to database |

---

## Troubleshooting

### "Invalid SAML response"
- Ensure `wantMessagesSigned` and `wantAssertionsSigned` match your IDP's signing config
- Check that the IDP certificate is correct and not expired

### Login redirects back to IDP
- Ensure your listener calls `Auth::login($user)` in the `Saml2LoginEvent` handler
- Check that `saml2/*` routes are excluded from CSRF protection

### Metadata not accessible
- SP metadata is available at `/saml2/metadata`
- Share this URL with your IDP administrator
