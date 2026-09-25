<?php

namespace App\Repositories\DocumentVerification;

use App\Models\Payslip;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Collection;
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

    /**
     * At most two matches are needed to distinguish a unique receipt from a collision.
     *
     * @return Collection<int, Receipt>
     */
    public function findReceiptMatchesByReference(string $reference): Collection
    {
        return $this->receipt->newQuery()
            ->where(function ($query) use ($reference) {
                $query->where('external_reference', $reference)
                    ->orWhere('public_token', $reference);

                if (Str::isUuid($reference)) {
                    $query->orWhere('uuid', strtolower($reference));
                }
            })
            ->limit(2)
            ->get();

    }
}
