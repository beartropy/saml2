<?php

use Beartropy\Saml2\Models\Saml2Idp;
use Beartropy\Saml2\Services\IdpResolver;

beforeEach(function () {
    $this->resolver = new IdpResolver();
});

test('resolves IDP from database', function () {
    Saml2Idp::create([
        'key' => 'azure',
        'name' => 'Azure AD',
        'entity_id' => 'https://sts.windows.net/test/',
        'sso_url' => 'https://login.microsoftonline.com/test/saml2',
        'x509_cert' => 'test-cert',
        'is_active' => true,
    ]);

    $idp = $this->resolver->resolve('azure');

    expect($idp)->not->toBeNull();
    expect($idp->key)->toBe('azure');
});

test('returns null for non-existent IDP', function () {
    expect($this->resolver->resolve('nonexistent'))->toBeNull();
});

test('returns null for inactive IDP', function () {
    Saml2Idp::create([
        'key' => 'inactive',
        'name' => 'Inactive IDP',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'cert',
        'is_active' => false,
    ]);

    expect($this->resolver->resolveFromDatabase('inactive'))->toBeNull();
});

test('resolves IDP from env config', function () {
    config([
        'beartropy-saml2.idp_source' => 'env',
        'beartropy-saml2.default_idp' => [
            'key' => 'default',
            'name' => 'Env IDP',
            'entityId' => 'https://env-idp.example.com',
            'ssoUrl' => 'https://env-idp.example.com/sso',
            'x509cert' => 'env-cert',
        ],
    ]);

    $idp = $this->resolver->resolve('default');

    expect($idp)->not->toBeNull();
    expect($idp->entity_id)->toBe('https://env-idp.example.com');
    expect($idp->is_active)->toBeTrue();
});

test('env resolver returns null for wrong key', function () {
    config([
        'beartropy-saml2.default_idp' => [
            'key' => 'default',
            'entityId' => 'https://idp.example.com',
            'ssoUrl' => 'https://idp.example.com/sso',
        ],
    ]);

    expect($this->resolver->resolveFromEnv('wrong-key'))->toBeNull();
});

test('resolveByEntityId finds IDP', function () {
    Saml2Idp::create([
        'key' => 'test',
        'name' => 'Test',
        'entity_id' => 'https://unique-entity.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'cert',
        'is_active' => true,
    ]);

    $idp = $this->resolver->resolveByEntityId('https://unique-entity.example.com');

    expect($idp)->not->toBeNull();
    expect($idp->key)->toBe('test');
});

test('all returns collection of active IDPs', function () {
    Saml2Idp::create([
        'key' => 'idp1',
        'name' => 'IDP 1',
        'entity_id' => 'https://idp1.example.com',
        'sso_url' => 'https://idp1.example.com/sso',
        'x509_cert' => 'cert',
        'is_active' => true,
    ]);

    Saml2Idp::create([
        'key' => 'idp2',
        'name' => 'IDP 2',
        'entity_id' => 'https://idp2.example.com',
        'sso_url' => 'https://idp2.example.com/sso',
        'x509_cert' => 'cert',
        'is_active' => false,
    ]);

    $all = $this->resolver->all();

    expect($all)->toHaveCount(1);
    expect($all->first()->key)->toBe('idp1');
});

test('exists returns correct boolean', function () {
    Saml2Idp::create([
        'key' => 'existing',
        'name' => 'Existing',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'cert',
        'is_active' => true,
    ]);

    expect($this->resolver->exists('existing'))->toBeTrue();
    expect($this->resolver->exists('nope'))->toBeFalse();
});
