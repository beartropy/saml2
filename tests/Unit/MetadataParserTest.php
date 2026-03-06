<?php

use Beartropy\Saml2\Services\MetadataParser;
use Beartropy\Saml2\Exceptions\Saml2Exception;

beforeEach(function () {
    $this->parser = new MetadataParser();
});

test('parses valid SAML metadata XML', function () {
    $xml = <<<XML
    <md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
                         xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                         entityID="https://idp.example.com">
        <md:IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
            <md:KeyDescriptor use="signing">
                <ds:KeyInfo>
                    <ds:X509Data>
                        <ds:X509Certificate>MIICpDCCAYwCCQtest123</ds:X509Certificate>
                    </ds:X509Data>
                </ds:KeyInfo>
            </md:KeyDescriptor>
            <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                                    Location="https://idp.example.com/sso"/>
            <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                                    Location="https://idp.example.com/slo"/>
        </md:IDPSSODescriptor>
    </md:EntityDescriptor>
    XML;

    $result = $this->parser->parseXml($xml);

    expect($result)
        ->entity_id->toBe('https://idp.example.com')
        ->sso_url->toBe('https://idp.example.com/sso')
        ->slo_url->toBe('https://idp.example.com/slo')
        ->x509_cert->toBe('MIICpDCCAYwCCQtest123');
});

test('throws on invalid XML', function () {
    $this->parser->parseXml('not xml at all');
})->throws(Saml2Exception::class, 'Invalid XML');

test('throws when EntityDescriptor is missing', function () {
    $xml = '<root><child/></root>';
    $this->parser->parseXml($xml);
})->throws(Saml2Exception::class, 'No EntityDescriptor');

test('throws when IDPSSODescriptor is missing', function () {
    $xml = <<<XML
    <md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="https://idp.example.com">
        <md:SPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol"/>
    </md:EntityDescriptor>
    XML;

    $this->parser->parseXml($xml);
})->throws(Saml2Exception::class, 'No IDPSSODescriptor');

test('throws when certificate is missing', function () {
    $xml = <<<XML
    <md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="https://idp.example.com">
        <md:IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
            <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                                    Location="https://idp.example.com/sso"/>
        </md:IDPSSODescriptor>
    </md:EntityDescriptor>
    XML;

    $this->parser->parseXml($xml);
})->throws(Saml2Exception::class, 'No x509 certificate');

test('XXE prevention blocks external entities', function () {
    $xml = <<<XML
    <?xml version="1.0"?>
    <!DOCTYPE foo [
        <!ENTITY xxe SYSTEM "file:///etc/passwd">
    ]>
    <md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="&xxe;">
        <md:IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
            <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                                    Location="https://idp.example.com/sso"/>
        </md:IDPSSODescriptor>
    </md:EntityDescriptor>
    XML;

    // Should either throw or not contain file contents
    try {
        $result = $this->parser->parseXml($xml);
        // If it parses, the entity_id must NOT contain file contents
        expect($result['entity_id'])->not->toContain('root:');
    } catch (Saml2Exception $e) {
        // Throwing is also acceptable
        expect(true)->toBeTrue();
    }
});
