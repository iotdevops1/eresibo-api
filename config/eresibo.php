<?php

return [

    'receipt_expiry_days' => (int) env(
        'ERESIBO_RECEIPT_EXPIRY_DAYS',
        90
    ),

    'pusopay' => [

        'webhook_url' =>
            env(
                'PUSOPAY_ERESIBO_WEBHOOK_URL',
                'https://api.pusopay.ph/api/v1/webhooks/eresibo'
            ),

        'webhook_secret' =>
            env('PUSOPAY_ERESIBO_WEBHOOK_SECRET'),

        'webhook_timeout' =>
            (int) env(
                'PUSOPAY_ERESIBO_WEBHOOK_TIMEOUT',
                10
            ),

        'webhook_max_attempts' =>
            (int) env(
                'PUSOPAY_ERESIBO_WEBHOOK_MAX_ATTEMPTS',
                5
            ),
    ],

    'internal' => [
        'portal_key' => env(
            'ERESIBO_INTERNAL_PORTAL_KEY'
        ),
    ],
];