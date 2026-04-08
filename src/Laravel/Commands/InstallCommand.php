<?php

namespace Ixopay\Client\Laravel\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'ixopay:install';

    protected $description = 'Publish the IXOPAY Laravel configuration file.';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'ixopay-config',
        ]);

        $this->components->info('IXOPAY config published. Add your credentials to .env before making requests.');

        return self::SUCCESS;
    }
}
