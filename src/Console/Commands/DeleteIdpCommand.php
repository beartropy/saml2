<?php

namespace Beartropy\Saml2\Console\Commands;

use Beartropy\Saml2\Models\Saml2Idp;
use Illuminate\Console\Command;

class DeleteIdpCommand extends Command
{
    protected $signature = 'saml2:delete-idp 
                            {key : The IDP key to delete}
                            {--force : Skip confirmation}';

    protected $description = 'Delete an Identity Provider from the database';

    public function handle(): int
    {
        $key = $this->argument('key');
        $idp = Saml2Idp::where('key', $key)->first();

        if (!$idp) {
            $this->error("IDP '{$key}' not found in database");
            return self::FAILURE;
        }

        $this->warn("You are about to delete IDP: {$idp->name} ({$idp->key})");
        $this->line("  Entity ID: {$idp->entity_id}");

        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete this IDP?')) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        $idp->delete();

        $this->info("IDP '{$key}' deleted successfully");
        return self::SUCCESS;
    }
}
