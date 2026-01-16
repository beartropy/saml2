<?php

namespace Beartropy\Saml2\Services;

use Beartropy\Saml2\Models\Saml2Idp;
use Illuminate\Support\Collection;

class IdpResolver
{
    /**
     * Resolve an IDP by its key.
     */
    public function resolve(string $idpKey): ?Saml2Idp
    {
        $source = config('beartropy-saml2.idp_source', 'database');

        return match ($source) {
            'env' => $this->resolveFromEnv($idpKey),
            'database' => $this->resolveFromDatabase($idpKey),
            'both' => $this->resolveFromEnv($idpKey) ?? $this->resolveFromDatabase($idpKey),
            default => $this->resolveFromDatabase($idpKey),
        };
    }

    /**
     * Resolve IDP from environment configuration.
     */
    public function resolveFromEnv(string $idpKey): ?Saml2Idp
    {
        $defaultIdp = config('beartropy-saml2.default_idp');

        if (!$defaultIdp || ($defaultIdp['key'] ?? 'default') !== $idpKey) {
            return null;
        }

        if (empty($defaultIdp['entityId']) || empty($defaultIdp['ssoUrl'])) {
            return null;
        }

        // Create a virtual IDP model from env config
        $idp = new Saml2Idp();
        $idp->key = $defaultIdp['key'] ?? 'default';
        $idp->name = $defaultIdp['name'] ?? 'Default IDP';
        $idp->entity_id = $defaultIdp['entityId'];
        $idp->sso_url = $defaultIdp['ssoUrl'];
        $idp->slo_url = $defaultIdp['sloUrl'] ?? null;
        $idp->x509_cert = $defaultIdp['x509cert'] ?? '';
        $idp->is_active = true;

        return $idp;
    }

    /**
     * Resolve IDP from database.
     */
    public function resolveFromDatabase(string $idpKey): ?Saml2Idp
    {
        return Saml2Idp::where('key', $idpKey)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all available IDPs.
     */
    public function all(): Collection
    {
        $source = config('beartropy-saml2.idp_source', 'database');
        $idps = collect();

        // Add env IDP if configured
        if ($source === 'env' || $source === 'both') {
            $envIdp = $this->resolveFromEnv(config('beartropy-saml2.default_idp.key', 'default'));
            if ($envIdp) {
                $idps->push($envIdp);
            }
        }

        // Add database IDPs
        if ($source === 'database' || $source === 'both') {
            $dbIdps = Saml2Idp::where('is_active', true)->get();
            
            // Merge, avoiding duplicates by key
            foreach ($dbIdps as $dbIdp) {
                if (!$idps->contains('key', $dbIdp->key)) {
                    $idps->push($dbIdp);
                }
            }
        }

        return $idps;
    }

    /**
     * Check if an IDP exists by key.
     */
    public function exists(string $idpKey): bool
    {
        return $this->resolve($idpKey) !== null;
    }
}
