<?php

namespace App\Services\DocumentVerification;

use App\Models\Payslip;
use App\Models\Receipt;
use App\Repositories\DocumentVerification\DocumentVerificationRepository;
use Illuminate\Support\Str;

class DocumentVerificationService
{
    public function __construct(protected DocumentVerificationRepository $documentVerificationRepository) {}

    /**
     * @return array{state: 'verified'|'not_found'|'expired', document: Payslip|Receipt|null}
     */
    public function verify(string $reference): array
    {
        $reference = trim($reference);

        if (preg_match('/^PAYSLIP-([0-9a-f-]{36})$/i', $reference, $matches) && Str::isUuid($matches[1])) {
            $payslip = $this->documentVerificationRepository->findPayslipByUuid(strtolower($matches[1]));

            if (! $payslip || ! in_array($payslip->status, [
                Payslip::STATUS_PENDING_ACKNOWLEDGEMENT,
                Payslip::STATUS_ACKNOWLEDGED,
            ], true)) {
                return ['state' => 'not_found', 'document' => null];
            }

            return ['state' => 'verified', 'document' => $payslip];
        }

        $receipt = $this->documentVerificationRepository->findUniqueReceiptByReference($reference);

        if (! $receipt || ! $receipt->isConfirmed()) {
            return ['state' => 'not_found', 'document' => null];
        }

        if (! $receipt->expires_at || $receipt->expires_at->isPast()) {
            return ['state' => 'expired', 'document' => null];
        }

        return ['state' => 'verified', 'document' => $receipt];
    }
}
