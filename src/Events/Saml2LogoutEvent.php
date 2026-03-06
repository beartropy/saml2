<?php

namespace Beartropy\Saml2\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Saml2LogoutEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param string $idpKey The key of the IDP that processed the logout
     * @param string|null $nameId The SAML NameID of the user who logged out
     * @param string|null $sessionIndex The SAML session index
     */
    public function __construct(
        public string $idpKey,
        public ?string $nameId = null,
        public ?string $sessionIndex = null,
    ) {}
}
