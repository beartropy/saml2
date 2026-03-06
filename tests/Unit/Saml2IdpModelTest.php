<?php

use Beartropy\Saml2\Models\Saml2Idp;

test('key is immutable after creation', function () {
    $idp = Saml2Idp::create([
        'key' => 'original-key',
        'name' => 'Test IDP',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'test-cert',
        'is_active' => true,
    ]);

    $idp->update(['key' => 'new-key', 'name' => 'Updated Name']);

    $idp->refresh();
    expect($idp->key)->toBe('original-key');
    expect($idp->name)->toBe('Updated Name');
});

test('isReady returns true when properly configured', function () {
    $idp = new Saml2Idp([
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'some-cert',
        'is_active' => true,
    ]);

    expect($idp->isReady())->toBeTrue();
});

test('isReady returns false when inactive', function () {
    $idp = new Saml2Idp([
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'some-cert',
        'is_active' => false,
    ]);

    expect($idp->isReady())->toBeFalse();
});

test('isReady returns false when missing entity_id', function () {
    $idp = new Saml2Idp([
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'some-cert',
        'is_active' => true,
    ]);

    expect($idp->isReady())->toBeFalse();
});

test('toIdpSettings returns correct structure', function () {
    $idp = new Saml2Idp([
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'slo_url' => 'https://idp.example.com/slo',
        'x509_cert' => 'test-cert',
    ]);

    $settings = $idp->toIdpSettings();

    expect($settings)
        ->toHaveKey('entityId', 'https://idp.example.com')
        ->toHaveKey('singleSignOnService')
        ->toHaveKey('singleLogoutService')
        ->toHaveKey('x509cert', 'test-cert');
});

test('toIdpSettings omits SLO when not configured', function () {
    $idp = new Saml2Idp([
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'test-cert',
    ]);

    $settings = $idp->toIdpSettings();

    expect($settings)->not->toHaveKey('singleLogoutService');
});

test('active scope filters correctly', function () {
    Saml2Idp::create([
        'key' => 'active-idp',
        'name' => 'Active',
        'entity_id' => 'https://active.example.com',
        'sso_url' => 'https://active.example.com/sso',
        'x509_cert' => 'cert',
        'is_active' => true,
    ]);

    Saml2Idp::create([
        'key' => 'inactive-idp',
        'name' => 'Inactive',
        'entity_id' => 'https://inactive.example.com',
        'sso_url' => 'https://inactive.example.com/sso',
        'x509_cert' => 'cert',
        'is_active' => false,
    ]);

    expect(Saml2Idp::active()->count())->toBe(1);
    expect(Saml2Idp::active()->first()->key)->toBe('active-idp');
});

test('x509_cert is hidden from serialization', function () {
    $idp = new Saml2Idp([
        'key' => 'test',
        'name' => 'Test',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'secret-cert-data',
        'is_active' => true,
    ]);

    $array = $idp->toArray();
    expect($array)->not->toHaveKey('x509_cert');
    expect($array)->not->toHaveKey('x509_cert_multi');
});

test('getAttributeMapping returns custom mapping when set', function () {
    $idp = new Saml2Idp();
    $idp->attribute_mapping = ['email' => 'custom_email'];

    expect($idp->getAttributeMapping())->toBe(['email' => 'custom_email']);
});

test('getAttributeMapping falls back to global config', function () {
    $idp = new Saml2Idp();
    $idp->attribute_mapping = null;

    $mapping = $idp->getAttributeMapping();
    expect($mapping)->toBeArray();
    expect($mapping)->toHaveKey('email');
});
