<?php

namespace App\Services\Integration;

use App\Jobs\SendPusoPayReceiptWebhook;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;

class PusoPayReceiptService
{
    public function createOrGet(array $data): Receipt
    {
        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Idempotency
            |--------------------------------------------------------------------------
            */

            $existing = Receipt::query()
                ->where(
                    'external_reference',
                    $data['externalReference']
                )
                ->first();

            if ($existing) {
                return $existing;
            }

            /*
            |--------------------------------------------------------------------------
            | Generate cryptographically random public token
            |--------------------------------------------------------------------------
            |
            | 16 bytes = 128 bits.
            |
            */

            $publicToken = $this->generatePublicToken();

            /*
            |--------------------------------------------------------------------------
            | Create receipt
            |--------------------------------------------------------------------------
            */

            $receipt = Receipt::create([
                'source_system' =>
                    'PUSOPAY',

                'external_reference' =>
                    $data['externalReference'],

                'amount_minor' =>
                    $data['amountMinor'],

                'currency' =>
                    strtoupper($data['currency']),

                'transaction_type' =>
                    $data['transactionType'],

                'counterparty_label' =>
                    $data['counterpartyLabel'] ?? null,

                'occurred_at' =>
                    $data['occurredAt'],

                'public_token' =>
                    $publicToken,

                'expires_at' =>
                    now()->addDays(
                        config(
                            'eresibo.receipt_expiry_days',
                            90
                        )
                    ),

                'status' =>
                    Receipt::STATUS_CONFIRMED,

                'processed_at' =>
                    now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Queue webhook AFTER transaction commits
            |--------------------------------------------------------------------------
            */

            SendPusoPayReceiptWebhook::dispatch(
                $receipt->id
            )->afterCommit();

            return $receipt;
        });
    }

    private function generatePublicToken(): string
    {
        do {
            $token = rtrim(
                strtr(
                    base64_encode(
                        random_bytes(16)
                    ),
                    '+/',
                    '-_'
                ),
                '='
            );
        } while (
            Receipt::query()
                ->where(
                    'public_token',
                    $token
                )
                ->exists()
        );

        return $token;
    }
}