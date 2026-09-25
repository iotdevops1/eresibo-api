<?php

namespace App\Console\Commands;

use App\Models\IntegrationApiKey;
use Illuminate\Console\Command;

class SetIntegrationApiKeyScopes extends Command
{
    protected $signature = 'integration:set-key-scopes
                            {uuid : UUID of the existing integration key}
                            {--scope=* : Replacement scopes; repeat for each permission}';

    protected $description = 'Replace scopes on one integration key without rotating its secret';

    public function handle(): int
    {
        $scopes = array_values(array_unique($this->option('scope')));

        if ($scopes === [] || array_diff($scopes, IntegrationApiKey::SCOPES)) {
            $this->error('Specify --scope using: '.implode(', ', IntegrationApiKey::SCOPES));

            return self::FAILURE;
        }

        $key = IntegrationApiKey::query()->where('uuid', $this->argument('uuid'))->first();

        if (! $key) {
            $this->error('Integration key not found.');

            return self::FAILURE;
        }

        $key->update(['scopes' => $scopes]);
        $this->info('Scopes replaced for '.$key->uuid.': '.implode(', ', $scopes));

        return self::SUCCESS;
    }
}
