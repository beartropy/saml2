<?php

use Beartropy\Saml2\Events\Saml2LoginEvent;

test('getEmail returns mapped email', function () {
    $event = new Saml2LoginEvent(
        idpKey: 'test',
        nameId: 'fallback@example.com',
        attributes: ['email' => 'user@example.com'],
        rawAttributes: [],
    );

    expect($event->getEmail())->toBe('user@example.com');
});

test('getEmail falls back to nameId', function () {
    $event = new Saml2LoginEvent(
        idpKey: 'test',
        nameId: 'user@example.com',
        attributes: [],
        rawAttributes: [],
    );

    expect($event->getEmail())->toBe('user@example.com');
});

test('getName returns mapped name', function () {
    $event = new Saml2LoginEvent(
        idpKey: 'test',
        nameId: 'user@example.com',
        attributes: ['name' => 'John Doe'],
        rawAttributes: [],
    );

    expect($event->getName())->toBe('John Doe');
});

test('getAttribute returns value with default', function () {
    $event = new Saml2LoginEvent(
        idpKey: 'test',
        nameId: 'user@example.com',
        attributes: ['roles' => 'admin'],
        rawAttributes: [],
    );

    expect($event->getAttribute('roles'))->toBe('admin');
    expect($event->getAttribute('missing', 'default'))->toBe('default');
});

test('getRawAttribute unwraps array values', function () {
    $event = new Saml2LoginEvent(
        idpKey: 'test',
        nameId: 'user@example.com',
        attributes: [],
        rawAttributes: ['email' => ['user@example.com']],
    );

    expect($event->getRawAttribute('email'))->toBe('user@example.com');
});

test('toArray includes all data', function () {
    $event = new Saml2LoginEvent(
        idpKey: 'azure',
        nameId: 'user@example.com',
        attributes: ['email' => 'user@example.com'],
        rawAttributes: ['raw_email' => ['user@example.com']],
        sessionIndex: 'session-123',
    );

    $array = $event->toArray();

    expect($array)
        ->toHaveKey('idp_key', 'azure')
        ->toHaveKey('name_id', 'user@example.com')
        ->toHaveKey('session_index', 'session-123')
        ->toHaveKey('attributes')
        ->toHaveKey('raw_attributes');
});
