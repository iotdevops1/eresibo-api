<?php

namespace App\Repositories\DocumentVerification;

use App\Models\Payslip;
use App\Models\Receipt;
use Illuminate\Support\Str;

class DocumentVerificationRepository
{
    public function __construct(
        protected Payslip $payslip,
        protected Receipt $receipt,
    ) {}

    public function findPayslipByUuid(string $uuid): ?Payslip
    {
        return $this->payslip->newQuery()
            ->with('merchant')
            ->where('uuid', $uuid)
            ->first();
    }

    public function findUniqueReceiptByReference(string $reference): ?Receipt
    {
        $receipts = $this->receipt->newQuery()
            ->where(function ($query) use ($reference) {
                $query->where('external_reference', $reference)
                    ->orWhere('public_token', $reference);

                if (Str::isUuid($reference)) {
                    $query->orWhere('uuid', strtolower($reference));
                }
            })
            ->limit(2)
            ->get();

        // Fail closed when identifiers match different documents.
        return $receipts->count() === 1 ? $receipts->first() : null;
    }
}
