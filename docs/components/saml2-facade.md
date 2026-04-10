# Saml2 Facade

The main API for SAML2 authentication — initiate login/logout, process responses, and generate metadata.

## Basic Usage

```php
use Beartropy\Saml2\Facades\Saml2;

// Initiate SSO login
$redirectUrl = Saml2::login('azure');

// Process ACS response (in your listener)
$result = Saml2::processAcsResponse('azure');

// Generate SP metadata
$xml = Saml2::getMetadataXml();
```

## Methods

| Method | Return | Description |
|--------|--------|-------------|
| `login(string $idpKey, ?string $returnTo = null)` | `string` | Initiate SSO login, returns redirect URL |
| `logout(string $idpKey, ?string $returnTo, ?string $nameId, ?string $sessionIndex)` | `string` | Initiate SLO |
| `processAcsResponse(string $idpKey)` | `array` | Process ACS response for specific IDP |
| `processAcsResponseAuto()` | `array` | Auto-detect IDP from SAML response |
| `processSlo(string $idpKey, ?callable $cb, ?string $nameId, ?string $sessionIndex)` | `?string` | Process SLO response |
| `getMetadataXml()` | `string` | Generate SP metadata XML (cached) |
| `getAuth(Saml2Idp\|string $idp)` | `Auth` | Get configured onelogin Auth instance |
| `getIdpResolver()` | `IdpResolver` | Get the IDP resolver service |
| `getMetadataParser()` | `MetadataParser` | Get the metadata parser service |

## Authentication Flow

1. User visits `/saml2/login/{idp}` → redirected to IDP login page
2. IDP authenticates user → POSTs SAML response to `/saml2/acs/{idp}`
3. `Saml2LoginEvent` is dispatched with user attributes
4. Your listener authenticates/creates the Laravel user
5. User is redirected to `login_redirect` URL

## Event Listener Example

```php
use Beartropy\Saml2\Events\Saml2LoginEvent;

class HandleSaml2Login
{
    public function handle(Saml2LoginEvent $event): void
    {
        $email = $event->getEmail();
        $name = $event->getName();
        $attributes = $event->getAttributes();

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name]
        );

        Auth::login($user);
    }
}
```
