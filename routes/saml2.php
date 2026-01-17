<?php

use Beartropy\Saml2\Http\Controllers\AdminController;
use Beartropy\Saml2\Http\Controllers\Saml2Controller;
use Beartropy\Saml2\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('beartropy-saml2.route_prefix', 'saml2'),
    'middleware' => config('beartropy-saml2.route_middleware', ['web']),
], function () {
    // Setup wizard (first-deploy)
    Route::get('setup', [SetupController::class, 'index'])->name('saml2.setup');
    Route::post('setup/parse-text', [SetupController::class, 'parseText'])->name('saml2.setup.parse-text');
    Route::post('setup/parse-xml', [SetupController::class, 'parseXml'])->name('saml2.setup.parse-xml');
    Route::post('setup/fetch-url', [SetupController::class, 'fetchFromUrl'])->name('saml2.setup.fetch-url');
    Route::post('setup/save', [SetupController::class, 'save'])->name('saml2.setup.save');

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

// Admin routes (protected by admin middleware)
if (config('beartropy-saml2.admin_enabled', true)) {
    Route::group([
        'prefix' => config('beartropy-saml2.admin_route_prefix', 'saml2/admin'),
        'middleware' => config('beartropy-saml2.admin_middleware', ['web', 'auth']),
    ], function () {
        // Dashboard
        Route::get('/', [AdminController::class, 'index'])->name('saml2.admin.index');
        
        // IDP CRUD
        Route::get('idp/create', [AdminController::class, 'createIdp'])->name('saml2.admin.idp.create');
        Route::post('idp', [AdminController::class, 'storeIdp'])->name('saml2.admin.idp.store');
        Route::get('idp/{id}', [AdminController::class, 'editIdp'])->name('saml2.admin.idp.edit');
        Route::put('idp/{id}', [AdminController::class, 'updateIdp'])->name('saml2.admin.idp.update');
        Route::delete('idp/{id}', [AdminController::class, 'deleteIdp'])->name('saml2.admin.idp.delete');
        Route::post('idp/{id}/toggle', [AdminController::class, 'toggleIdp'])->name('saml2.admin.idp.toggle');
        Route::post('idp/{id}/refresh', [AdminController::class, 'refreshMetadata'])->name('saml2.admin.idp.refresh');
        
        // Attribute Mapping
        Route::get('idp/{id}/mapping', [AdminController::class, 'editMapping'])->name('saml2.admin.idp.mapping');
        Route::post('idp/{id}/mapping', [AdminController::class, 'updateMapping'])->name('saml2.admin.idp.mapping.update');
        
        // AJAX - parse metadata
        Route::post('parse-metadata', [AdminController::class, 'parseMetadata'])->name('saml2.admin.parse-metadata');
    });
}

