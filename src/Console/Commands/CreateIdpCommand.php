<?php

namespace Beartropy\Saml2\Console\Commands;

use Beartropy\Saml2\Models\Saml2Idp;
use Beartropy\Saml2\Services\MetadataParser;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateIdpCommand extends Command
{
    protected $signature = 'saml2:create-idp 
                            {key? : Unique slug for the IDP}
                            {--interactive : Run in interactive mode}
                            {--from-metadata= : Path to IDP metadata XML file}
                            {--from-url= : URL to fetch IDP metadata from}
                            {--name= : IDP display name}';

    protected $description = 'Create a new Identity Provider configuration';

    public function __construct(
        protected MetadataParser $metadataParser
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        // Check for metadata sources first
        if ($fromUrl = $this->option('from-url')) {
            return $this->createFromUrl($fromUrl);
        }

        if ($fromMetadata = $this->option('from-metadata')) {
            return $this->createFromFile($fromMetadata);
        }

        if ($this->option('interactive')) {
            return $this->createInteractive();
        }

        $this->error('Please use --interactive, --from-metadata, or --from-url option');
        return self::FAILURE;
    }

    protected function createFromUrl(string $url): int
    {
        $this->info("Fetching metadata from: {$url}");

        try {
            $data = $this->metadataParser->parseFromUrl($url);
            
            $key = $this->argument('key') ?? $this->generateKey($data['entity_id']);
            $name = $this->option('name') ?? $this->extractName($data['entity_id']);

            return $this->saveIdp($key, $name, $data);
        } catch (\Throwable $e) {
            $this->error("Failed to parse metadata: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    protected function createFromFile(string $path): int
    {
        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $this->info("Reading metadata from: {$path}");

        try {
            $xml = file_get_contents($path);
            $data = $this->metadataParser->parseXml($xml);
            
            $key = $this->argument('key') ?? $this->generateKey($data['entity_id']);
            $name = $this->option('name') ?? $this->extractName($data['entity_id']);

            return $this->saveIdp($key, $name, $data);
        } catch (\Throwable $e) {
            $this->error("Failed to parse metadata: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    protected function createInteractive(): int
    {
        $key = $this->argument('key') ?? $this->ask('IDP key (unique slug)');
        
        if (Saml2Idp::where('key', $key)->exists()) {
            $this->error("IDP with key '{$key}' already exists");
            return self::FAILURE;
        }

        $name = $this->ask('IDP display name', $key);
        $entityId = $this->ask('IDP Entity ID');
        $ssoUrl = $this->ask('SSO URL');
        $sloUrl = $this->ask('SLO URL (optional)', '');
        
        $this->info('x509 certificate (paste the certificate without headers, can be multiline):');
        $this->line('  Tip: Copy from IDP metadata, it can include line breaks.');
        $x509cert = $this->ask('Certificate');

        $data = [
            'entity_id' => $entityId,
            'sso_url' => $ssoUrl,
            'slo_url' => $sloUrl ?: null,
            'x509_cert' => $this->cleanCertificate($x509cert),
        ];

        return $this->saveIdp($key, $name, $data);
    }

    protected function saveIdp(string $key, string $name, array $data): int
    {
        if (Saml2Idp::where('key', $key)->exists()) {
            if (!$this->confirm("IDP '{$key}' already exists. Update it?")) {
                return self::FAILURE;
            }
            
            $idp = Saml2Idp::where('key', $key)->first();
            $idp->update([
                'name' => $name,
                'entity_id' => $data['entity_id'],
                'sso_url' => $data['sso_url'],
                'slo_url' => $data['slo_url'] ?? null,
                'x509_cert' => $this->cleanCertificate($data['x509_cert']),
                'x509_cert_multi' => $data['x509_cert_multi'] ?? null,
                'metadata_url' => $data['metadata_url'] ?? null,
            ]);
            
            $this->info("IDP '{$key}' updated successfully!");
        } else {
            Saml2Idp::create([
                'key' => $key,
                'name' => $name,
                'entity_id' => $data['entity_id'],
                'sso_url' => $data['sso_url'],
                'slo_url' => $data['slo_url'] ?? null,
                'x509_cert' => $this->cleanCertificate($data['x509_cert']),
                'x509_cert_multi' => $data['x509_cert_multi'] ?? null,
                'metadata_url' => $data['metadata_url'] ?? null,
                'is_active' => true,
            ]);
            
            $this->info("IDP '{$key}' created successfully!");
        }

        $this->line("  Entity ID: {$data['entity_id']}");
        $this->line("  SSO URL: {$data['sso_url']}");
        if ($data['slo_url'] ?? null) {
            $this->line("  SLO URL: {$data['slo_url']}");
        }
        $this->line("  Login URL: " . route('saml2.login', ['idp' => $key]));

        return self::SUCCESS;
    }

    protected function generateKey(string $entityId): string
    {
        $host = parse_url($entityId, PHP_URL_HOST) ?? $entityId;
        return Str::slug($host);
    }

    protected function extractName(string $entityId): string
    {
        $host = parse_url($entityId, PHP_URL_HOST) ?? $entityId;
        return Str::title(str_replace(['.', '-'], ' ', $host));
    }

    protected function cleanCertificate(string $cert): string
    {
        // Remove headers, whitespace
        $cert = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'], '', $cert);
        return preg_replace('/\s+/', '', $cert);
    }
}
