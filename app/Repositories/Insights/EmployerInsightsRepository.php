<?php

namespace App\Repositories\Insights;

use App\Models\Dispute;
use App\Models\Employee;
use App\Models\Payslip;
use Carbon\Carbon;

class EmployerInsightsRepository
{
    public function payslips(int $merchantId, Carbon $start, Carbon $end)
    {
        return Payslip::query()
            ->where('merchant_id', $merchantId)
            ->whereBetween('pay_date', [$start->toDateString(), $end->toDateString()])
            ->with(['lines', 'employee'])
            ->orderBy('pay_date')
            ->get();
    }

    public function disputes(int $merchantId, Carbon $start, Carbon $end)
    {
        return Dispute::query()
            ->where('merchant_id', $merchantId)
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->get();
    }

    public function openDisputesCount(int $merchantId): int
    {
        return Dispute::query()
            ->where('merchant_id', $merchantId)
            ->whereIn('status', [Dispute::STATUS_OPEN, Dispute::STATUS_PENDING])
            ->count();
    }

    public function activeEmployeesCount(int $merchantId): int
    {
        return Employee::query()
            ->where('merchant_id', $merchantId)
            ->where('status', Employee::STATUS_ACTIVE)
            ->count();
    }
}
