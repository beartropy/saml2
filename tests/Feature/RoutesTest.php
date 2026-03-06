<?php

test('SAML routes are registered', function () {
    $routes = collect(app('router')->getRoutes()->getRoutes())
        ->map(fn ($route) => $route->getName())
        ->filter()
        ->values();

    expect($routes->toArray())
        ->toContain('saml2.login')
        ->toContain('saml2.acs.auto')
        ->toContain('saml2.acs')
        ->toContain('saml2.sls')
        ->toContain('saml2.metadata')
        ->toContain('saml2.logout')
        ->toContain('saml2.setup');
});

test('admin routes are registered when enabled', function () {
    $routes = collect(app('router')->getRoutes()->getRoutes())
        ->map(fn ($route) => $route->getName())
        ->filter()
        ->values();

    expect($routes->toArray())
        ->toContain('saml2.admin.index')
        ->toContain('saml2.admin.idp.create')
        ->toContain('saml2.admin.idp.store')
        ->toContain('saml2.admin.idp.edit')
        ->toContain('saml2.admin.idp.update')
        ->toContain('saml2.admin.idp.delete')
        ->toContain('saml2.admin.idp.toggle')
        ->toContain('saml2.admin.idp.refresh')
        ->toContain('saml2.admin.idp.mapping');
});

test('SAML routes have rate limiting middleware', function () {
    $loginRoute = app('router')->getRoutes()->getByName('saml2.login');
    $middleware = $loginRoute->middleware();

    expect($middleware)->toContain('throttle:60,1');
});

test('ACS routes exclude CSRF verification', function () {
    $acsRoute = app('router')->getRoutes()->getByName('saml2.acs');
    $excluded = $acsRoute->excludedMiddleware();

    // Should exclude at least one CSRF middleware class
    expect(
        in_array(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, $excluded) ||
        in_array(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, $excluded)
    )->toBeTrue();
});

test('logout route accepts optional IDP parameter', function () {
    $response = $this->get('/saml2/logout');
    // Should redirect (no SAML session), not 404
    $response->assertRedirect();
});
