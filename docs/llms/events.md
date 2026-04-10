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
- `getAttribute($key)` → looks in `$attributes` (mapped via config/IDP mapping)
- `getRawAttribute($key)` → looks in `$rawAttributes` (raw SAML assertion values)

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
