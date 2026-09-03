<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function show(
        Request $request,
        string $token
    ) {
        $receipt = Receipt::query()
            ->where('public_token', $token)
            ->where('status', Receipt::STATUS_CONFIRMED)
            ->firstOrFail();

        if ($receipt->expires_at->isPast()) {
            abort(404);
        }

        return response()
            ->view('receipts.show', [
                'receipt' => $receipt,
            ])
            ->header(
                'X-Robots-Tag',
                'noindex, nofollow'
            )
            ->header(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, max-age=0'
            )
            ->header(
                'Pragma',
                'no-cache'
            )
            ->header(
                'Expires',
                '0'
            );
    }
}