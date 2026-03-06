<?php

namespace Beartropy\Saml2\Support;

class CertificateHelper
{
    /**
     * Clean certificate string by removing headers and whitespace.
     */
    public static function clean(string $cert): string
    {
        $cert = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'], '', $cert);
        return preg_replace('/\s+/', '', $cert);
    }
}
