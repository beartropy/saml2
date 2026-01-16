<?php

namespace Beartropy\Saml2\Models;

use Illuminate\Database\Eloquent\Model;

class Saml2Idp extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'beartropy_saml2_idps';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'name',
        'entity_id',
        'sso_url',
        'slo_url',
        'x509_cert',
        'x509_cert_multi',
        'metadata_url',
        'metadata',
        'attribute_mapping',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'x509_cert_multi' => 'array',
        'metadata' => 'array',
        'attribute_mapping' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to only include active IDPs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the login URL for this IDP.
     */
    public function getLoginUrl(): string
    {
        return route('saml2.login', ['idp' => $this->key]);
    }

    /**
     * Convert to onelogin/php-saml IDP settings array.
     */
    public function toIdpSettings(): array
    {
        $settings = [
            'entityId' => $this->entity_id,
            'singleSignOnService' => [
                'url' => $this->sso_url,
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            ],
            'x509cert' => $this->x509_cert,
        ];

        if ($this->slo_url) {
            $settings['singleLogoutService'] = [
                'url' => $this->slo_url,
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            ];
        }

        // Handle multiple certificates
        if ($this->x509_cert_multi) {
            $settings['x509certMulti'] = $this->x509_cert_multi;
        }

        return $settings;
    }

    /**
     * Check if the IDP is ready for use.
     */
    public function isReady(): bool
    {
        return $this->is_active
            && !empty($this->entity_id)
            && !empty($this->sso_url)
            && !empty($this->x509_cert);
    }

    /**
     * Get additional metadata value.
     */
    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Set additional metadata value.
     */
    public function setMetadataValue(string $key, $value): self
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Get the effective attribute mapping for this IDP.
     * Falls back to global config if IDP-specific mapping is empty.
     */
    public function getAttributeMapping(): array
    {
        // If IDP has custom mapping, use it
        if (!empty($this->attribute_mapping)) {
            return $this->attribute_mapping;
        }

        // Fallback to global config
        return config('beartropy-saml2.attribute_mapping', []);
    }

    /**
     * Check if this IDP has custom attribute mapping.
     */
    public function hasCustomAttributeMapping(): bool
    {
        return !empty($this->attribute_mapping);
    }
}
