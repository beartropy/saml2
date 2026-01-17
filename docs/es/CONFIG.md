# Archivo de Configuración

Este documento describe todas las opciones disponibles en el archivo de configuración `config/beartropy-saml2.php`.

## Publicar la Configuración

Para publicar el archivo de configuración en tu aplicación Laravel:

```bash
php artisan vendor:publish --tag=beartropy-saml2-config
```

Esto creará el archivo `config/beartropy-saml2.php` en tu proyecto.

---

## Configuración del Service Provider (SP)

El Service Provider es tu aplicación Laravel. Esta sección define cómo tu aplicación se identifica ante los Identity Providers.

```php
'sp' => [
    // Identificador único para tu SP (normalmente la URL de tu aplicación)
    'entityId' => env('SAML2_SP_ENTITY_ID'),
    
    // Certificado x509 y clave privada del SP
    'x509cert' => env('SAML2_SP_CERT'),
    'privateKey' => env('SAML2_SP_PRIVATE_KEY'),
    
    // URLs personalizadas (null = auto-generadas basadas en las rutas)
    'acs_url' => env('SAML2_SP_ACS_URL'),
    'sls_url' => env('SAML2_SP_SLS_URL'),
    'metadata_url' => env('SAML2_SP_METADATA_URL'),
    
    // Formato NameID
    'nameIdFormat' => env('SAML2_SP_NAMEID_FORMAT', 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress'),
],
```

### Opciones Detalladas

| Opción | Variable de Entorno | Descripción | Ejemplo |
|--------|---------------------|-------------|---------|
| `entityId` | `SAML2_SP_ENTITY_ID` | Identificador único de tu aplicación. Los IDPs usarán este valor para identificar las solicitudes. | `https://tu-app.com` |
| `x509cert` | `SAML2_SP_CERT` | Certificado público X.509 del SP para firmar solicitudes. | Contenido del certificado |
| `privateKey` | `SAML2_SP_PRIVATE_KEY` | Clave privada correspondiente al certificado. | Contenido de la clave |
| `acs_url` | `SAML2_SP_ACS_URL` | URL de Assertion Consumer Service. Si es `null`, se genera automáticamente. | `https://tu-app.com/saml2/acs` |
| `sls_url` | `SAML2_SP_SLS_URL` | URL de Single Logout Service. Si es `null`, se genera automáticamente. | `https://tu-app.com/saml2/sls/{idp}` |
| `metadata_url` | `SAML2_SP_METADATA_URL` | URL donde se expone el metadata XML del SP. | `https://tu-app.com/saml2/metadata` |
| `nameIdFormat` | `SAML2_SP_NAMEID_FORMAT` | Formato del NameID esperado en las respuestas SAML. | Ver formatos abajo |

### Formatos de NameID Disponibles

- `urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress` (por defecto, recomendado)
- `urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified`
- `urn:oasis:names:tc:SAML:2.0:nameid-format:persistent`
- `urn:oasis:names:tc:SAML:2.0:nameid-format:transient`

---

## Origen de los IDPs

Esta configuración define dónde se cargan las configuraciones de Identity Providers.

```php
'idp_source' => env('SAML2_IDP_SOURCE', 'database'),
```

### Opciones Disponibles

| Valor | Descripción | Uso Recomendado |
|-------|-------------|-----------------|
| `env` | Solo desde variables de entorno (IDP único) | Ambientes simples con un solo IDP |
| `database` | Solo desde base de datos (múltiples IDPs) | La mayoría de los casos de producción |
| `both` | Primero verifica env, luego base de datos | Desarrollo local con IDP de prueba |

---

## IDP por Defecto (vía entorno)

Cuando usas `env` o `both` como `idp_source`, configura el IDP predeterminado aquí.

```php
'default_idp' => [
    'key' => env('SAML2_IDP_KEY', 'default'),
    'name' => env('SAML2_IDP_NAME', 'Default IDP'),
    'entityId' => env('SAML2_IDP_ENTITY_ID'),
    'ssoUrl' => env('SAML2_IDP_SSO_URL'),
    'sloUrl' => env('SAML2_IDP_SLO_URL'),
    'x509cert' => env('SAML2_IDP_CERT'),
],
```

### Variables de Entorno para IDP

```env
SAML2_IDP_SOURCE=env
SAML2_IDP_KEY=azure
SAML2_IDP_NAME="Azure Active Directory"
SAML2_IDP_ENTITY_ID=https://sts.windows.net/{tenant-id}/
SAML2_IDP_SSO_URL=https://login.microsoftonline.com/{tenant-id}/saml2
SAML2_IDP_SLO_URL=https://login.microsoftonline.com/{tenant-id}/saml2
SAML2_IDP_CERT="MIICpDCCAYwCCQ..."
```

---

## Configuración de Rutas

```php
'route_prefix' => env('SAML2_ROUTE_PREFIX', 'saml2'),
'route_middleware' => ['web'],
```

### Opciones

| Opción | Variable de Entorno | Descripción | Por Defecto |
|--------|---------------------|-------------|-------------|
| `route_prefix` | `SAML2_ROUTE_PREFIX` | Prefijo para todas las rutas SAML2 | `saml2` |
| `route_middleware` | - | Middleware aplicado a las rutas SAML2 | `['web']` |

### Rutas Resultantes

Con el prefijo por defecto `saml2`:

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/saml2/setup` | GET | Wizard de configuración inicial |
| `/saml2/login/{idp?}` | GET | Iniciar login SSO |
| `/saml2/acs` | POST | ACS genérico (auto-detecta IDP) |
| `/saml2/acs/{idp}` | POST | ACS con IDP explícito |
| `/saml2/sls/{idp}` | GET/POST | Single Logout Service |
| `/saml2/metadata` | GET | Metadata XML del SP |
| `/saml2/logout/{idp?}` | GET | Iniciar logout |

---

## Panel de Administración

```php
'admin_enabled' => env('SAML2_ADMIN_ENABLED', true),
'admin_route_prefix' => env('SAML2_ADMIN_PREFIX', 'saml2/admin'),
'admin_middleware' => ['web', 'auth'],
'layout' => env('SAML2_ADMIN_LAYOUT', 'beartropy-saml2::admin.partials.layout'),
```

### Opciones del Panel Admin

| Opción | Variable de Entorno | Descripción | Por Defecto |
|--------|---------------------|-------------|-------------|
| `admin_enabled` | `SAML2_ADMIN_ENABLED` | Habilitar/deshabilitar el panel de admin | `true` |
| `admin_route_prefix` | `SAML2_ADMIN_PREFIX` | Prefijo para rutas de administración | `saml2/admin` |
| `admin_middleware` | - | Middleware para proteger rutas admin | `['web', 'auth']` |
| `layout` | `SAML2_ADMIN_LAYOUT` | Layout blade usado para el panel | Layout interno del paquete |

### Proteger el Panel de Administración

Para restringir el acceso solo a administradores:

```php
// config/beartropy-saml2.php
'admin_middleware' => ['web', 'auth', 'can:manage-saml'],
```

Define el Gate en tu `AuthServiceProvider`:

```php
// app/Providers/AuthServiceProvider.php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('manage-saml', function ($user) {
        return $user->hasRole('admin'); // o tu lógica de permisos
    });
}
```

### Usar Layout Personalizado

Para integrar el panel admin con el layout de tu aplicación:

```php
// config/beartropy-saml2.php
'layout' => 'layouts.admin', // Tu layout personalizado
```

Tu layout debe incluir:
- `@yield('title')` para el título de la página
- `@yield('content')` para el contenido principal
- `@yield('scripts')` para scripts adicionales

---

## URLs de Redirección

```php
'login_redirect' => env('SAML2_LOGIN_REDIRECT', '/'),
'logout_redirect' => env('SAML2_LOGOUT_REDIRECT', '/'),
'error_redirect' => env('SAML2_ERROR_REDIRECT', '/login'),
```

| Opción | Variable de Entorno | Descripción | Por Defecto |
|--------|---------------------|-------------|-------------|
| `login_redirect` | `SAML2_LOGIN_REDIRECT` | URL de redirección después del login exitoso | `/` |
| `logout_redirect` | `SAML2_LOGOUT_REDIRECT` | URL de redirección después del logout | `/` |
| `error_redirect` | `SAML2_ERROR_REDIRECT` | URL de redirección en caso de error SAML | `/login` |

---

## Modelo de Usuario

```php
'user_model' => \App\Models\User::class,
```

Especifica el modelo de usuario de tu aplicación. Usado internamente para lookups de autenticación.

---

## Importación de Metadata

```php
'allow_metadata_import' => env('SAML2_ALLOW_METADATA_IMPORT', true),
```

| Opción | Variable de Entorno | Descripción | Por Defecto |
|--------|---------------------|-------------|-------------|
| `allow_metadata_import` | `SAML2_ALLOW_METADATA_IMPORT` | Permitir importar metadata de IDPs desde URLs | `true` |

> **Nota de Seguridad**: En ambientes de alta seguridad, considera deshabilitar esta opción y configurar los IDPs manualmente.

---

## Mapeo de Atributos

Define cómo los atributos SAML se mapean a campos de usuario en tu aplicación.

```php
'attribute_mapping' => [
    'email' => 'email',
    'name' => 'displayname',
    'firstname' => 'firstname',
    'lastname' => 'lastname',
    'username' => 'username',
    'roles' => 'roles',
    'groups' => 'groups',
],
```

### Mapeo Global vs Por-IDP

Este mapeo es **global** y se aplica a todos los IDPs. Sin embargo, cada IDP puede tener su propio mapeo personalizado que sobreescribe el global.

### Ejemplos de Mapeo para Proveedores Comunes

**Azure AD:**
```php
'attribute_mapping' => [
    'email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
    'name' => 'http://schemas.microsoft.com/identity/claims/displayname',
    'firstname' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname',
    'lastname' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname',
    'roles' => 'http://schemas.microsoft.com/ws/2008/06/identity/claims/groups',
],
```

**Okta:**
```php
'attribute_mapping' => [
    'email' => 'email',
    'name' => 'name',
    'firstname' => 'firstName',
    'lastname' => 'lastName',
],
```

**ADFS:**
```php
'attribute_mapping' => [
    'email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
    'name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name',
    'groups' => 'http://schemas.xmlsoap.org/claims/Group',
],
```

---

## Configuración de Seguridad

Configuración avanzada para comunicación SAML segura.

```php
'security' => [
    'nameIdEncrypted' => false,
    'authnRequestsSigned' => false,
    'logoutRequestSigned' => false,
    'logoutResponseSigned' => false,
    'signMetadata' => false,
    'wantMessagesSigned' => false,
    'wantAssertionsSigned' => false,
    'wantAssertionsEncrypted' => false,
    'wantNameIdEncrypted' => false,
    'requestedAuthnContext' => true,
    'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
    'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',
],
```

### Opciones de Seguridad

| Opción | Descripción | Recomendación Producción |
|--------|-------------|--------------------------|
| `nameIdEncrypted` | Encriptar el NameID en las solicitudes | `false` (raramente necesario) |
| `authnRequestsSigned` | Firmar solicitudes de autenticación | `true` |
| `logoutRequestSigned` | Firmar solicitudes de logout | `true` |
| `logoutResponseSigned` | Firmar respuestas de logout | `true` |
| `signMetadata` | Firmar el metadata XML del SP | `true` |
| `wantMessagesSigned` | Requerir mensajes firmados del IDP | `true` |
| `wantAssertionsSigned` | Requerir assertions firmadas | `true` |
| `wantAssertionsEncrypted` | Requerir assertions encriptadas | `false` (según IDP) |
| `wantNameIdEncrypted` | Requerir NameID encriptado | `false` |
| `requestedAuthnContext` | Solicitar contexto de autenticación | `true` |
| `signatureAlgorithm` | Algoritmo de firma | RSA-SHA256 |
| `digestAlgorithm` | Algoritmo de digest | SHA256 |

### Configuración Recomendada para Producción

```php
'security' => [
    'authnRequestsSigned' => true,
    'logoutRequestSigned' => true,
    'logoutResponseSigned' => true,
    'signMetadata' => true,
    'wantMessagesSigned' => true,
    'wantAssertionsSigned' => true,
    // ... resto como valores por defecto
],
```

> **Importante**: Para usar firmas, primero debes generar certificados SP con:
> ```bash
> php artisan saml2:generate-cert
> ```

---

## Modo Debug

```php
'debug' => env('SAML2_DEBUG', false),
'strict' => env('SAML2_STRICT', true),
```

| Opción | Variable de Entorno | Descripción | Por Defecto |
|--------|---------------------|-------------|-------------|
| `debug` | `SAML2_DEBUG` | Activar modo debug (logs detallados) | `false` |
| `strict` | `SAML2_STRICT` | Modo estricto (valida firmas/schemas) | `true` |

> **Advertencia**: Nunca habilites `debug` en producción. Puede exponer información sensible.

---

## Ejemplo Completo de .env

```env
# Service Provider
SAML2_SP_ENTITY_ID=https://tu-app.com

# Fuente de IDPs
SAML2_IDP_SOURCE=database

# Rutas
SAML2_ROUTE_PREFIX=saml2
SAML2_ADMIN_PREFIX=saml2/admin
SAML2_ADMIN_ENABLED=true

# Redirecciones
SAML2_LOGIN_REDIRECT=/dashboard
SAML2_LOGOUT_REDIRECT=/
SAML2_ERROR_REDIRECT=/login

# Seguridad
SAML2_DEBUG=false
SAML2_STRICT=true

# Importación de metadata
SAML2_ALLOW_METADATA_IMPORT=true
```

---

## Siguiente Paso

Después de configurar el archivo, procede con la [Instalación](INSTALL.md) y luego el [Setup Inicial](SETUP.md).
