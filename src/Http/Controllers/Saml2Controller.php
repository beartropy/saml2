<?php

namespace Beartropy\Saml2\Http\Controllers;

use Beartropy\Saml2\Exceptions\InvalidIdpException;
use Beartropy\Saml2\Models\Saml2Idp;
use Beartropy\Saml2\Services\Saml2Service;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class Saml2Controller extends Controller
{
    public function __construct(
        protected Saml2Service $saml2Service
    ) {}

    /**
     * Initiate SSO login for an IDP.
     * 
     * GET /saml2/login/{idp?}
     */
    public function login(Request $request, ?string $idp = null)
    {
        try {
            // If no IDP specified, use first active IDP
            if (!$idp) {
                $firstIdp = Saml2Idp::active()->first();
                if (!$firstIdp) {
                    throw new InvalidIdpException('No active IDP configured');
                }
                $idp = $firstIdp->key;
            }

            $returnTo = $request->query('returnTo', config('beartropy-saml2.login_redirect', '/'));
            $redirectUrl = $this->saml2Service->login($idp, $returnTo);
            
            return redirect($redirectUrl);
        } catch (InvalidIdpException $e) {
            Log::error('SAML2 Login Error', [
                'idp' => $idp,
                'error' => $e->getMessage(),
            ]);
            
            return redirect(config('beartropy-saml2.error_redirect', '/login'))
                ->with('error', 'Invalid identity provider');
        } catch (\Throwable $e) {
            Log::error('SAML2 Login Error', [
                'idp' => $idp,
                'error' => $e->getMessage(),
            ]);
            
            return redirect(config('beartropy-saml2.error_redirect', '/login'))
                ->with('error', 'An error occurred during login');
        }
    }

    /**
     * Assertion Consumer Service - process SAML response.
     * 
     * POST /saml2/acs/{idp}
     */
    public function acs(Request $request, string $idp)
    {
        try {
            $result = $this->saml2Service->processAcsResponse($idp);
            
            // Store session info for potential SLO
            session([
                'saml2_idp' => $idp,
                'saml2_session_index' => $result['sessionIndex'],
                'saml2_name_id' => $result['nameId'],
            ]);

            // The Saml2LoginEvent has been dispatched by the service
            // The listener should handle authentication
            
            return redirect(config('beartropy-saml2.login_redirect', '/'));
        } catch (\Throwable $e) {
            Log::error('SAML2 ACS Error', [
                'idp' => $idp,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect(config('beartropy-saml2.error_redirect', '/login'))
                ->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Single Logout Service - process logout request/response.
     * 
     * GET /saml2/sls/{idp}
     */
    public function sls(Request $request, string $idp)
    {
        try {
            $redirectUrl = $this->saml2Service->processSlo($idp);
            
            // Clear SAML session data
            session()->forget(['saml2_idp', 'saml2_session_index', 'saml2_name_id']);
            
            // The Saml2LogoutEvent has been dispatched by the service
            // The listener should handle logout (Auth::logout(), etc.)
            
            return redirect($redirectUrl ?? config('beartropy-saml2.logout_redirect', '/'));
        } catch (\Throwable $e) {
            Log::error('SAML2 SLS Error', [
                'idp' => $idp,
                'error' => $e->getMessage(),
            ]);
            
            // Even on error, redirect to logout page
            return redirect(config('beartropy-saml2.logout_redirect', '/'));
        }
    }

    /**
     * SP Metadata endpoint.
     * 
     * GET /saml2/metadata
     */
    public function metadata()
    {
        try {
            $metadata = $this->saml2Service->getMetadataXml();
            
            return response($metadata, 200, [
                'Content-Type' => 'application/xml',
            ]);
        } catch (\Throwable $e) {
            Log::error('SAML2 Metadata Error', [
                'error' => $e->getMessage(),
            ]);
            
            return response('Error generating metadata: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Initiate logout via SAML SLO.
     * 
     * GET /saml2/logout/{idp?}
     */
    public function logout(Request $request, ?string $idp = null)
    {
        try {
            // Get IDP from session if not provided
            $idp = $idp ?? session('saml2_idp');
            
            if (!$idp) {
                // No SAML session, just redirect
                return redirect(config('beartropy-saml2.logout_redirect', '/'));
            }

            $nameId = session('saml2_name_id');
            $sessionIndex = session('saml2_session_index');
            $returnTo = $request->query('returnTo', config('beartropy-saml2.logout_redirect', '/'));

            $redirectUrl = $this->saml2Service->logout($idp, $returnTo, $nameId, $sessionIndex);
            
            return redirect($redirectUrl);
        } catch (\Throwable $e) {
            Log::error('SAML2 Logout Error', [
                'idp' => $idp,
                'error' => $e->getMessage(),
            ]);
            
            return redirect(config('beartropy-saml2.logout_redirect', '/'));
        }
    }
}
