# Setup Inicial (First Deploy)

Esta guía explica el proceso de configuración inicial del paquete `beartropy/saml2` usando el **Setup Wizard** o comandos de Artisan.

## Visión General

El setup inicial consiste en:

1. **Configurar el Service Provider (SP)**: Tu aplicación Laravel
2. **Configurar un Identity Provider (IDP)**: Azure AD, Okta, ADFS, etc.
3. **Publicar el listener de login**: Para manejar la autenticación
4. **Probar el flujo de login**

---

## Opción A: Setup Wizard (Recomendado)

El Setup Wizard es la forma más fácil de configurar SAML2. Está disponible solo **antes de configurar el primer IDP**.

### Acceder al Wizard

```
https://tu-app.com/saml2/setup
```

### Paso 1: Revisar Metadata del SP

El wizard muestra automáticamente la información de tu Service Provider que debes compartir con tu administrador de IDP:

| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| **Entity ID** | Identificador único de tu aplicación | `https://tu-app.com` |
| **ACS URL** | URL donde el IDP enviará las respuestas SAML | `https://tu-app.com/saml2/acs` |
| **Metadata URL** | URL del archivo XML de metadata del SP | `https://tu-app.com/saml2/metadata` |
| **Metadata XML** | Contenido XML completo para copiar | `<EntityDescriptor ...>` |

> **Acción Requerida**: Envía estos datos a tu administrador de IDP para que configure la aplicación en su lado.

### Paso 2: Configurar el IDP

El wizard ofrece tres métodos para configurar tu IDP:

#### Método 1: Desde URL (Recomendado)

1. Selecciona la pestaña **"Desde URL"**
2. Ingresa la URL del metadata del IDP
3. Haz clic en **"Obtener"**
4. El wizard parseará automáticamente el XML y llenará los campos

**URLs de Metadata Comunes:**

| Proveedor | URL de Metadata |
|-----------|-----------------|
| **Azure AD** | `https://login.microsoftonline.com/{tenant-id}/federationmetadata/2007-06/federationmetadata.xml` |
| **Okta** | `https://{tu-dominio}.okta.com/app/{app-id}/sso/saml/metadata` |
| **ADFS** | `https://{tu-servidor}/FederationMetadata/2007-06/FederationMetadata.xml` |
| **Google** | `https://accounts.google.com/gsiwebsdk/v3/downloadmetadata` |
| **Keycloak** | `https://{servidor}/realms/{realm}/protocol/saml/descriptor` |

> **Nota**: Si la descarga falla por CORS, el wizard ofrece usar el servidor como proxy.

#### Método 2: Pegar XML

1. Selecciona la pestaña **"Pegar XML"**
2. Copia y pega el contenido XML del metadata de tu IDP
3. Haz clic en **"Parsear"**
4. El wizard extraerá los valores automáticamente

#### Método 3: Entrada Manual

1. Selecciona la pestaña **"Manual"**
2. Completa los siguientes campos:

| Campo | Requerido | Descripción |
|-------|-----------|-------------|
| **IDP Key** | ✅ | Identificador único (slug) para este IDP. Ej: `azure`, `okta` |
| **IDP Name** | ✅ | Nombre legible. Ej: `Azure Active Directory` |
| **Entity ID** | ✅ | Identificador del IDP |
| **SSO URL** | ✅ | URL de Single Sign-On del IDP |
| **SLO URL** | ❌ | URL de Single Logout (opcional) |
| **X.509 Certificate** | ✅ | Certificado público del IDP |

### Paso 3: Guardar y Completar

1. Haz clic en **"Guardar y Completar Setup"**
2. Si todo es correcto, verás la **página de éxito** con:
   - Resumen del SP y IDP configurado
   - Rutas de login y logout
   - Próximos pasos recomendados

---

## Página de Éxito del Setup

Después de completar el wizard, verás una página con:

### Información del SP

- Entity ID configurado
- ACS URL para compartir con el IDP
- Metadata URL para importación automática

### IDP Configurado

- Key del IDP (ej: `azure`)
- Nombre del IDP
- Entity ID del IDP

### Rutas Disponibles

```
Login:  https://tu-app.com/saml2/login/azure
Logout: https://tu-app.com/saml2/logout/azure
```

### Próximos Pasos

1. **Publicar Listener**: `php artisan saml2:publish-listener`
2. **Revisar Configuración**: `config/beartropy-saml2.php`
3. **Probar Login**: Usar el botón "Probar Login"

---

## Opción B: Setup vía Comandos Artisan

Si prefieres la línea de comandos, puedes configurar todo sin usar el wizard.

### 1. Configurar el .env

```env
# Entity ID de tu aplicación
SAML2_SP_ENTITY_ID=https://tu-app.com

# Redirecciones
SAML2_LOGIN_REDIRECT=/dashboard
SAML2_LOGOUT_REDIRECT=/
```

### 2. Generar Certificados SP (Recomendado)

```bash
php artisan saml2:generate-cert
```

### 3. Crear el IDP

**Desde URL de metadata:**

```bash
php artisan saml2:create-idp azure --from-url=https://login.microsoftonline.com/{tenant}/federationmetadata.xml
```

**Modo interactivo:**

```bash
php artisan saml2:create-idp azure --interactive
```

El modo interactivo te pedirá:
- Nombre del IDP
- Entity ID
- SSO URL
- SLO URL (opcional)
- Certificado X.509

### 4. Verificar la Configuración

```bash
php artisan saml2:list-idps
```

Salida esperada:
```
+-------+-----------------------+-------------------+--------+
| Key   | Name                  | Entity ID         | Active |
+-------+-----------------------+-------------------+--------+
| azure | Azure Active Directory| https://sts...    | Yes    |
+-------+-----------------------+-------------------+--------+
```

### 5. Probar el IDP

```bash
php artisan saml2:test-idp azure
```

Este comando verifica:
- ✅ IDP existe en la base de datos
- ✅ Entity ID está configurado
- ✅ SSO URL es accesible
- ✅ Certificado es válido

---

## Publicar el Listener de Login

El listener es **esencial** para manejar qué sucede después de una autenticación SAML exitosa.

```bash
php artisan saml2:publish-listener
```

Esto crea `app/Listeners/HandleSaml2Login.php`:

```php
<?php

namespace App\Listeners;

use Beartropy\Saml2\Events\Saml2LoginEvent;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class HandleSaml2Login
{
    public function handle(Saml2LoginEvent $event): void
    {
        $email = $event->getEmail();
        $name = $event->getName();

        // Buscar o crear usuario
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name ?? $email]
        );

        // Autenticar al usuario
        Auth::login($user, remember: true);
    }
}
```

### Personalizar el Listener

Puedes extender el listener para:

**Sincronizar roles desde SAML:**

```php
public function handle(Saml2LoginEvent $event): void
{
    $email = $event->getEmail();
    $name = $event->getName();
    $groups = $event->getAttribute('groups') ?? [];

    $user = User::firstOrCreate(
        ['email' => $email],
        ['name' => $name ?? $email]
    );

    // Sincronizar roles (spatie/laravel-permission)
    if (!empty($groups)) {
        $user->syncRoles($groups);
    }

    Auth::login($user, remember: true);
}
```

**Actualizar datos del usuario en cada login:**

```php
public function handle(Saml2LoginEvent $event): void
{
    $user = User::updateOrCreate(
        ['email' => $event->getEmail()],
        [
            'name' => $event->getName(),
            'first_name' => $event->getAttribute('firstname'),
            'last_name' => $event->getAttribute('lastname'),
            'last_login_at' => now(),
        ]
    );

    Auth::login($user, remember: true);
}
```

**Validar dominio de email:**

```php
public function handle(Saml2LoginEvent $event): void
{
    $email = $event->getEmail();
    
    // Validar dominio
    if (!str_ends_with($email, '@tuempresa.com')) {
        throw new \Exception('Dominio de email no permitido');
    }

    $user = User::firstOrCreate(
        ['email' => $email],
        ['name' => $event->getName()]
    );

    Auth::login($user, remember: true);
}
```

---

## Integrar SAML con tus Rutas

### Link Simple de Login

```html
<a href="{{ route('saml2.login', ['idp' => 'azure']) }}">
    Login con Azure AD
</a>
```

### Múltiples IDPs

```html
<h3>Elige tu proveedor de identidad:</h3>
<ul>
    <li><a href="{{ route('saml2.login', ['idp' => 'azure']) }}">Azure AD</a></li>
    <li><a href="{{ route('saml2.login', ['idp' => 'okta']) }}">Okta</a></li>
    <li><a href="{{ route('saml2.login', ['idp' => 'adfs']) }}">ADFS Corporativo</a></li>
</ul>
```

### Reemplazar Rutas de Auth por Defecto

```php
// routes/auth.php

Route::middleware('guest')->group(function () {
    if (app()->environment('local')) {
        // Login local para desarrollo
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'authenticate']);
    } else {
        // SAML Login - redirige al IDP
        Route::get('/login', function () {
            return redirect()->route('saml2.login', ['idp' => 'azure']);
        })->name('login');
    }
});

Route::middleware('auth')->group(function () {
    if (app()->environment('local')) {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    } else {
        // SAML Logout (SLO)
        Route::match(['get', 'post'], '/logout', function () {
            return redirect()->route('saml2.logout');
        })->name('logout');
    }
});
```

---

## Resetear el Setup

Si necesitas volver a ejecutar el wizard de setup:

```bash
# Solo resetear el estado de setup (conserva IDPs)
php artisan saml2:reset-setup

# Resetear setup Y eliminar todos los IDPs
php artisan saml2:reset-setup --with-idps
```

Después de esto, el wizard estará disponible nuevamente en `/saml2/setup`.

---

## Flujo Completo de Autenticación

```
┌─────────┐     ┌─────────────┐     ┌─────────┐
│ Usuario │     │  Tu App     │     │   IDP   │
└────┬────┘     └──────┬──────┘     └────┬────┘
     │                 │                  │
     │ Click Login     │                  │
     │────────────────>│                  │
     │                 │                  │
     │                 │ SAML Request     │
     │                 │─────────────────>│
     │                 │                  │
     │                 │    Formulario    │
     │<───────────────────────────────────│
     │                 │                  │
     │ Credenciales    │                  │
     │───────────────────────────────────>│
     │                 │                  │
     │                 │  SAML Response   │
     │                 │<─────────────────│
     │                 │                  │
     │                 │ Valida Response  │
     │                 │ Dispara Evento   │
     │                 │ Listener crea    │
     │                 │ sesión           │
     │                 │                  │
     │    Dashboard    │                  │
     │<────────────────│                  │
     │                 │                  │
```

---

## Checklist Post-Setup

- [ ] Entity ID del SP configurado en `.env`
- [ ] IDP creado y activo
- [ ] Listener de login publicado y personalizado
- [ ] Rutas de login/logout integradas en tu app
- [ ] Certificados SP generados (producción)
- [ ] Middleware de admin configurado (opcional)
- [ ] Mapeo de atributos configurado (si es necesario)
- [ ] Probado el flujo completo de login

---

## Siguiente Paso

Aprende sobre la [Interfaz de Administración](UI.md) para gestionar los IDPs después del setup.
