<?php

namespace App\Services\Insights;

use App\Models\Dispute;
use App\Models\Payslip;
use App\Models\PayslipLine;
use App\Models\User;
use App\Repositories\Insights\EmployerInsightsRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class EmployerInsightsService
{
    public function __construct(protected EmployerInsightsRepository $repository)
    {
    }

    public function overview(User $user, array $filters): array
    {
        [$merchantId, $start, $end] = $this->context($user, $filters);
        $payslips = $this->repository->payslips($merchantId, $start, $end);
        $acknowledged = $payslips->where('status', Payslip::STATUS_ACKNOWLEDGED);

        return [
            'period' => $this->periodData($start, $end, $filters),
            'summary' => [
                'total_disbursed' => $this->amount($acknowledged->sum('net_amount_minor_units')),
                'payslips_issued' => [
                    'total' => $payslips->count(),
                    'acknowledged' => $acknowledged->count(),
                    'pending' => $payslips->count() - $acknowledged->count(),
                ],
                'open_disputes' => $this->repository->openDisputesCount($merchantId),
                'active_employees' => $this->repository->activeEmployeesCount($merchantId),
            ],
            'payroll_over_time' => $this->monthlySeries($payslips, $start, $end, fn ($payslip) => $payslip->status === Payslip::STATUS_ACKNOWLEDGED ? $payslip->net_amount_minor_units : 0),
            'disbursements_by_type' => [
                'items' => [],
                'empty_message' => 'No assistance released in this period.',
            ],
        ];
    }

    public function payrollDeepDive(User $user, array $filters): array
    {
        [$merchantId, $start, $end] = $this->context($user, $filters);
        $payslips = $this->repository->payslips($merchantId, $start, $end);
        $acknowledged = $payslips->where('status', Payslip::STATUS_ACKNOWLEDGED);
        $disputes = $this->repository->disputes($merchantId, $start, $end);
        $yearStart = now()->startOfYear();
        $yearToDate = $this->repository->payslips($merchantId, $yearStart, now());

        return [
            'period' => $this->periodData($start, $end, $filters),
            'metrics' => [
                'acknowledgement_rate_percent' => $this->percentage($acknowledged->count(), $payslips->count()),
                'average_acknowledgement_hours' => $this->averageAcknowledgementHours($acknowledged),
                'dispute_rate_percent' => $this->percentage($disputes->count(), $payslips->count()),
                'correction_rate_percent' => 0,
                'corrections_count' => 0,
            ],
            'year_to_date_payroll' => [
                'net' => $this->amount($yearToDate->sum('net_amount_minor_units')),
                'gross' => $this->amount($yearToDate->sum('gross_amount_minor_units')),
                'deductions' => $this->amount($yearToDate->sum('deduction_amount_minor_units')),
            ],
            'payroll_trend' => $this->monthlySeries($payslips, $start, $end, fn ($payslip) => $payslip->net_amount_minor_units, true),
            'earnings_breakdown' => $this->lineBreakdown($payslips, PayslipLine::TYPE_EARNING),
            'deductions_breakdown' => $this->lineBreakdown($payslips, PayslipLine::TYPE_DEDUCTION),
            'cost_by_department' => $this->departmentBreakdown($payslips),
        ];
    }

    public function payslips(User $user, array $filters): array
    {
        [, $start, $end] = $this->context($user, $filters);
        [$merchantId] = $this->context($user, $filters);
        $payslips = $this->repository->payslips($merchantId, $start, $end);
        $acknowledged = $payslips->where('status', Payslip::STATUS_ACKNOWLEDGED);

        return [
            'period' => $this->periodData($start, $end, $filters),
            'issued_vs_acknowledged' => $this->monthlyIssuedSeries($payslips, $start, $end),
            'acknowledgement_status' => [
                'total' => $payslips->count(),
                'acknowledged' => $acknowledged->count(),
                'awaiting_acknowledgement' => $payslips->count() - $acknowledged->count(),
            ],
            'template_usage' => [
                'items' => [['name' => 'No template (classic)', 'count' => $payslips->count()]],
            ],
        ];
    }

    public function fundHolds(User $user, array $filters): array
    {
        [, $start, $end] = $this->context($user, $filters);

        return [
            'period' => $this->periodData($start, $end, $filters),
            'summary' => ['total_holds' => 0, 'active_holds' => 0, 'amount' => $this->amount(0)],
            'items' => [],
            'empty_message' => 'No fund holds exist for this period.',
        ];
    }

    private function context(User $user, array $filters): array
    {
        if (! $user->merchant_id) {
            throw ValidationException::withMessages([
                'merchant' => ['Employer is not assigned to a merchant.'],
            ]);
        }

        $range = $filters['range'] ?? 'LAST_90_DAYS';
        $end = now()->endOfDay();
        $start = match ($range) {
            'LAST_30_DAYS' => now()->subDays(29)->startOfDay(),
            'LAST_6_MONTHS' => now()->subMonths(5)->startOfMonth(),
            'LAST_12_MONTHS' => now()->subMonths(11)->startOfMonth(),
            default => now()->subDays(89)->startOfDay(),
        };

        return [$user->merchant_id, $start, $end];
    }

    private function periodData(Carbon $start, Carbon $end, array $filters): array
    {
        return [
            'range' => $filters['range'] ?? 'LAST_90_DAYS',
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];
    }

    private function amount(int|float $minor): array
    {
        return [
            'minor_units' => (int) $minor,
            'major_units' => number_format($minor / 100, 2, '.', ''),
            'currency' => 'PHP',
        ];
    }

    private function percentage(int $part, int $whole): float
    {
        return $whole === 0 ? 0 : round(($part / $whole) * 100, 1);
    }

    private function averageAcknowledgementHours(Collection $payslips): float
    {
        $hours = $payslips
            ->filter(fn ($payslip) => $payslip->acknowledged_at)
            ->map(fn ($payslip) => $payslip->created_at->diffInMinutes($payslip->acknowledged_at) / 60);

        return $hours->isEmpty() ? 0 : round($hours->average(), 1);
    }

    private function monthlySeries(Collection $payslips, Carbon $start, Carbon $end, callable $value, bool $includeGrossAndDeductions = false): array
    {
        $months = collect();
        $cursor = $start->copy()->startOfMonth();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $items = $payslips->filter(fn ($payslip) => $payslip->pay_date->format('Y-m') === $key);
            $row = [
                'month' => $key,
                'label' => $cursor->format('M Y'),
                'net' => $this->amount($items->sum($value)),
            ];

            if ($includeGrossAndDeductions) {
                $row['gross'] = $this->amount($items->sum('gross_amount_minor_units'));
                $row['deductions'] = $this->amount($items->sum('deduction_amount_minor_units'));
            }

            $months->push($row);
            $cursor->addMonth();
        }

        return $months->all();
    }

    private function monthlyIssuedSeries(Collection $payslips, Carbon $start, Carbon $end): array
    {
        $cursor = $start->copy()->startOfMonth();
        $months = [];

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $items = $payslips->filter(fn ($payslip) => $payslip->pay_date->format('Y-m') === $key);
            $months[] = [
                'month' => $key,
                'label' => $cursor->format('M Y'),
                'issued' => $items->count(),
                'acknowledged' => $items->where('status', Payslip::STATUS_ACKNOWLEDGED)->count(),
            ];
            $cursor->addMonth();
        }

        return $months;
    }

    private function lineBreakdown(Collection $payslips, string $type): array
    {
        return $payslips->flatMap->lines
            ->where('line_type', $type)
            ->groupBy('description')
            ->map(fn ($lines, $description) => [
                'description' => $description,
                'amount' => $this->amount($lines->sum('amount_minor_units')),
            ])
            ->sortByDesc(fn ($item) => $item['amount']['minor_units'])
            ->values()
            ->all();
    }

    private function departmentBreakdown(Collection $payslips): array
    {
        return $payslips
            ->groupBy(fn ($payslip) => $payslip->employee?->department ?: 'Unassigned')
            ->map(fn ($items, $department) => [
                'department' => $department,
                'net' => $this->amount($items->sum('net_amount_minor_units')),
                'payslip_count' => $items->count(),
            ])
            ->sortByDesc(fn ($item) => $item['net']['minor_units'])
            ->values()
            ->all();
    }
}
