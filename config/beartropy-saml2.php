<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Service Provider (SP) Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Service Provider settings. These define how your
    | application identifies itself to Identity Providers.
    |
    */
    'sp' => [
        // Unique identifier for your SP (usually your app URL)
        'entityId' => env('SAML2_SP_ENTITY_ID'),
        
        // SP x509 certificate and private key
        'x509cert' => env('SAML2_SP_CERT'),
        'privateKey' => env('SAML2_SP_PRIVATE_KEY'),
        
        // Custom URLs (null = auto-generate based on routes)
        'acs_url' => env('SAML2_SP_ACS_URL'),
        'sls_url' => env('SAML2_SP_SLS_URL'),
        'metadata_url' => env('SAML2_SP_METADATA_URL'),
        
        // NameID format
        'nameIdFormat' => env('SAML2_SP_NAMEID_FORMAT', 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Identity Provider (IDP) Source
    |--------------------------------------------------------------------------
    |
    | Where to load IDP configurations from:
    | - 'env': Only from environment variables (single IDP)
    | - 'database': Only from database (multiple IDPs)
    | - 'both': Check env first, then database
    |
    */
    'idp_source' => env('SAML2_IDP_SOURCE', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Default IDP Configuration (for env source)
    |--------------------------------------------------------------------------
    |
    | When using 'env' or 'both' as idp_source, configure the default IDP here.
    |
    */
    'default_idp' => [
        'key' => env('SAML2_IDP_KEY', 'default'),
        'name' => env('SAML2_IDP_NAME', 'Default IDP'),
        'entityId' => env('SAML2_IDP_ENTITY_ID'),
        'ssoUrl' => env('SAML2_IDP_SSO_URL'),
        'sloUrl' => env('SAML2_IDP_SLO_URL'),
        'x509cert' => env('SAML2_IDP_CERT'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the routes for SAML2 endpoints.
    |
    */
    'route_prefix' => env('SAML2_ROUTE_PREFIX', 'saml2'),
    'route_middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the admin panel for managing IDPs and settings.
    |
    */
    'admin_enabled' => env('SAML2_ADMIN_ENABLED', true),
    'admin_route_prefix' => env('SAML2_ADMIN_PREFIX', 'saml2/admin'),
    'admin_middleware' => ['web', 'auth'], // Customize to protect admin routes


    /*
    |--------------------------------------------------------------------------
    | Redirect URLs
    |--------------------------------------------------------------------------
    |
    | Where to redirect users after login/logout or on errors.
    |
    */
    'login_redirect' => env('SAML2_LOGIN_REDIRECT', '/'),
    'logout_redirect' => env('SAML2_LOGOUT_REDIRECT', '/'),
    'error_redirect' => env('SAML2_ERROR_REDIRECT', '/login'),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model class to use for authentication lookups.
    |
    */
    'user_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Metadata Import
    |--------------------------------------------------------------------------
    |
    | Enable/disable the ability to import IDP metadata from URLs.
    |
    */
    'allow_metadata_import' => env('SAML2_ALLOW_METADATA_IMPORT', true),

    /*
    |--------------------------------------------------------------------------
    | Attribute Mapping
    |--------------------------------------------------------------------------
    |
    | Map SAML attributes to user-friendly keys. These are provided in
    | the Saml2LoginEvent for your listener to process.
    |
    */
    'attribute_mapping' => [
        'email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
        'name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name',
        'first_name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname',
        'last_name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Advanced security settings for SAML communication.
    |
    */
    'security' => [
        'nameIdEncrypted' => false,
        'authnRequestsSigned' => false,
        'logoutRequestSigned' => false,
        'logoutResponseSigned' => false,
        'signMetadata' => false,
        'wantMessagesSigned' => false,
        'wantAssertionsSigned' => false,
        'wantAssertionsEncrypted' => false,
        'wantNameIdEncrypted' => false,
        'requestedAuthnContext' => true,
        'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
        'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Enable debug mode for development. Should be false in production.
    |
    */
    'debug' => env('SAML2_DEBUG', false),
    'strict' => env('SAML2_STRICT', true),
];
