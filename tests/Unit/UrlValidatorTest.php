<?php

use Beartropy\Saml2\Support\UrlValidator;

test('sanitizeRedirect allows relative paths', function () {
    expect(UrlValidator::sanitizeRedirect('/dashboard'))->toBe('/dashboard');
    expect(UrlValidator::sanitizeRedirect('/a/b/c'))->toBe('/a/b/c');
    expect(UrlValidator::sanitizeRedirect('/'))->toBe('/');
});

test('sanitizeRedirect blocks absolute URLs', function () {
    expect(UrlValidator::sanitizeRedirect('https://evil.com'))->toBe('/');
    expect(UrlValidator::sanitizeRedirect('http://evil.com/path'))->toBe('/');
});

test('sanitizeRedirect blocks protocol-relative URLs', function () {
    expect(UrlValidator::sanitizeRedirect('//evil.com'))->toBe('/');
    expect(UrlValidator::sanitizeRedirect('//evil.com/path'))->toBe('/');
});

test('sanitizeRedirect uses custom fallback', function () {
    expect(UrlValidator::sanitizeRedirect('https://evil.com', '/login'))->toBe('/login');
});

test('sanitizeRedirect blocks javascript URLs', function () {
    expect(UrlValidator::sanitizeRedirect('javascript:alert(1)'))->toBe('/');
});
