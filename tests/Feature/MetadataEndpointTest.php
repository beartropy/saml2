<?php

use Beartropy\Saml2\Models\Saml2Idp;

test('metadata endpoint returns XML', function () {
    Saml2Idp::create([
        'key' => 'test',
        'name' => 'Test IDP',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'MIICpDCCAYwCCQ',
        'is_active' => true,
    ]);

    $response = $this->get('/saml2/metadata');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/xml');
    expect($response->getContent())->toContain('EntityDescriptor');
    expect($response->getContent())->toContain('https://test-app.com');
});

test('metadata endpoint works without IDPs', function () {
    $response = $this->get('/saml2/metadata');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/xml');
});
