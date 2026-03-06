<?php

namespace Beartropy\Saml2\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IdpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uniqueRule = 'unique:beartropy_saml2_idps,key';

        // On update, exclude the current IDP from unique check
        if ($this->route('idp')) {
            $uniqueRule .= ',' . $this->route('idp')->id;
        }

        return [
            'idp_key' => ['required', 'string', 'alpha_dash', $uniqueRule],
            'idp_name' => 'required|string|max:255',
            'entity_id' => 'required|string',
            'sso_url' => 'required|url',
            'slo_url' => 'nullable|url',
            'x509_cert' => 'required|string',
            'metadata_url' => 'nullable|url',
        ];
    }
}
