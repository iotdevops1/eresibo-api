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

            return $this->verifyPayslip($payslip);
        }

        // Older payslip pages printed only the UUID before acknowledgement.
        $payslip = Str::isUuid($reference)
            ? $this->documentVerificationRepository->findPayslipByUuid(strtolower($reference))
            : null;
        $receipts = $this->documentVerificationRepository->findReceiptMatchesByReference($reference);

        // Never guess whether an ambiguous unprefixed ID means a receipt or a payslip.
        if ($receipts->count() > 1 || ($payslip && $receipts->isNotEmpty())) {
            return ['state' => 'not_found', 'document' => null];
        }

        if ($payslip) {
            return $this->verifyPayslip($payslip);
        }

        $receipt = $receipts->first();

        if (! $receipt || ! $receipt->isConfirmed()) {
            return ['state' => 'not_found', 'document' => null];
        }

        if (! $receipt->expires_at || $receipt->expires_at->isPast()) {
            return ['state' => 'expired', 'document' => null];
        }

        return ['state' => 'verified', 'document' => $receipt];
    }

    /**
     * @return array{state: 'verified'|'not_found', document: Payslip|null}
     */
    private function verifyPayslip(?Payslip $payslip): array
    {
        if (! $payslip || ! in_array($payslip->status, [
            Payslip::STATUS_PENDING_ACKNOWLEDGEMENT,
            Payslip::STATUS_ACKNOWLEDGED,
        ], true)) {
            return ['state' => 'not_found', 'document' => null];
        }

        return ['state' => 'verified', 'document' => $payslip];
    }
}
