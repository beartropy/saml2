# Events — AI Reference

## Saml2LoginEvent

### Class
`Beartropy\Saml2\Events\Saml2LoginEvent`

### Constructor
```php
new Saml2LoginEvent(
    string $idpKey,
    string $nameId,
    array $attributes,
    array $rawAttributes,
    string $sessionIndex
)
```

### Intelligent Helpers
- `getEmail()` — tries mapped `email`, then raw attributes `email`, `mail`, `emailaddress`, `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress`
- `getName()` — tries mapped `name`, then raw `displayname`, `name`, `cn`, `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name`
- Both helpers handle array values (take first element)

### Attribute Resolution
- `getAttribute($key)` → looks in `$attributes` (mapped via config/IDP mapping). Returns an array when the attribute has 2+ values (e.g. roles/groups), a scalar when single-valued
- `getAttributeAll($key, array $default = [])` → mapped attribute as an array of ALL values; wraps a single scalar in `[$value]`
- `getRawAttribute($key)` → looks in `$rawAttributes` (raw SAML assertion values); returns only the FIRST value
- `getRawAttributeAll($key, array $default = [])` → raw attribute as an array of ALL values

### Multi-valued attributes (roles/groups)
SAML attributes always arrive as arrays. `mapAttributes()` (in `Saml2Service`) collapses a SINGLE-valued attribute to a scalar so `email`/`name` stay strings, and keeps an attribute with 2+ values as an array. `getAttribute()` returns whatever was stored — so its type depends on the count.

Return type per accessor for `roles`:

| Accessor | Source | 0 values | 1 value | 2+ values |
|----------|--------|----------|---------|-----------|
| `getAttribute('roles')` | mapped | `$default` | `'admin'` (scalar) | `['admin','editor']` |
| `getAttributeAll('roles')` | mapped | `[]` | `['admin']` | `['admin','editor']` |
| `getRawAttribute('roles')` | raw | `$default` | `'admin'` | `'admin'` (first only) |
| `getRawAttributeAll('roles')` | raw | `[]` | `['admin']` | `['admin','editor']` |

Rules:
- `getAttributeAll()` / `getRawAttributeAll()` ALWAYS return an array (`[]`/`[v]`/`[v,...]`). Use them when you iterate/count/sync, so the single-value case is not a bare string.
- `getAttribute()` is scalar for one value, array for many — safe for `syncRoles()` (spatie accepts string or array), unsafe to `foreach`.
- `getRawAttribute()` returns ONLY the first value — never use it for multi-valued claims.
- Mapped accessors read `$attributes` keyed by the LOCAL mapping key (may map to a differently-named SAML claim). Raw accessors read `$rawAttributes` keyed by the actual claim name. The untouched raw array is `getRawAttributeAll()` / `getRawAttributes()[$key]`.
- For role/group assignment, prefer: `$user->syncRoles($event->getAttributeAll('roles'))`.

## Saml2LogoutEvent

### Class
`Beartropy\Saml2\Events\Saml2LogoutEvent`

### Constructor
```php
new Saml2LogoutEvent(
    string $idpKey,
    string $nameId,
    string $sessionIndex
)
```

## Common Pitfalls
- SAML attributes are often arrays even for single values — helpers handle this
- Raw attributes use IDP-specific OID or claim URIs — use mapped attributes when possible
- Events are dispatched synchronously — long operations in listeners will block the response
- A user with 2+ roles: `getRawAttribute('roles')` and (for a single role) `getAttribute('roles')` give only one value. Use `getAttributeAll('roles')` / `getRawAttributeAll('roles')` to get them all before `syncRoles()`
