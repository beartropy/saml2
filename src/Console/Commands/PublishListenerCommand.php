<?php

namespace Beartropy\Saml2\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishListenerCommand extends Command
{
    protected $signature = 'saml2:publish-listener 
                            {--force : Overwrite existing file}';

    protected $description = 'Publish a standard HandleSaml2Login listener to your app';

    public function __construct(
        protected Filesystem $files
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $stubPath = __DIR__ . '/../../../stubs/HandleSaml2Login.stub';
        $targetPath = app_path('Listeners/HandleSaml2Login.php');

        if ($this->files->exists($targetPath) && !$this->option('force')) {
            $this->error('HandleSaml2Login.php already exists!');
            $this->line('Use --force to overwrite.');
            return self::FAILURE;
        }

        // Ensure directory exists
        $this->files->ensureDirectoryExists(dirname($targetPath));

        // Copy stub
        $this->files->copy($stubPath, $targetPath);

        $this->info('✓ Published: app/Listeners/HandleSaml2Login.php');
        $this->line('');
        $this->line('The listener will be auto-discovered by Laravel.');
        $this->line('Edit it to customize your authentication logic.');

        return self::SUCCESS;
    }
}
