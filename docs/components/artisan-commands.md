# Artisan Commands

CLI commands for managing SAML2 configuration.

## Commands

| Command | Description |
|---------|-------------|
| `saml2:create-idp` | Create a new IDP (interactive or from metadata URL) |
| `saml2:list-idps` | List all configured IDPs |
| `saml2:test-idp` | Test IDP connectivity |
| `saml2:delete-idp` | Delete an IDP |
| `saml2:generate-cert` | Generate SP certificates |
| `saml2:refresh-metadata` | Refresh metadata for all IDPs |
| `saml2:publish-listener` | Scaffold a login event listener |
| `saml2:reset-setup` | Reset to first-deploy state |

## Examples

```bash
# Create IDP from metadata URL
php artisan saml2:create-idp --url=https://idp.example.com/metadata

# List all IDPs
php artisan saml2:list-idps

# Generate SP certificates
php artisan saml2:generate-cert

# Scaffold login listener
php artisan saml2:publish-listener
```
