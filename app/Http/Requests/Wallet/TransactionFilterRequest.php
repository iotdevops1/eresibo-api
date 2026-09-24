<?php

namespace App\Http\Requests\Wallet;

use App\Models\WalletTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', Rule::in([
                WalletTransaction::TYPE_PREFUND,
                WalletTransaction::TYPE_TRANSFER,
                WalletTransaction::TYPE_PAYROLL,
                WalletTransaction::TYPE_PAYOUT,
                WalletTransaction::TYPE_CASH_IN,
                WalletTransaction::TYPE_CASH_OUT,
                WalletTransaction::TYPE_REFUND,
                WalletTransaction::TYPE_FEE,
                WalletTransaction::TYPE_ADJUSTMENT,
                WalletTransaction::TYPE_FUNDING,
            ])],
            'status' => ['nullable', 'integer', Rule::in([
                WalletTransaction::STATUS_PENDING,
                WalletTransaction::STATUS_COMPLETED,
                WalletTransaction::STATUS_FAILED,
                WalletTransaction::STATUS_CANCELLED,
            ])],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
