<?php

namespace Beartropy\Saml2\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array buildSettings(\Beartropy\Saml2\Models\Saml2Idp|string $idp)
 * @method static \OneLogin\Saml2\Auth getAuth(\Beartropy\Saml2\Models\Saml2Idp|string $idp)
 * @method static string login(string $idpKey, ?string $returnTo = null)
 * @method static array processAcsResponse(string $idpKey)
 * @method static string logout(string $idpKey, ?string $returnTo = null, ?string $nameId = null, ?string $sessionIndex = null)
 * @method static ?string processSlo(string $idpKey, callable $keepLocalSession = null)
 * @method static string getMetadataXml()
 * @method static \Beartropy\Saml2\Services\IdpResolver getIdpResolver()
 * @method static \Beartropy\Saml2\Services\MetadataParser getMetadataParser()
 * 
 * @see \Beartropy\Saml2\Services\Saml2Service
 */
class Saml2 extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Beartropy\Saml2\Services\Saml2Service::class;
    }
}
