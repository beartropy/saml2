<?php

namespace Beartropy\Saml2\Console\Commands;

use Beartropy\Saml2\Models\Saml2Idp;
use Beartropy\Saml2\Models\Saml2Setting;
use Illuminate\Console\Command;

class ResetSetupCommand extends Command
{
    protected $signature = 'saml2:reset-setup 
                            {--with-idps : Also delete all configured IDPs}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Reset SAML2 to first-deploy state (shows setup wizard again)';

    public function handle(): int
    {
        if (!$this->option('force')) {
            $message = $this->option('with-idps')
                ? 'This will reset the setup state AND delete all configured IDPs. Continue?'
                : 'This will reset the setup state. The setup wizard will appear again. Continue?';

            if (!$this->confirm($message)) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        // Reset setup state
        Saml2Setting::resetToFirstDeploy();
        $this->info('✓ Setup state reset to first-deploy.');

        // Optionally delete IDPs
        if ($this->option('with-idps')) {
            $count = Saml2Idp::count();
            Saml2Idp::truncate();
            $this->info("✓ Deleted {$count} IDP(s).");
        }

        $this->newLine();
        $this->info('The setup wizard will appear when accessing SAML2 setup route.');

        return self::SUCCESS;
    }
}
