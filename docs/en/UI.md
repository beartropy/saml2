# Admin Interface (UI)

The `beartropy/saml2` package includes a complete web admin panel to manage Identity Providers without the need for commands or direct database editing.

## Accessing the Panel

```
https://your-app.com/saml2/admin
```

> **Note**: The panel is protected by the middleware configured in `admin_middleware`. By default, it requires authentication (`['web', 'auth']`).

---

## Main Dashboard

The dashboard provides an overview of your SAML2 configuration.

### Service Provider Information

At the top, you'll see your SP data to share with IDP administrators:

| Field | Description |
|-------|-------------|
| **Entity ID** | Unique identifier for your application |
| **ACS URL** | Assertion Consumer Service URL |
| **Metadata URL** | Link to XML metadata (click to view) |

### Identity Provider List

A table with all configured IDPs showing:

| Column | Description |
|--------|-------------|
| **Key** | Unique IDP identifier (slug) |
| **Name** | Human-readable IDP name |
| **Entity ID** | IDP identifier |
| **Status** | Badge indicating Active/Inactive state |
| **Mapping** | Badge indicating Global or Custom mapping |
| **Actions** | Action buttons |

### Available Actions per IDP

| Action | Description |
|--------|-------------|
| **Edit** | Edit IDP configuration |
| **Mapping** | Configure attribute mapping |
| **Activate/Deactivate** | Toggle IDP status |
| **↻ (Refresh)** | Refresh metadata from URL (only if `metadata_url` exists) |
| **Delete** | Remove IDP (with confirmation) |

---

## Create New IDP

To add a new Identity Provider:

1. Click **"+ Add IDP"** on the dashboard.
2. The creation form will open.

### Import from URL

The fastest way to configure an IDP:

1. In the top section of the form, enter the **metadata URL**.
2. Click **"Fetch"**.
3. Fields will be filled automatically.

> **Note**: Metadata fetch is performed from the user's browser. If it fails due to CORS, try using the server as a proxy.

### Form Fields

| Field | Required | Description |
|-------|----------|-------------|
| **IDP Key** | ✅ | Unique identifier (slug). Letters, numbers, and dashes only. E.g., `azure-prod` |
| **IDP Name** | ✅ | Display name in UI. E.g., `Azure Active Directory (Production)` |
| **Entity ID** | ✅ | IDP Entity ID |
| **SSO URL** | ✅ | Single Sign-On URL |
| **SLO URL** | ❌ | Single Logout URL (optional, for federated logout) |
| **X.509 Certificate** | ✅ | IDP's public certificate (without `-----BEGIN...` headers) |
| **Metadata URL** | ❌ | URL for automatic metadata refreshing |
| **Active** | ❌ | Checkbox to enable/disable the IDP |

### Azure AD Configuration Example

```
IDP Key:      azure
IDP Name:     Azure Active Directory
Entity ID:    https://sts.windows.net/{tenant-id}/
SSO URL:      https://login.microsoftonline.com/{tenant-id}/saml2
SLO URL:      https://login.microsoftonline.com/{tenant-id}/saml2
Metadata URL: https://login.microsoftonline.com/{tenant-id}/federationmetadata/2007-06/federationmetadata.xml
```

### Okta Configuration Example

```
IDP Key:      okta
IDP Name:     Okta SSO
Entity ID:    http://www.okta.com/exk...
SSO URL:      https://your-org.okta.com/app/app-name/exk.../sso/saml
SLO URL:      https://your-org.okta.com/app/app-name/exk.../slo/saml
```

---

## Edit IDP

To modify an existing IDP:

1. Click **"Edit"** in the IDP list.
2. Modify the necessary fields.
3. Click **"Save Changes"**.

> **Note**: The **Key** field cannot be modified after creation. This is enforced server-side to prevent route/code breakage.

---

## Attribute Mapping

Attribute mapping allows normalizing SAML claims from different IDPs into consistent fields in your application.

### Accessing the Mapping Editor

1. Click **"Mapping"** in the IDP list.
2. The mapping editor for that IDP will open.

### Global vs Custom Mapping

| Type | Description |
|------|-------------|
| **Global** | Uses mapping defined in `config/beartropy-saml2.php` |
| **Custom** | IDP-specific mapping that overrides global settings |

### Use Global Mapping

1. Check the **"Use global mapping"** checkbox.
2. You'll see a read-only table with the current global mapping.
3. Save to apply.

### Configure Custom Mapping

1. Uncheck the **"Use global mapping"** checkbox.
2. The custom mapping editor will appear.
3. For each field:
   - **Local Field**: Field name in your application (e.g., `email`, `name`).
   - **SAML Attribute**: IDP's SAML attribute name (e.g., `http://schemas...`).

### Add/Remove Mappings

- Click **"+ Add Mapping"** to add a new row.
- Click **"×"** to remove an existing row.

### Azure AD Mapping Example

| Local Field | SAML Attribute |
|-------------|----------------|
| `email` | `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress` |
| `name` | `http://schemas.microsoft.com/identity/claims/displayname` |
| `firstname` | `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname` |
| `lastname` | `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname` |
| `groups` | `http://schemas.microsoft.com/ws/2008/06/identity/claims/groups` |

### Okta Mapping Example

| Local Field | SAML Attribute |
|-------------|----------------|
| `email` | `email` |
| `name` | `name` |
| `firstname` | `firstName` |
| `lastname` | `lastName` |

---

## Activate/Deactivate IDP

You can activate or deactivate an IDP without deleting it:

1. Click **"Activate"** or **"Deactivate"** in the IDP list.
2. The status will change immediately.

### States

| State | Badge | Behavior |
|-------|-------|----------|
| **Active** | 🟢 Green | IDP is available for login |
| **Inactive** | ⚪ Gray | IDP does not appear in login options |

> **Usage**: Deactivate an IDP temporarily during maintenance without losing its configuration.

---

## Refresh Metadata

If an IDP has a `metadata_url` configured, you can update its configuration automatically:

1. Click **"↻"** in the IDP list (only visible if metadata URL exists).
2. The system will download and parse the metadata.
3. Entity ID, SSO URL, SLO URL, and Certificate will be updated.

> **Caution**: This process will overwrite any manual changes made to these fields.

> **Security**: Since v0.3.0, server-side metadata fetching includes SSRF protection. URLs pointing to private/reserved IP ranges (10.x, 172.16.x, 192.168.x, 169.254.x, localhost) are blocked by default. If your IDP is on an internal network, set `SAML2_BLOCK_PRIVATE_URLS=false` in your `.env`.

---

## Delete IDP

To permanently remove an IDP:

1. Click **"Delete"** in the IDP list.
2. Confirm in the dialog that appears.
3. The IDP will be removed from the database.

> **Warning**: This action is irreversible. The IDP will stop working immediately.

---

## Admin Panel Customization

### Use Custom Layout

To integrate the panel with your application's layout:

```php
// config/beartropy-saml2.php
'layout' => 'layouts.admin',
```

Your layout must be a Blade component that accepts a `$slot`:

```blade
{{-- resources/views/components/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Admin</title>
</head>
<body>
    {{ $slot }}
</body>
</html>
```

### Publish Views

For full appearance customization:

```bash
php artisan vendor:publish --tag=beartropy-saml2-views
```

Views will be created in `resources/views/vendor/beartropy-saml2/`:

```
resources/views/vendor/beartropy-saml2/
├── setup.blade.php              # Initial configuration wizard
├── setup-success.blade.php      # Success page after setup
└── admin/
    ├── index.blade.php          # Dashboard
    ├── idp-form.blade.php       # Create/edit form
    ├── mapping.blade.php        # Mapping editor
    └── partials/
        └── layout.blade.php     # Base layout
```

> **Note**: Views use vanilla HTML/CSS without CSS framework dependencies, making it easy to integrate with any stack.

---

## Protecting the Admin Panel

### Custom Middleware

```php
// config/beartropy-saml2.php
'admin_middleware' => ['web', 'auth', 'can:manage-saml'],
```

### Defining a Gate

```php
// app/Providers/AuthServiceProvider.php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('manage-saml', function ($user) {
        return $user->hasRole('admin');
        // or: return $user->is_admin;
        // or: return in_array($user->email, ['admin@company.com']);
    });
}
```

### Using Spatie Permission

```php
'admin_middleware' => ['web', 'auth', 'role:admin'],
// or
'admin_middleware' => ['web', 'auth', 'permission:manage-saml'],
```

---

## Disabling the Panel

If you prefer managing IDPs via Artisan only:

```php
// config/beartropy-saml2.php
'admin_enabled' => false,
```

Or via `.env`:

```env
SAML2_ADMIN_ENABLED=false
```

---

## Admin Panel Routes

| Route | Method | Description |
|-------|--------|-------------|
| `/saml2/admin` | GET | Main dashboard |
| `/saml2/admin/idp/create` | GET | Create IDP form |
| `/saml2/admin/idp` | POST | Store new IDP |
| `/saml2/admin/idp/{id}` | GET | Edit IDP form |
| `/saml2/admin/idp/{id}` | PUT | Update IDP |
| `/saml2/admin/idp/{id}` | DELETE | Delete IDP |
| `/saml2/admin/idp/{id}/toggle` | POST | Activate/deactivate IDP |
| `/saml2/admin/idp/{id}/mapping` | GET | Mapping editor |
| `/saml2/admin/idp/{id}/mapping` | POST | Store mapping |
| `/saml2/admin/idp/{id}/refresh` | POST | Refresh metadata |

---

## Internationalization (i18n)

The panel is fully translated. To change the language:

### Option 1: Change Laravel Locale

```php
// config/app.php
'locale' => 'en',
```

### Option 2: Publish and Customize Translations

```bash
php artisan vendor:publish --tag=beartropy-saml2-lang
```

Edit files in `lang/vendor/beartropy-saml2/`:

- `en/saml2.php` - English
- `es/saml2.php` - Spanish

---

## Troubleshooting

### Panel does not load styles

Verify your layout includes styles correctly. The default layout embeds styles inline.

### "Unauthorized" Error

Verify that:
1. You are authenticated.
2. Your user has the permissions configured in `admin_middleware`.

### Refresh metadata button not visible

The button appears only if the IDP has a `metadata_url` configured.

### Changes are not saving

Verify that:
1. The form has the CSRF token (`@csrf`).
2. There are no validation errors.
3. You have write permissions on the database.

---

## Next Step

Check the [Configuration](CONFIG.md) document for advanced options or the main [README](../README.md) for a general overview of the package.
