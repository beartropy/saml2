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

### Multi-valued attributes (roles / groups)

SAML attributes always arrive as arrays, and a user often belongs to **several** roles or groups. How you read them decides whether you get all of the values or just one, so pick the right accessor.

#### Mapped vs. raw

- **Mapped** accessors (`getAttribute*`) read `$attributes` — the values after your `attribute_mapping` is applied, keyed by your **local** key (e.g. `roles`). The local key may point at a differently-named SAML claim (e.g. `roles` → `http://schemas.microsoft.com/ws/2008/06/identity/claims/groups`).
- **Raw** accessors (`getRawAttribute*`) read `$rawAttributes` — the **untouched** SAML claim values, keyed by the claim name exactly as the IdP sent it.

#### Single-value collapse

When mapping is built, a **single-valued** attribute is collapsed to a scalar (so `email`, `name`, etc. stay plain strings); an attribute with **two or more** values is kept as an array. `getAttribute()` simply returns whatever was stored, so its type depends on how many values came in:

| Accessor | Source | 0 values (missing/empty) | 1 value | 2+ values |
|----------|--------|--------------------------|---------|-----------|
| `getAttribute('roles')` | mapped | `$default` | `'admin'` *(scalar)* | `['admin', 'editor']` |
| `getAttributeAll('roles')` | mapped | `[]` *(or `$default`)* | `['admin']` | `['admin', 'editor']` |
| `getRawAttribute('roles')` | raw | `$default` | `'admin'` | `'admin'` *(first only!)* |
| `getRawAttributeAll('roles')` | raw | `[]` *(or `$default`)* | `['admin']` | `['admin', 'editor']` |

Key points:

- **`getAttributeAll()` / `getRawAttributeAll()` always return an array** — `[]`, `['one']`, or `['many', '...']` — never a scalar. Use them whenever you will iterate, count, or sync, so the **single-role** case (`'admin'`) doesn't surprise you as a bare string.
- **`getAttribute()`** returns a scalar for one value and an array for many. It's fine for `syncRoles()` (spatie accepts a string *or* an array) but it's a footgun if you `foreach`/`map` over it.
- **`getRawAttribute()`** returns only the **first** value, even when several were sent. Never use it for multi-valued claims — reach for `getRawAttributeAll()` (or `getRawAttributes()[$key]`) instead.
- `getAttributeAll()` reconstructs the full value list losslessly (order preserved), so for a mapped key it matches what the IdP sent. The truly *untouched* raw array is `getRawAttributeAll()` / `getRawAttributes()[$key]`.

#### Recommended pattern

`getAttributeAll()` always returns an array, so you can assign roles without branching on the count:

```php
public function handle(Saml2LoginEvent $event): void
{
    $user = User::firstOrCreate(
        ['email' => $event->getEmail()],
        ['name' => $event->getName() ?? $event->getEmail()]
    );

    // Every role the IdP sent — works for 0, 1, or many.
    $roles = $event->getAttributeAll('roles');

    if (! empty($roles)) {
        $user->syncRoles($roles); // spatie/laravel-permission
    }
}
```

## Saml2LogoutEvent

Dispatched after a successful SLO response.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `idpKey` | `string` | IDP identifier |
| `nameId` | `string` | SAML NameID |
| `sessionIndex` | `string` | SAML session index |
