<?php

namespace App\Jobs;

use App\Models\Receipt;
use App\Models\ReceiptWebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendPusoPayReceiptWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public function __construct(
        public int $receiptId
    ) {
    }

    public function backoff(): array
    {
        return [
            60,
            300,
            900,
            3600,
        ];
    }

    public function handle(): void
    {
        $receipt = Receipt::query()->find($this->receiptId);

        if (! $receipt) {
            Log::warning(
                'PusoPay webhook skipped: receipt not found.',
                [
                    'receipt_id' => $this->receiptId,
                ]
            );

            return;
        }

        $endpoint = config('eresibo.pusopay.webhook_url');
        $secret = config('eresibo.pusopay.webhook_secret');
        $timeout = (int) config(
            'eresibo.pusopay.webhook_timeout',
            10
        );

        /*
        |--------------------------------------------------------------------------
        | Validate configuration
        |--------------------------------------------------------------------------
        */

        if (empty($endpoint)) {
            throw new \RuntimeException(
                'PusoPay webhook URL is not configured.'
            );
        }

        if (empty($secret)) {
            throw new \RuntimeException(
                'PusoPay webhook secret is not configured.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Create / retrieve delivery record
        |--------------------------------------------------------------------------
        */

        $delivery = ReceiptWebhookDelivery::query()
            ->firstOrCreate(
                [
                    'receipt_id' => $receipt->id,
                ],
                [
                    'endpoint' => $endpoint,
                    'status' => ReceiptWebhookDelivery::STATUS_PENDING,
                    'attempts' => 0,
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Already delivered
        |--------------------------------------------------------------------------
        */

        if ($delivery->isSent()) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Build payload
        |--------------------------------------------------------------------------
        */

        $payload = [
            'externalReference' => $receipt->external_reference,
            'eresiboReceiptId' => $receipt->uuid,
            'eresiboReceiptUrl' => $receipt->public_url,
            'status' => $receipt->status === Receipt::STATUS_CONFIRMED
                ? 'CONFIRMED'
                : 'FAILED',
        ];

        /*
        |--------------------------------------------------------------------------
        | Encode exact JSON body
        |--------------------------------------------------------------------------
        */

        $rawBody = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES
        );

        if ($rawBody === false) {
            throw new \RuntimeException(
                'Unable to encode PusoPay webhook payload.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate HMAC-SHA256 signature
        |--------------------------------------------------------------------------
        */

        $signature = hash_hmac(
            'sha256',
            $rawBody,
            $secret
        );

        /*
        |--------------------------------------------------------------------------
        | Increment attempt
        |--------------------------------------------------------------------------
        */

        $attemptNumber = $delivery->attempts + 1;

        $delivery->update([
            'attempts' => $attemptNumber,
            'last_attempted_at' => now(),
            'status' => ReceiptWebhookDelivery::STATUS_PENDING,
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Send webhook
            |--------------------------------------------------------------------------
            */

            $response = Http::timeout($timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Eresibo-Signature' => $signature,
                ])
                ->withBody(
                    $rawBody,
                    'application/json'
                )
                ->post($delivery->endpoint);

            /*
            |--------------------------------------------------------------------------
            | Store response
            |--------------------------------------------------------------------------
            */

            $delivery->update([
                'last_http_status' => $response->status(),
                'last_response' => mb_substr(
                    $response->body(),
                    0,
                    5000
                ),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            if ($response->successful()) {
                $delivery->update([
                    'status' => ReceiptWebhookDelivery::STATUS_SENT,
                    'delivered_at' => now(),
                    'next_attempt_at' => null,
                ]);

                Log::info(
                    'PusoPay receipt webhook delivered successfully.',
                    [
                        'receipt_id' => $receipt->id,
                        'delivery_id' => $delivery->id,
                        'attempts' => $attemptNumber,
                        'http_status' => $response->status(),
                    ]
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Failed HTTP response
            |--------------------------------------------------------------------------
            */

            $nextAttemptAt = null;

            if ($attemptNumber < $this->tries) {
                $backoff = $this->backoff();
                $delay = $backoff[$attemptNumber - 1]
                    ?? end($backoff);

                $nextAttemptAt = now()->addSeconds($delay);
            }

            $delivery->update([
                'status' => ReceiptWebhookDelivery::STATUS_FAILED,
                'next_attempt_at' => $nextAttemptAt,
            ]);

            throw new \RuntimeException(
                'PusoPay webhook returned HTTP '
                . $response->status()
            );
        } catch (\Throwable $e) {
            /*
            |--------------------------------------------------------------------------
            | Record retry timing for transport exceptions
            |--------------------------------------------------------------------------
            */

            $nextAttemptAt = null;

            if ($attemptNumber < $this->tries) {
                $backoff = $this->backoff();
                $delay = $backoff[$attemptNumber - 1]
                    ?? end($backoff);

                $nextAttemptAt = now()->addSeconds($delay);
            }

            $delivery->update([
                'status' => ReceiptWebhookDelivery::STATUS_FAILED,
                'next_attempt_at' => $nextAttemptAt,
            ]);

            Log::warning(
                'PusoPay receipt webhook delivery failed.',
                [
                    'receipt_id' => $receipt->id,
                    'delivery_id' => $delivery->id,
                    'attempts' => $attemptNumber,
                    'next_attempt_at' => $nextAttemptAt,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }
}