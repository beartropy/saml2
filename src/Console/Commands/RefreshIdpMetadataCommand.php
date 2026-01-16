<?php

namespace Beartropy\Saml2\Console\Commands;

use Beartropy\Saml2\Models\Saml2Idp;
use Beartropy\Saml2\Services\MetadataParser;
use Illuminate\Console\Command;

class RefreshIdpMetadataCommand extends Command
{
    protected $signature = 'saml2:refresh-metadata 
                            {key? : The IDP key to refresh (optional)}
                            {--all : Refresh all IDPs with metadata URLs}';

    protected $description = 'Refresh IDP configuration from metadata URLs';

    public function __construct(
        protected MetadataParser $metadataParser
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($key = $this->argument('key')) {
            return $this->refreshSingle($key);
        }

        if ($this->option('all')) {
            return $this->refreshAll();
        }

        $this->error('Please specify an IDP key or use --all');
        return self::FAILURE;
    }

    protected function refreshSingle(string $key): int
    {
        $idp = Saml2Idp::where('key', $key)->first();

        if (!$idp) {
            $this->error("IDP '{$key}' not found");
            return self::FAILURE;
        }

        if (!$idp->metadata_url) {
            $this->error("IDP '{$key}' has no metadata URL configured");
            return self::FAILURE;
        }

        return $this->refreshIdp($idp) ? self::SUCCESS : self::FAILURE;
    }

    protected function refreshAll(): int
    {
        $idps = Saml2Idp::whereNotNull('metadata_url')->get();

        if ($idps->isEmpty()) {
            $this->warn('No IDPs with metadata URLs found');
            return self::SUCCESS;
        }

        $this->info("Refreshing {$idps->count()} IDP(s)...");
        $this->line('');

        $hasErrors = false;
        foreach ($idps as $idp) {
            if (!$this->refreshIdp($idp)) {
                $hasErrors = true;
            }
        }

        return $hasErrors ? self::FAILURE : self::SUCCESS;
    }

    protected function refreshIdp(Saml2Idp $idp): bool
    {
        $this->info("Refreshing: {$idp->name} ({$idp->key})");

        try {
            $data = $this->metadataParser->parseFromUrl($idp->metadata_url);

            $oldSsoUrl = $idp->sso_url;
            $oldCert = substr($idp->x509_cert, 0, 20);

            $idp->update([
                'entity_id' => $data['entity_id'],
                'sso_url' => $data['sso_url'],
                'slo_url' => $data['slo_url'] ?? $idp->slo_url,
                'x509_cert' => $data['x509_cert'],
                'x509_cert_multi' => $data['x509_cert_multi'] ?? $idp->x509_cert_multi,
            ]);

            $this->info("  ✓ Updated from: {$idp->metadata_url}");
            
            // Show changes
            if ($oldSsoUrl !== $data['sso_url']) {
                $this->line("    SSO URL changed: {$oldSsoUrl} -> {$data['sso_url']}");
            }
            
            $newCert = substr($data['x509_cert'], 0, 20);
            if ($oldCert !== $newCert) {
                $this->line("    Certificate updated");
            }

            return true;
        } catch (\Throwable $e) {
            $this->error("  ✗ Failed: {$e->getMessage()}");
            return false;
        }
    }
}
