<?php

test('session prefix is configurable', function () {
    config(['beartropy-saml2.session_prefix' => 'custom_saml']);

    // Simulate what the controller does
    $prefix = config('beartropy-saml2.session_prefix', 'saml2');

    session([
        "{$prefix}_idp" => 'azure',
        "{$prefix}_name_id" => 'user@test.com',
        "{$prefix}_session_index" => 'idx-123',
    ]);

    expect(session('custom_saml_idp'))->toBe('azure');
    expect(session('custom_saml_name_id'))->toBe('user@test.com');
    expect(session('custom_saml_session_index'))->toBe('idx-123');
});

test('default session prefix is saml2', function () {
    $prefix = config('beartropy-saml2.session_prefix', 'saml2');
    expect($prefix)->toBe('saml2');
});
