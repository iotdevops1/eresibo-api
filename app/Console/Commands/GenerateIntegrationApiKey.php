<?php

namespace App\Console\Commands;

use App\Models\IntegrationApiKey;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('app:generate-integration-api-key')]
#[Description('Command description')]

class GenerateIntegrationApiKey extends Command
{
    protected $signature = 'integration:generate-api-key
                            {name : Name of the integration key}
                            {--environment=sandbox : sandbox or production}
                            {--days=365 : Number of days before expiry}';

    protected $description = 'Generate an integration API key';

    public function handle(): int
    {
        $name = $this->argument('name');

        $environment = $this->option('environment');

        $days = (int) $this->option('days');

        if (! in_array($environment, [
            'sandbox',
            'production',
        ], true)) {
            $this->error(
                'Environment must be sandbox or production.'
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Generate secret
        |--------------------------------------------------------------------------
        */

        $secret = bin2hex(
            random_bytes(32)
        );

        $apiKey = match ($environment) {
            'sandbox' =>
                'ersb_sbx_' . $secret,

            'production' =>
                'ersb_prod_' . $secret,
        };

        /*
        |--------------------------------------------------------------------------
        | Store only hash
        |--------------------------------------------------------------------------
        */

        $record = IntegrationApiKey::create([
            'uuid' => (string) Str::uuid(),

            'name' => $name,

            'key_hash' => hash(
                'sha256',
                $apiKey
            ),

            'environment' => $environment,

            'active' => true,

            'expires_at' => now()->addDays($days),
        ]);

        $this->newLine();

        $this->info(
            'Integration API key generated successfully.'
        );

        $this->newLine();

        $this->line(
            'Name: ' . $record->name
        );

        $this->line(
            'Environment: ' . $record->environment
        );

        $this->line(
            'Expires: ' . $record->expires_at?->toISOString()
        );

        $this->newLine();

        $this->warn(
            'SAVE THIS KEY NOW. The plaintext key is not stored and cannot be retrieved later.'
        );

        $this->newLine();

        $this->line(
            $apiKey
        );

        $this->newLine();

        return self::SUCCESS;
    }
}
