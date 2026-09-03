<?php

namespace App\Http\Controllers\Api\V1\Internal;

use App\Http\Controllers\BaseApiController;
use App\Models\Receipt;
use Illuminate\Http\JsonResponse;

class ReceiptLookupController extends BaseApiController
{
    public function show(string $token): JsonResponse
    {
        $receipt = Receipt::query()
            ->where('public_token', $token)
            ->where('status', Receipt::STATUS_CONFIRMED)
            ->whereNull('deleted_at')
            ->first();

        if (! $receipt) {
            return response()->json([
                'success' => false,
                'message' => 'Receipt not found.',
            ], 404);
        }

        if (
            ! $receipt->expires_at ||
            $receipt->expires_at->isPast()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Receipt has expired.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Receipt retrieved successfully.',
            'data' => [
                'receiptId' => $receipt->uuid,

                'externalReference' =>
                    $receipt->external_reference,

                'amountMinor' =>
                    $receipt->amount_minor,

                'currency' =>
                    $receipt->currency,

                'transactionType' =>
                    $receipt->transaction_type,

                'counterpartyLabel' =>
                    $receipt->counterparty_label,

                'occurredAt' =>
                    $receipt->occurred_at?->toISOString(),

                'expiresAt' =>
                    $receipt->expires_at?->toISOString(),
            ],
        ]);
    }
}