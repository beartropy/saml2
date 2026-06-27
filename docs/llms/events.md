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
- `mapAttributes()` preserves all values when the IdP sends more than one; it collapses single-valued attributes to a scalar so `email`/`name` stay strings
- For role/group assignment, prefer `getAttributeAll('roles')` (always an array) → `$user->syncRoles($roles)`
- Do NOT use `getRawAttribute('roles')` for multi-role users — it returns only the first role. Use `getRawAttributeAll()`

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
