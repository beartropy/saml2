<?php

use Beartropy\Saml2\Http\Controllers\Saml2Controller;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('beartropy-saml2.route_prefix', 'saml2'),
    'middleware' => config('beartropy-saml2.route_middleware', ['web']),
], function () {
    // SSO Login - redirect to IDP (if no idp specified, uses first active)
    Route::get('login/{idp?}', [Saml2Controller::class, 'login'])
        ->name('saml2.login');
    
    // ACS - Generic (auto-detects IDP from SAML response Issuer)
    Route::post('acs', [Saml2Controller::class, 'acsAuto'])
        ->name('saml2.acs.auto')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    
    // ACS - Specific IDP (legacy, for backwards compatibility)
    Route::post('acs/{idp}', [Saml2Controller::class, 'acs'])
        ->name('saml2.acs')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    
    // SLS - Single Logout Service
    Route::match(['get', 'post'], 'sls/{idp}', [Saml2Controller::class, 'sls'])
        ->name('saml2.sls')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    
    // SP Metadata
    Route::get('metadata', [Saml2Controller::class, 'metadata'])
        ->name('saml2.metadata');
    
    // Logout (initiates SLO)
    Route::get('logout/{idp?}', [Saml2Controller::class, 'logout'])
        ->name('saml2.logout');
});
