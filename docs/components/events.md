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
| `getAttribute(string $key, $default)` | `mixed` | Get a mapped attribute |
| `getRawAttribute(string $key, $default)` | `mixed` | Get a raw SAML attribute |
| `getAttributes()` | `array` | Get all mapped attributes |
| `getRawAttributes()` | `array` | Get all raw attributes |
| `getEmail()` | `?string` | Intelligent email extraction |
| `getName()` | `?string` | Intelligent name extraction |
| `toArray()` | `array` | All event data as array |

## Saml2LogoutEvent

Dispatched after a successful SLO response.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `idpKey` | `string` | IDP identifier |
| `nameId` | `string` | SAML NameID |
| `sessionIndex` | `string` | SAML session index |
