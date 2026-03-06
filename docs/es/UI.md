# Interfaz de Administración (UI)

El paquete `beartropy/saml2` incluye un panel de administración web completo para gestionar Identity Providers sin necesidad de usar comandos o editar la base de datos directamente.

## Acceso al Panel

```
https://tu-app.com/saml2/admin
```

> **Nota**: El panel está protegido por los middleware configurados en `admin_middleware`. Por defecto requiere autenticación (`['web', 'auth']`).

---

## Dashboard Principal

El dashboard muestra una visión general de la configuración SAML2.

### Información del Service Provider

En la parte superior verás los datos de tu SP que puedes compartir con administradores de IDPs:

| Campo | Descripción |
|-------|-------------|
| **Entity ID** | Identificador único de tu aplicación |
| **ACS URL** | URL de Assertion Consumer Service |
| **Metadata URL** | Link al XML de metadata (click para ver) |

### Lista de Identity Providers

Una tabla con todos los IDPs configurados mostrando:

| Columna | Descripción |
|---------|-------------|
| **Key** | Identificador único (slug) del IDP |
| **Name** | Nombre legible del IDP |
| **Entity ID** | Identificador del IDP |
| **Status** | Badge indicando Activo/Inactivo |
| **Mapping** | Badge indicando si usa mapeo Global o Custom |
| **Actions** | Botones de acción |

### Acciones Disponibles por IDP

| Acción | Descripción |
|--------|-------------|
| **Edit** | Editar configuración del IDP |
| **Mapping** | Configurar mapeo de atributos |
| **Activate/Deactivate** | Activar o desactivar el IDP |
| **↻ (Refresh)** | Refrescar metadata desde URL (solo si tiene `metadata_url`) |
| **Delete** | Eliminar el IDP (con confirmación) |

---

## Crear Nuevo IDP

Para agregar un nuevo Identity Provider:

1. Haz clic en **"+ Add IDP"** en el dashboard
2. Se abrirá el formulario de creación

### Importar desde URL

La forma más rápida de configurar un IDP:

1. En la sección superior del formulario, ingresa la **URL del metadata**
2. Haz clic en **"Fetch"**
3. Los campos se llenarán automáticamente

> **Nota**: La obtención de metadata se realiza desde el navegador del usuario. Si falla por CORS, intenta usando el servidor como proxy.

### Campos del Formulario

| Campo | Requerido | Descripción |
|-------|-----------|-------------|
| **IDP Key** | ✅ | Identificador único (slug). Solo letras, números y guiones. Ej: `azure-prod` |
| **IDP Name** | ✅ | Nombre visible en la UI. Ej: `Azure Active Directory (Production)` |
| **Entity ID** | ✅ | Entity ID proporcionado por el IDP |
| **SSO URL** | ✅ | URL de Single Sign-On |
| **SLO URL** | ❌ | URL de Single Logout (opcional, para logout federado) |
| **X.509 Certificate** | ✅ | Certificado público del IDP (sin encabezados `-----BEGIN...`) |
| **Metadata URL** | ❌ | URL para refrescar metadata automáticamente |
| **Active** | ❌ | Checkbox para activar/desactivar el IDP |

### Ejemplo de Configuración para Azure AD

```
IDP Key:      azure
IDP Name:     Azure Active Directory
Entity ID:    https://sts.windows.net/{tenant-id}/
SSO URL:      https://login.microsoftonline.com/{tenant-id}/saml2
SLO URL:      https://login.microsoftonline.com/{tenant-id}/saml2
Metadata URL: https://login.microsoftonline.com/{tenant-id}/federationmetadata/2007-06/federationmetadata.xml
```

### Ejemplo de Configuración para Okta

```
IDP Key:      okta
IDP Name:     Okta SSO
Entity ID:    http://www.okta.com/exk...
SSO URL:      https://tu-org.okta.com/app/app-name/exk.../sso/saml
SLO URL:      https://tu-org.okta.com/app/app-name/exk.../slo/saml
```

---

## Editar IDP

Para modificar un IDP existente:

1. En la lista de IDPs, haz clic en **"Edit"**
2. Modifica los campos necesarios
3. Haz clic en **"Save Changes"**

> **Nota**: El campo **Key** no se puede modificar después de la creación. Esto se aplica a nivel de servidor para prevenir problemas con rutas y código.

---

## Mapeo de Atributos

El mapeo de atributos permite normalizar los claims SAML de diferentes IDPs a campos consistentes en tu aplicación.

### Acceder al Editor de Mapeo

1. En la lista de IDPs, haz clic en **"Mapping"**
2. Se abrirá el editor de mapeo para ese IDP

### Mapeo Global vs Custom

| Tipo | Descripción |
|------|-------------|
| **Global** | Usa el mapeo definido en `config/beartropy-saml2.php` |
| **Custom** | Mapeo específico para este IDP que sobreescribe el global |

### Usar Mapeo Global

1. Marca el checkbox **"Use global mapping"**
2. Verás una tabla de solo lectura con el mapeo global actual
3. Guarda para aplicar

### Configurar Mapeo Personalizado

1. Desmarca el checkbox **"Use global mapping"**
2. Aparecerá el editor de mapeo personalizado
3. Para cada campo:
   - **Local Field**: Nombre del campo en tu aplicación (ej: `email`, `name`)
   - **SAML Attribute**: Nombre del atributo SAML del IDP (ej: `http://schemas...`)

### Agregar/Eliminar Mapeos

- Haz clic en **"+ Add Mapping"** para agregar una nueva fila
- Haz clic en **"×"** para eliminar una fila existente

### Ejemplo de Mapeo para Azure AD

| Local Field | SAML Attribute |
|-------------|----------------|
| `email` | `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress` |
| `name` | `http://schemas.microsoft.com/identity/claims/displayname` |
| `firstname` | `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname` |
| `lastname` | `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname` |
| `groups` | `http://schemas.microsoft.com/ws/2008/06/identity/claims/groups` |

### Ejemplo de Mapeo para Okta

| Local Field | SAML Attribute |
|-------------|----------------|
| `email` | `email` |
| `name` | `name` |
| `firstname` | `firstName` |
| `lastname` | `lastName` |

---

## Activar/Desactivar IDP

Puedes activar o desactivar un IDP sin eliminarlo:

1. En la lista de IDPs, haz clic en **"Activate"** o **"Deactivate"**
2. El estado cambiará inmediatamente

### Estados

| Estado | Badge | Comportamiento |
|--------|-------|----------------|
| **Active** | 🟢 Verde | El IDP está disponible para login |
| **Inactive** | ⚪ Gris | El IDP no aparece en opciones de login |

> **Uso**: Desactiva un IDP temporalmente durante mantenimiento sin perder la configuración.

---

## Refrescar Metadata

Si el IDP tiene una `metadata_url` configurada, puedes actualizar su configuración automáticamente:

1. En la lista de IDPs, haz clic en **"↻"** (solo visible si tiene URL de metadata)
2. El sistema descargará y parseará el metadata
3. Se actualizarán: Entity ID, SSO URL, SLO URL, Certificado

> **Precaución**: Este proceso sobrescribirá cualquier cambio manual que hayas hecho en estos campos.

> **Seguridad**: Desde v0.3.0, la obtención de metadata del servidor incluye protección SSRF. URLs que apuntan a rangos de IP privados/reservados (10.x, 172.16.x, 192.168.x, 169.254.x, localhost) son bloqueados por defecto. Si tu IDP está en una red interna, establece `SAML2_BLOCK_PRIVATE_URLS=false` en tu `.env`.

---

## Eliminar IDP

Para eliminar permanentemente un IDP:

1. En la lista de IDPs, haz clic en **"Delete"**
2. Confirma en el diálogo que aparece
3. El IDP será eliminado de la base de datos

> **Advertencia**: Esta acción es irreversible. El IDP dejará de funcionar inmediatamente.

---

## Personalización del Panel

### Usar Layout Personalizado

Para integrar el panel con el layout de tu aplicación:

```php
// config/beartropy-saml2.php
'layout' => 'layouts.admin',
```

Tu layout debe ser un componente Blade que acepte un `$slot`:

```blade
{{-- resources/views/components/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Admin</title>
</head>
<body>
    {{ $slot }}
</body>
</html>
```

### Publicar Vistas

Para personalizar completamente la apariencia:

```bash
php artisan vendor:publish --tag=beartropy-saml2-views
```

Las vistas se crearán en `resources/views/vendor/beartropy-saml2/`:

```
resources/views/vendor/beartropy-saml2/
├── setup.blade.php              # Wizard de configuración inicial
├── setup-success.blade.php      # Página de éxito post-setup
└── admin/
    ├── index.blade.php          # Dashboard
    ├── idp-form.blade.php       # Formulario crear/editar
    ├── mapping.blade.php        # Editor de mapeo
    └── partials/
        └── layout.blade.php     # Layout base
```

> **Nota**: Las vistas usan HTML/CSS vanilla sin dependencias de frameworks CSS, por lo que puedes integrarlas fácilmente con cualquier stack.

---

## Proteger el Panel de Administración

### Middleware Personalizado

```php
// config/beartropy-saml2.php
'admin_middleware' => ['web', 'auth', 'can:manage-saml'],
```

### Definir Gate

```php
// app/Providers/AuthServiceProvider.php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('manage-saml', function ($user) {
        return $user->hasRole('admin');
        // o: return $user->is_admin;
        // o: return in_array($user->email, ['admin@empresa.com']);
    });
}
```

### Usar Spatie Permission

```php
'admin_middleware' => ['web', 'auth', 'role:admin'],
// o
'admin_middleware' => ['web', 'auth', 'permission:manage-saml'],
```

---

## Deshabilitar el Panel

Si prefieres gestionar los IDPs solo via Artisan:

```php
// config/beartropy-saml2.php
'admin_enabled' => false,
```

O via `.env`:

```env
SAML2_ADMIN_ENABLED=false
```

---

## Rutas del Panel Admin

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/saml2/admin` | GET | Dashboard principal |
| `/saml2/admin/idp/create` | GET | Formulario crear IDP |
| `/saml2/admin/idp` | POST | Guardar nuevo IDP |
| `/saml2/admin/idp/{id}` | GET | Formulario editar IDP |
| `/saml2/admin/idp/{id}` | PUT | Actualizar IDP |
| `/saml2/admin/idp/{id}` | DELETE | Eliminar IDP |
| `/saml2/admin/idp/{id}/toggle` | POST | Activar/desactivar IDP |
| `/saml2/admin/idp/{id}/mapping` | GET | Editor de mapeo |
| `/saml2/admin/idp/{id}/mapping` | POST | Guardar mapeo |
| `/saml2/admin/idp/{id}/refresh` | POST | Refrescar metadata |

---

## Internacionalización (i18n)

El panel está completamente traducido. Para cambiar el idioma:

### Opción 1: Cambiar Locale de Laravel

```php
// config/app.php
'locale' => 'es',
```

### Opción 2: Publicar y Personalizar Traducciones

```bash
php artisan vendor:publish --tag=beartropy-saml2-lang
```

Edita los archivos en `lang/vendor/beartropy-saml2/`:

- `en/saml2.php` - Inglés
- `es/saml2.php` - Español

---

## Troubleshooting

### El panel no carga estilos

Verifica que el layout tenga los estilos embebidos correctamente. El paquete incluye los estilos inline en el layout por defecto.

### Error "Unauthorized"

Verifica que:
1. Estás autenticado
2. Tu usuario tiene los permisos configurados en `admin_middleware`

### No puedo ver el botón de refrescar metadata

El botón solo aparece si el IDP tiene una `metadata_url` configurada.

### Los cambios no se guardan

Verifica que:
1. El formulario tiene el token CSRF (`@csrf`)
2. No hay errores de validación
3. Tienes permisos de escritura en la base de datos

---

## Siguiente Paso

Consulta la documentación de [Configuración](CONFIG.md) para opciones avanzadas o el [README principal](../README.md) para una visión general del paquete.
