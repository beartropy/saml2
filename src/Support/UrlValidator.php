<?php

namespace Beartropy\Saml2\Support;

class UrlValidator
{
    /**
     * Ensure a redirect URL is safe (relative path only, no protocol-relative URLs).
     */
    public static function sanitizeRedirect(string $url, string $fallback = '/'): string
    {
        // Must start with a single slash and not be protocol-relative (//)
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }

        return $fallback;
    }
}
