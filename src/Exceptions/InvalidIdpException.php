<?php

namespace Beartropy\Saml2\Exceptions;

class InvalidIdpException extends Saml2Exception
{
    public static function notFound(string $idpKey): self
    {
        return new self("IDP '{$idpKey}' not found or is not active");
    }

    public static function notConfigured(string $idpKey): self
    {
        return new self("IDP '{$idpKey}' is not properly configured");
    }
}
