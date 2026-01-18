<?php

namespace Beartropy\Saml2\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Saml2LoginEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param string $idpKey The key of the IDP that authenticated the user
     * @param string $nameId The SAML NameID (usually email or unique identifier)
     * @param array $attributes Mapped attributes based on config
     * @param array $rawAttributes Raw SAML attributes as received
     * @param string|null $sessionIndex The SAML session index for SLO
     */
    public function __construct(
        public string $idpKey,
        public string $nameId,
        public array $attributes,
        public array $rawAttributes,
        public ?string $sessionIndex = null,
    ) {}

    /**
     * Get a specific mapped attribute.
     */
    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Get a specific raw SAML attribute.
     */
    public function getRawAttribute(string $key, $default = null)
    {
        $value = $this->rawAttributes[$key] ?? null;
        
        if ($value === null) {
            return $default;
        }

        // SAML attributes are typically arrays
        return is_array($value) ? ($value[0] ?? $default) : $value;
    }

    /**
     * Get all mapped attributes.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get all raw SAML attributes.
     */
    public function getRawAttributes(): array
    {
        return $this->rawAttributes;
    }

    /**
     * Get the email from common attribute sources.
     */
    public function getEmail(): ?string
    {
        return $this->getAttribute('email')
            ?? $this->getRawAttribute('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress')
            ?? $this->getRawAttribute('email')
            ?? $this->getRawAttribute('mail')
            ?? $this->nameId;
    }

    /**
     * Get the name from common attribute sources.
     */
    public function getName(): ?string
    {
        return $this->getAttribute('name')
            ?? $this->getRawAttribute('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name')
            ?? $this->getRawAttribute('displayName')
            ?? $this->getRawAttribute('cn');
    }

    /**
     * Get all event data as an array.
     */
    public function toArray(): array
    {
        return [
            'idp_key' => $this->idpKey,
            'name_id' => $this->nameId,
            'email' => $this->getEmail(),
            'name' => $this->getName(),
            'attributes' => $this->attributes,
            'raw_attributes' => $this->rawAttributes,
            'session_index' => $this->sessionIndex,
        ];
    }
}
