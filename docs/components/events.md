# Events

SAML2 events dispatched during authentication flow.

## Saml2LoginEvent

Dispatched after a successful SAML assertion is processed.

```php
use Beartropy\Saml2\Events\Saml2LoginEvent;

class HandleSaml2Login
{
    public function handle(Saml2LoginEvent $event): void
    {
        $email = $event->getEmail();
        $name = $event->getName();
        $idpKey = $event->idpKey;
        $attributes = $event->getAttributes();
    }
}
```

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `idpKey` | `string` | IDP identifier |
| `nameId` | `string` | SAML NameID |
| `attributes` | `array` | Mapped attributes |
| `rawAttributes` | `array` | Raw SAML attributes |
| `sessionIndex` | `string` | SAML session index |

### Methods

| Method | Return | Description |
|--------|--------|-------------|
| `getAttribute(string $key, $default)` | `mixed` | Get a mapped attribute. Returns an **array** when the attribute has 2+ values (e.g. roles), a scalar when single-valued |
| `getAttributeAll(string $key, array $default = [])` | `array` | Get a mapped attribute as an array of **all** its values (wraps a single scalar in one element) |
| `getRawAttribute(string $key, $default)` | `mixed` | Get a raw SAML attribute. Returns only the **first** value |
| `getRawAttributeAll(string $key, array $default = [])` | `array` | Get a raw SAML attribute as an array of **all** its values |
| `getAttributes()` | `array` | Get all mapped attributes |
| `getRawAttributes()` | `array` | Get all raw attributes |
| `getEmail()` | `?string` | Intelligent email extraction |
| `getName()` | `?string` | Intelligent name extraction |
| `toArray()` | `array` | All event data as array |

### Multiple roles / groups

SAML attributes can carry more than one value — a user often belongs to several roles or groups. Use `getAttributeAll()` (or `getRawAttributeAll()`) so you never lose any. They always return an array, even for a single value, so you can assign roles without branching:

```php
public function handle(Saml2LoginEvent $event): void
{
    $user = User::firstOrCreate(
        ['email' => $event->getEmail()],
        ['name' => $event->getName() ?? $event->getEmail()]
    );

    // Every role the IdP sent — not just the first one.
    $roles = $event->getAttributeAll('roles');

    if (! empty($roles)) {
        $user->syncRoles($roles); // spatie/laravel-permission
    }
}
```

> `getAttribute('roles')` returns an array when the user has 2+ roles and a scalar when they have one. Prefer `getAttributeAll('roles')` when you want a consistent array. `getRawAttribute()` still returns only the first value, for backward compatibility — use `getRawAttributeAll()` for the full list.

## Saml2LogoutEvent

Dispatched after a successful SLO response.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `idpKey` | `string` | IDP identifier |
| `nameId` | `string` | SAML NameID |
| `sessionIndex` | `string` | SAML session index |
