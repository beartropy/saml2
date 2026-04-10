---
name: bt-saml2-component
description: Get detailed information and examples for Beartropy SAML2 components and API
version: 1.0.0
author: Beartropy
tags: [beartropy, saml2, sso, components, documentation, examples]
---

# Beartropy SAML2 Component Helper

You are an expert in Beartropy SAML2. Use this guide to help users configure SSO and handle SAML authentication.

---

## Quick Reference

| Task | How |
|---|---|
| Initiate login | `Saml2::login('idp_key')` |
| Process ACS | `Saml2::processAcsResponse('idp_key')` |
| Auto-detect IDP | `Saml2::processAcsResponseAuto()` |
| Get metadata | `Saml2::getMetadataXml()` |
| Handle login event | Listen for `Saml2LoginEvent` |
| Create IDP | `php artisan saml2:create-idp` |
| Generate certs | `php artisan saml2:generate-cert` |

## Authentication Flow

1. `/saml2/login/{idp}` → redirect to IDP
2. IDP → POST to `/saml2/acs/{idp}`
3. `Saml2LoginEvent` dispatched
4. Your listener calls `Auth::login($user)`
