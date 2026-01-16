<?php

namespace Beartropy\Saml2\Console\Commands;

use Beartropy\Saml2\Services\IdpResolver;
use Beartropy\Saml2\Services\Saml2Service;
use Illuminate\Console\Command;

class TestIdpCommand extends Command
{
    protected $signature = 'saml2:test-idp 
                            {key : The IDP key to test}
                            {--validate-cert : Validate the x509 certificate}';

    protected $description = 'Test an Identity Provider configuration';

    public function __construct(
        protected IdpResolver $idpResolver,
        protected Saml2Service $saml2Service
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $key = $this->argument('key');
        $idp = $this->idpResolver->resolve($key);

        if (!$idp) {
            $this->error("IDP '{$key}' not found");
            return self::FAILURE;
        }

        $this->info("Testing IDP: {$idp->name} ({$idp->key})");
        $this->line('');

        $hasErrors = false;

        // Check basic config
        $this->line('Configuration:');
        $this->checkField('Entity ID', $idp->entity_id);
        $this->checkField('SSO URL', $idp->sso_url);
        $this->checkField('SLO URL', $idp->slo_url, false);
        $this->checkField('x509 Certificate', !empty($idp->x509_cert) ? 'Present (' . strlen($idp->x509_cert) . ' chars)' : null);
        $this->line('');

        // Check if ready
        if (!$idp->isReady()) {
            $this->error('IDP is NOT ready - missing required configuration');
            $hasErrors = true;
        } else {
            $this->info('✓ IDP configuration is complete');
        }

        // Validate certificate if requested
        if ($this->option('validate-cert')) {
            $this->line('');
            $this->line('Certificate Validation:');
            $certResult = $this->validateCertificate($idp->x509_cert);
            if ($certResult['valid']) {
                $this->info("  ✓ Certificate is valid");
                $this->line("  Subject: {$certResult['subject']}");
                $this->line("  Issuer: {$certResult['issuer']}");
                $this->line("  Valid from: {$certResult['valid_from']}");
                $this->line("  Valid to: {$certResult['valid_to']}");
                
                if ($certResult['expired']) {
                    $this->error("  ✗ Certificate has EXPIRED!");
                    $hasErrors = true;
                }
            } else {
                $this->error("  ✗ Invalid certificate: {$certResult['error']}");
                $hasErrors = true;
            }
        }

        // Try to build settings
        $this->line('');
        $this->line('Settings Build Test:');
        try {
            $settings = $this->saml2Service->buildSettings($idp);
            $this->info('  ✓ Settings built successfully');
        } catch (\Throwable $e) {
            $this->error("  ✗ Failed to build settings: {$e->getMessage()}");
            $hasErrors = true;
        }

        // Show login URL
        $this->line('');
        $this->info('Login URL: ' . route('saml2.login', ['idp' => $idp->key]));

        return $hasErrors ? self::FAILURE : self::SUCCESS;
    }

    protected function checkField(string $name, $value, bool $required = true): void
    {
        if ($value) {
            $this->line("  ✓ {$name}: <fg=green>configured</>");
        } elseif ($required) {
            $this->line("  ✗ {$name}: <fg=red>missing</>");
        } else {
            $this->line("  - {$name}: <fg=yellow>not set</>");
        }
    }

    protected function validateCertificate(string $cert): array
    {
        try {
            // Add PEM headers if missing
            if (strpos($cert, '-----BEGIN CERTIFICATE-----') === false) {
                $cert = "-----BEGIN CERTIFICATE-----\n" . 
                        chunk_split($cert, 64, "\n") . 
                        "-----END CERTIFICATE-----\n";
            }

            $certData = openssl_x509_parse($cert);
            if (!$certData) {
                return ['valid' => false, 'error' => 'Could not parse certificate'];
            }

            $validFrom = date('Y-m-d H:i:s', $certData['validFrom_time_t']);
            $validTo = date('Y-m-d H:i:s', $certData['validTo_time_t']);
            $expired = time() > $certData['validTo_time_t'];

            return [
                'valid' => true,
                'subject' => $certData['subject']['CN'] ?? 'Unknown',
                'issuer' => $certData['issuer']['CN'] ?? 'Unknown',
                'valid_from' => $validFrom,
                'valid_to' => $validTo,
                'expired' => $expired,
            ];
        } catch (\Throwable $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }
}
