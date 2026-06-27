# SAML2 Examples - Beartropy SAML2

## Login Event Listener

```php
<?php

namespace App\Listeners;

use App\Models\User;
use Beartropy\Saml2\Events\Saml2LoginEvent;
use Illuminate\Support\Facades\Auth;

class HandleSaml2Login
{
    public function handle(Saml2LoginEvent $event): void
    {
        $email = $event->getEmail();
        $name = $event->getName();

        if (!$email) {
            abort(403, 'No email in SAML response');
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name ?? explode('@', $email)[0],
                'password' => bcrypt(str()->random(32)),
            ]
        );

        // Sync roles from SAML attributes.
        // getAttributeAll() always returns an array, so users with 2+ roles
        // keep all of them (getAttribute() would return a scalar for one role).
        $roles = $event->getAttributeAll('roles');
        if (!empty($roles)) {
            $user->syncRoles($roles);
        }

        Auth::login($user);
    }
}
```

## Register the Listener

```php
// app/Providers/EventServiceProvider.php or bootstrap/app.php
use App\Listeners\HandleSaml2Login;
use Beartropy\Saml2\Events\Saml2LoginEvent;

protected $listen = [
    Saml2LoginEvent::class => [
        HandleSaml2Login::class,
    ],
];
```

## CSRF Exception

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'saml2/*',
    ]);
})
```

## Custom Login Button

```blade
<a href="{{ route('saml2.login', ['idp' => 'azure']) }}"
   class="btn btn-primary">
    Sign in with Azure AD
</a>
```

## Multiple IDP Login Page

```blade
@php
    $resolver = \Beartropy\Saml2\Facades\Saml2::getIdpResolver();
    $idps = $resolver->all();
@endphp

<div class="space-y-2">
    @foreach($idps as $idp)
        <a href="{{ $idp->getLoginUrl() }}" class="block p-4 border rounded hover:bg-gray-50">
            {{ $idp->name }}
        </a>
    @endforeach
</div>
```

## CLI Setup

```bash
# Generate SP certificates
php artisan saml2:generate-cert

# Create IDP from metadata URL
php artisan saml2:create-idp --url=https://login.microsoftonline.com/{tenant}/federationmetadata/2007-06/federationmetadata.xml

# Scaffold listener
php artisan saml2:publish-listener

# Verify setup
php artisan saml2:test-idp azure
php artisan saml2:list-idps
```
