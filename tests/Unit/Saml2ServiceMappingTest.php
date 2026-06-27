<?php

use Beartropy\Saml2\Services\Saml2Service;

/**
 * Invoke the protected mapAttributes() with the global config mapping.
 *
 * @param  array<string, mixed>  $attributes
 * @return array<string, mixed>
 */
function mapAttributes(array $attributes): array
{
    $service = app(Saml2Service::class);
    $method = new ReflectionMethod($service, 'mapAttributes');
    $method->setAccessible(true);

    return $method->invoke($service, $attributes, null);
}

test('mapAttributes preserves all values when an attribute is multi-valued', function () {
    config()->set('beartropy-saml2.attribute_mapping', ['roles' => 'roles']);

    $mapped = mapAttributes(['roles' => ['admin', 'editor']]);

    expect($mapped['roles'])->toBe(['admin', 'editor']);
});

test('mapAttributes collapses a single-valued attribute to a scalar', function () {
    config()->set('beartropy-saml2.attribute_mapping', [
        'email' => 'email',
        'roles' => 'roles',
    ]);

    $mapped = mapAttributes([
        'email' => ['user@example.com'],
        'roles' => ['admin'],
    ]);

    // Single value stays a string so getEmail()/scalar consumers keep working.
    expect($mapped['email'])->toBe('user@example.com')
        ->and($mapped['roles'])->toBe('admin');
});
