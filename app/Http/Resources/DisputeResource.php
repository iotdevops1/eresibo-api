<?php

namespace App\Http\Resources;

use App\Models\Dispute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewerId = $request->user()?->id;

        return [
            'uuid' => $this->uuid,
            'reference' => $this->reference,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => [
                'id' => $this->status,
                'name' => $this->statusName(),
                'note' => $this->statusNote(),
            ],
            'priority' => [
                'id' => $this->priority,
                'name' => match ($this->priority) {
                    Dispute::PRIORITY_LOW => 'LOW',
                    Dispute::PRIORITY_NORMAL => 'NORMAL',
                    Dispute::PRIORITY_HIGH => 'HIGH',
                    default => 'UNKNOWN',
                },
            ],
            'merchant' => $this->whenLoaded('merchant', fn () => $this->merchant ? [
                'uuid' => $this->merchant->uuid,
                'merchant_code' => $this->merchant->merchant_code,
                'business_name' => $this->merchant->business_name,
            ] : null),
            'employee' => $this->whenLoaded('employee', fn () => $this->employee ? [
                'uuid' => $this->employee->uuid,
                'employee_no' => $this->employee->employee_no,
                'name' => $this->employee->full_name,
            ] : null),
            'payslip' => $this->whenLoaded('payslip', fn () => $this->payslip ? [
                'uuid' => $this->payslip->uuid,
                'pay_date' => $this->payslip->pay_date?->format('Y-m-d'),
                'net_amount_minor_units' => $this->payslip->net_amount_minor_units,
                'currency' => $this->payslip->currency,
            ] : null),
            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy ? [
                'uuid' => $this->createdBy->uuid,
                'name' => $this->createdBy->name,
            ] : null),
            'resolved_by' => $this->whenLoaded('resolvedBy', fn () => $this->resolvedBy ? [
                'uuid' => $this->resolvedBy->uuid,
                'name' => $this->resolvedBy->name,
            ] : null),
            'resolved_at' => $this->resolved_at?->toISOString(),
            'can_reply' => $this->status !== Dispute::STATUS_CLOSED,
            'messages' => $this->whenLoaded(
                'messages',
                fn () => $this->messages->map(
                    fn ($message) => $this->messageData($message, $viewerId)
                )->values()
            ),
            'conversation' => $this->whenLoaded('messages', function () use ($viewerId) {
                $initialMessage = [
                    'uuid' => null,
                    'kind' => 'INITIAL_MESSAGE',
                    'message' => $this->description,
                    'author' => $this->createdBy ? [
                        'uuid' => $this->createdBy->uuid,
                        'name' => $this->createdBy->name,
                    ] : null,
                    'is_current_user' => $this->created_by_user_id === $viewerId,
                    'created_at' => $this->created_at?->toISOString(),
                ];

                return collect([$initialMessage])
                    ->merge(
                        $this->messages->map(
                            fn ($message) => $this->messageData($message, $viewerId)
                        )
                    )
                    ->values();
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function statusName(): string
    {
        return match ($this->status) {
            Dispute::STATUS_OPEN => 'OPEN',
            Dispute::STATUS_PENDING => 'PENDING',
            Dispute::STATUS_RESOLVED => 'RESOLVED',
            Dispute::STATUS_CLOSED => 'CLOSED',
            default => 'UNKNOWN',
        };
    }

    private function statusNote(): string
    {
        return match ($this->status) {
            Dispute::STATUS_OPEN => 'Submitted and waiting for the employer to review.',
            Dispute::STATUS_PENDING => 'A reply has been added and the case is awaiting resolution.',
            Dispute::STATUS_RESOLVED => 'This case has been resolved.',
            Dispute::STATUS_CLOSED => 'This case has been closed.',
            default => 'Case status is unavailable.',
        };
    }

    private function messageData($message, ?int $viewerId): array
    {
        return [
            'uuid' => $message->uuid,
            'kind' => 'REPLY',
            'message' => $message->message,
            'author' => $message->user ? [
                'uuid' => $message->user->uuid,
                'name' => $message->user->name,
            ] : null,
            'is_current_user' => $message->user_id === $viewerId,
            'created_at' => $message->created_at?->toISOString(),
        ];
    }
}
