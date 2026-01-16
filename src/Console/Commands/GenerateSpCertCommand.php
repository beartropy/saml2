<?php

namespace Beartropy\Saml2\Console\Commands;

use Illuminate\Console\Command;

class GenerateSpCertCommand extends Command
{
    protected $signature = 'saml2:generate-cert 
                            {--days=365 : Certificate validity in days}
                            {--out= : Output directory (default: storage/app/saml2)}';

    protected $description = 'Generate x509 certificate and private key for the SP';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $outDir = $this->option('out') ?? storage_path('app/saml2');

        if (!is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        $certFile = $outDir . '/sp.crt';
        $keyFile = $outDir . '/sp.key';

        if (file_exists($certFile) || file_exists($keyFile)) {
            if (!$this->confirm('Certificate files already exist. Overwrite?')) {
                $this->info('Cancelled.');
                return self::SUCCESS;
            }
        }

        $this->info('Generating SP certificate...');

        try {
            // Generate private key
            $privateKey = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);

            if (!$privateKey) {
                throw new \Exception('Failed to generate private key: ' . openssl_error_string());
            }

            // Generate CSR
            $dn = [
                'countryName' => $this->ask('Country (2 letter code)', 'US'),
                'stateOrProvinceName' => $this->ask('State/Province', 'State'),
                'localityName' => $this->ask('Locality/City', 'City'),
                'organizationName' => $this->ask('Organization name', config('app.name', 'Laravel')),
                'commonName' => $this->ask('Common Name (your domain)', parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost'),
            ];

            $csr = openssl_csr_new($dn, $privateKey);
            if (!$csr) {
                throw new \Exception('Failed to generate CSR: ' . openssl_error_string());
            }

            // Self-sign the certificate
            $cert = openssl_csr_sign($csr, null, $privateKey, $days);
            if (!$cert) {
                throw new \Exception('Failed to sign certificate: ' . openssl_error_string());
            }

            // Export key and certificate
            openssl_pkey_export($privateKey, $privateKeyPem);
            openssl_x509_export($cert, $certPem);

            // Save files
            file_put_contents($keyFile, $privateKeyPem);
            chmod($keyFile, 0600);

            file_put_contents($certFile, $certPem);

            $this->info('Certificate generated successfully!');
            $this->line('');
            $this->line("Certificate: {$certFile}");
            $this->line("Private Key: {$keyFile}");
            $this->line('');

            // Show the certificate content for .env
            $certForEnv = str_replace(["\r", "\n"], '', trim(str_replace(
                ['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'],
                '',
                $certPem
            )));

            $keyForEnv = str_replace(["\r", "\n"], '', trim(str_replace(
                ['-----BEGIN PRIVATE KEY-----', '-----END PRIVATE KEY-----', 
                 '-----BEGIN RSA PRIVATE KEY-----', '-----END RSA PRIVATE KEY-----'],
                '',
                $privateKeyPem
            )));

            $this->info('Add these to your .env file:');
            $this->line('');
            $this->line("SAML2_SP_CERT=\"{$certForEnv}\"");
            $this->line('');
            $this->line("SAML2_SP_PRIVATE_KEY=\"{$keyForEnv}\"");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to generate certificate: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
