<?php

use Beartropy\Saml2\Models\Saml2Idp;

test('login redirects to IDP SSO URL', function () {
    Saml2Idp::create([
        'key' => 'test',
        'name' => 'Test IDP',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'MIICpDCCAYwCCQ',
        'is_active' => true,
    ]);

    $response = $this->get('/saml2/login/test');

    // Should redirect (302) to the IDP's SSO URL
    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('idp.example.com');
});

test('login without IDP uses first active', function () {
    Saml2Idp::create([
        'key' => 'first',
        'name' => 'First IDP',
        'entity_id' => 'https://first-idp.example.com',
        'sso_url' => 'https://first-idp.example.com/sso',
        'x509_cert' => 'MIICpDCCAYwCCQ',
        'is_active' => true,
    ]);

    $response = $this->get('/saml2/login');

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('first-idp.example.com');
});

test('login with no active IDP redirects to error', function () {
    $response = $this->get('/saml2/login');

    $response->assertRedirect(config('beartropy-saml2.error_redirect', '/login'));
});

test('login sanitizes returnTo parameter', function () {
    Saml2Idp::create([
        'key' => 'test',
        'name' => 'Test IDP',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'MIICpDCCAYwCCQ',
        'is_active' => true,
    ]);

    // Evil returnTo should be sanitized
    $response = $this->get('/saml2/login/test?returnTo=https://evil.com');
    $response->assertRedirect();

    // The redirect goes to the IDP, not to evil.com
    $location = $response->headers->get('Location');
    expect($location)->not->toContain('evil.com');
});

test('login with invalid IDP redirects to error', function () {
    $response = $this->get('/saml2/login/nonexistent');

    $response->assertRedirect(config('beartropy-saml2.error_redirect', '/login'));
});
