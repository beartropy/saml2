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
    $router = app('router');

    // The CSRF guard's class name differs by Laravel version: Laravel 13 registers
    // the parent PreventRequestForgery, while L11/L12 register ValidateCsrfToken
    // (which now extends it). withoutMiddleware matches by exact class, not by
    // ancestry, so the resolved stack must be checked against every variant —
    // asserting only the declared exclusion list would miss the L13 regression.
    $csrfClasses = [
        \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    ];

    foreach (['saml2.acs.auto', 'saml2.acs', 'saml2.sls'] as $name) {
        $route = $router->getRoutes()->getByName($name);
        $gathered = $router->gatherRouteMiddleware($route);

        foreach ($csrfClasses as $csrf) {
            expect($gathered)->not->toContain($csrf);
        }
    }
});

test('logout route accepts optional IDP parameter', function () {
    $response = $this->get('/saml2/logout');
    // Should redirect (no SAML session), not 404
    $response->assertRedirect();
});
