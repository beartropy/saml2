# Guía de Instalación

Esta guía cubre el proceso completo de instalación del paquete `beartropy/saml2` en tu aplicación Laravel.

## Requisitos Previos

- **PHP** 8.1 o superior
- **Laravel** 10.x, 11.x o 12.x
- Extensión **openssl** de PHP
- Extensión **dom** de PHP

---

## Instalación vía Composer

```bash
composer require beartropy/saml2
```

---

## Publicación de Assets

El paquete incluye varios assets que puedes publicar según tus necesidades.

### 1. Publicar Configuración (Requerido)

```bash
php artisan vendor:publish --tag=beartropy-saml2-config
```

Esto crea el archivo `config/beartropy-saml2.php` donde puedes personalizar todas las opciones del paquete.

> **Importante**: Este paso es requerido para poder configurar tu Service Provider (SP).

---

### 2. Ejecutar Migraciones (Requerido)

Las migraciones crean las tablas necesarias para almacenar los IDPs y configuración.

```bash
php artisan migrate
```

#### Tablas Creadas

| Tabla | Descripción |
|-------|-------------|
| `beartropy_saml2_idps` | Almacena configuración de Identity Providers |
| `beartropy_saml2_settings` | Almacena configuración general del paquete |

#### Estructura de `beartropy_saml2_idps`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigint | ID autoincremental |
| `key` | string | Identificador único del IDP (slug) |
| `name` | string | Nombre legible del IDP |
| `entity_id` | string | Entity ID del IDP |
| `sso_url` | string | URL de Single Sign-On |
| `slo_url` | string (nullable) | URL de Single Logout |
| `x509_cert` | text | Certificado X.509 del IDP |
| `x509_cert_multi` | json (nullable) | Múltiples certificados |
| `metadata_url` | string (nullable) | URL del metadata para refrescar |
| `metadata` | json (nullable) | Datos adicionales de configuración |
| `attribute_mapping` | json (nullable) | Mapeo de atributos personalizado |
| `is_active` | boolean | Estado activo/inactivo del IDP |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Estructura de `beartropy_saml2_settings`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigint | ID autoincremental |
| `key` | string | Clave de configuración |
| `value` | text (nullable) | Valor de configuración |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

---

### 3. Publicar Vistas (Opcional)

Si deseas personalizar la apariencia del wizard de setup o panel de administración:

```bash
php artisan vendor:publish --tag=beartropy-saml2-views
```

Esto crea los archivos en `resources/views/vendor/beartropy-saml2/`.

#### Estructura de Vistas

```
resources/views/vendor/beartropy-saml2/
├── setup.blade.php              # Wizard de configuración inicial
├── setup-success.blade.php      # Página de éxito post-setup
└── admin/
    ├── index.blade.php          # Dashboard del panel admin
    ├── idp-form.blade.php       # Formulario crear/editar IDP
    ├── mapping.blade.php        # Editor de mapeo de atributos
    └── partials/
        └── layout.blade.php     # Layout base del panel admin
```

> **Nota**: Las vistas usan HTML/CSS vanilla sin dependencias de Tailwind o Livewire, funcionando con cualquier stack Laravel.

---

### 4. Publicar Traducciones (Opcional)

Para personalizar los textos de la interfaz:

```bash
php artisan vendor:publish --tag=beartropy-saml2-lang
```

Esto crea los archivos en `lang/vendor/beartropy-saml2/`.

#### Idiomas Incluidos

- **Inglés** (`en`)
- **Español** (`es`)

#### Estructura de Traducciones

```
lang/vendor/beartropy-saml2/
├── en/
│   └── saml2.php
└── es/
    └── saml2.php
```

---

### 5. Publicar Listener de Login (Recomendado)

Este comando crea un listener base que puedes personalizar para manejar el login SAML:

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

        // Opcional: actualizar datos del usuario
        // $user->update(['name' => $name]);

        // Opcional: asignar roles (si usas spatie/laravel-permission)
        // $roles = $event->getRawAttribute('http://schemas.xmlsoap.org/claims/Group');
        // if ($roles) {
        //     $user->syncRoles($roles);
        // }

        // Autenticar al usuario
        Auth::login($user, remember: true);
    }
}
```

> **Nota**: En Laravel 11/12, los eventos se auto-descubren automáticamente. El listener se registrará sin configuración adicional.

---

## Publicar Todo de Una Vez

Para publicar todos los assets con un solo comando:

```bash
# Configuración
php artisan vendor:publish --tag=beartropy-saml2-config

# Vistas
php artisan vendor:publish --tag=beartropy-saml2-views

# Traducciones
php artisan vendor:publish --tag=beartropy-saml2-lang

# Migraciones
php artisan migrate
```

O usa el provider directamente:

```bash
php artisan vendor:publish --provider="Beartropy\Saml2\Saml2ServiceProvider"
```

---

## Generar Certificados SP (Recomendado)

Para entornos de producción, genera certificados para firmar las solicitudes SAML:

```bash
php artisan saml2:generate-cert
```

Este comando:
1. Genera un par de clave pública/privada RSA
2. Crea un certificado X.509 auto-firmado
3. Guarda los valores en tu archivo `.env`

### Variables de Entorno Generadas

```env
SAML2_SP_CERT="-----BEGIN CERTIFICATE-----
MIIDqTCCApGg...
-----END CERTIFICATE-----"

SAML2_SP_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBg...
-----END PRIVATE KEY-----"
```

> **Seguridad**: Nunca compartas la clave privada. Asegúrate de que `.env` esté en `.gitignore`.

---

## Configuración Mínima del .env

Después de la instalación, agrega estas variables a tu `.env`:

```env
# Identificador de tu aplicación (requerido)
SAML2_SP_ENTITY_ID=https://tu-app.com

# Opcional pero recomendado
SAML2_LOGIN_REDIRECT=/dashboard
SAML2_LOGOUT_REDIRECT=/
```

---

## Verificar la Instalación

### 1. Verifica que las rutas estén registradas:

```bash
php artisan route:list --name=saml2
```

Deberías ver rutas como:
- `saml2.login`
- `saml2.acs`
- `saml2.metadata`
- `saml2.logout`
- `saml2.setup`
- `saml2.admin.*`

### 2. Verifica que las migraciones se ejecutaron:

```bash
php artisan migrate:status
```

Busca:
- `create_beartropy_saml2_idps_table`
- `create_beartropy_saml2_settings_table`

### 3. Verifica el acceso al Setup Wizard:

Abre en tu navegador:
```
https://tu-app.com/saml2/setup
```

Deberías ver el wizard de configuración inicial.

---

## Comandos Artisan Disponibles

El paquete incluye varios comandos para gestionar SAML2:

| Comando | Descripción |
|---------|-------------|
| `saml2:create-idp {key}` | Crear un nuevo IDP |
| `saml2:list-idps` | Listar todos los IDPs configurados |
| `saml2:test-idp {key}` | Probar la configuración de un IDP |
| `saml2:delete-idp {key}` | Eliminar un IDP |
| `saml2:generate-cert` | Generar certificados SP |
| `saml2:refresh-metadata` | Refrescar metadata de IDPs desde URLs |
| `saml2:publish-listener` | Publicar listener de login |
| `saml2:reset-setup` | Resetear al estado inicial de setup |

### Ejemplos de Uso

```bash
# Crear IDP desde una URL de metadata
php artisan saml2:create-idp azure --from-url=https://login.microsoftonline.com/{tenant}/federationmetadata/2007-06/federationmetadata.xml

# Crear IDP interactivamente
php artisan saml2:create-idp okta --interactive

# Listar IDPs
php artisan saml2:list-idps

# Probar conexión con IDP
php artisan saml2:test-idp azure

# Refrescar metadata de todos los IDPs
php artisan saml2:refresh-metadata

# Resetear setup (acceder al wizard nuevamente)
php artisan saml2:reset-setup
```

---

## Solución de Problemas

### Error: "Route [saml2.xxx] not found"

Ejecuta `php artisan route:clear` y verifica que el paquete esté correctamente instalado.

### Error: "Table beartropy_saml2_idps doesn't exist"

Ejecuta las migraciones: `php artisan migrate`

### El Setup Wizard no está accesible

Verifica que:
1. No haya un IDP ya configurado (el wizard solo aparece antes del primer IDP)
2. Si necesitas acceder nuevamente: `php artisan saml2:reset-setup`

### Error de certificados/firmas

Genera los certificados SP: `php artisan saml2:generate-cert`

---

## Siguiente Paso

Después de completar la instalación, continúa con la [Configuración](CONFIG.md) y el [Setup Inicial](SETUP.md).
