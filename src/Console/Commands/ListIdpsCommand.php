<?php

namespace Beartropy\Saml2\Console\Commands;

use Beartropy\Saml2\Services\IdpResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ListIdpsCommand extends Command
{
    protected $signature = 'saml2:list-idps';

    protected $description = 'List all configured Identity Providers';

    public function __construct(
        protected IdpResolver $idpResolver
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $idps = $this->idpResolver->all();

        if ($idps->isEmpty()) {
            $this->warn('No IDPs configured.');
            $this->line('');
            $this->line('To configure an IDP:');
            $this->line('  php artisan saml2:create-idp --interactive');
            $this->line('  php artisan saml2:create-idp my-idp --from-url=https://idp.example.com/metadata');
            return self::SUCCESS;
        }

        $this->info('Configured Identity Providers:');
        $this->line('');

        $rows = [];
        foreach ($idps as $idp) {
            $source = $idp->exists ? 'database' : 'env';
            $status = $idp->is_active ? '<fg=green>Active</>' : '<fg=red>Inactive</>';
            
            $rows[] = [
                $idp->key,
                $idp->name,
                $source,
                $status,
                Str::limit($idp->entity_id, 40),
            ];
        }

        $this->table(
            ['Key', 'Name', 'Source', 'Status', 'Entity ID'],
            $rows
        );

        $this->line('');
        $this->info('Login URLs:');
        foreach ($idps as $idp) {
            $this->line("  {$idp->key}: " . route('saml2.login', ['idp' => $idp->key]));
        }

        return self::SUCCESS;
    }
}
