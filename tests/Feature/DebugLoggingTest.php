<?php

use Beartropy\Saml2\Models\Saml2Idp;
use Beartropy\Saml2\Services\Saml2Service;
use Illuminate\Support\Facades\Log;

function makeDebugIdp(): Saml2Idp
{
    return Saml2Idp::create([
        'key' => 'test',
        'name' => 'Test IDP',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'MIICpDCCAYwCCQ',
        'is_active' => true,
    ]);
}

test('login does not log when debug is disabled', function () {
    config()->set('beartropy-saml2.debug', false);
    makeDebugIdp();

    Log::spy();

    app(Saml2Service::class)->login('test');

    Log::shouldNotHaveReceived('debug');
});

test('login writes a debug log when debug is enabled', function () {
    config()->set('beartropy-saml2.debug', true);
    makeDebugIdp();

    Log::spy();

    app(Saml2Service::class)->login('test');

    Log::shouldHaveReceived('debug')
        ->withArgs(fn ($message, $context = []) => str_contains($message, 'Initiating SSO login')
            && ($context['idp'] ?? null) === 'test')
        ->once();
});

test('debug logs are routed to the configured channel', function () {
    config()->set('beartropy-saml2.debug', true);
    config()->set('beartropy-saml2.log_channel', 'saml');
    makeDebugIdp();

    $channel = Mockery::spy(Psr\Log\LoggerInterface::class);
    Log::shouldReceive('channel')->with('saml')->andReturn($channel);

    app(Saml2Service::class)->login('test');

    $channel->shouldHaveReceived('debug')
        ->withArgs(fn ($message, $context = []) => str_contains($message, 'Initiating SSO login'))
        ->once();
});
