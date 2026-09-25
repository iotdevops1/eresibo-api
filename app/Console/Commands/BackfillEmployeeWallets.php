<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use App\Repositories\Wallet\WalletRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillEmployeeWallets extends Command
{
    protected $signature = 'wallets:backfill-employees
                            {--dry-run : Preview missing wallets without writing any records}';

    protected $description = 'Create missing zero-balance PHP wallets for active employees with valid active accounts';

    public function handle(WalletRepository $walletRepository): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $counts = ['created' => 0, 'would_create' => 0, 'existing' => 0, 'skipped' => 0];

        Employee::query()->select('id')->chunkById(200, function ($employees) use ($walletRepository, $dryRun, &$counts) {
            foreach ($employees as $employee) {
                // Each employee is atomic; interrupted runs can be safely repeated.
                $result = DB::transaction(function () use ($employee, $walletRepository, $dryRun) {
                    $employee = Employee::query()->whereKey($employee->id)->lockForUpdate()->first();

                    if (! $employee || $employee->status !== Employee::STATUS_ACTIVE || ! $employee->user_id) {
                        return 'skipped';
                    }

                    $user = User::query()->with('role')->whereKey($employee->user_id)->lockForUpdate()->first();

                    if (! $user || (int) $user->status !== User::STATUS_ACTIVE || $user->is_lock
                        || $user->role?->code !== 'EMPLOYEE' || ! $employee->merchant_id
                        || (int) $user->merchant_id !== (int) $employee->merchant_id) {
                        return 'skipped';
                    }

                    $wallet = $walletRepository->findUserWallet($user->id, 'PHP', includeDeleted: true);

                    if ($wallet) {
                        return $wallet->trashed() ? 'skipped' : 'existing';
                    }

                    if ($dryRun) {
                        return 'would_create';
                    }

                    $wallet = $walletRepository->firstOrCreateUserWallet(
                        $user->id,
                        'Employee wallet - '.$employee->employee_no,
                    );

                    if ($wallet->trashed()) {
                        return 'skipped';
                    }

                    return $wallet->wasRecentlyCreated ? 'created' : 'existing';
                });

                $counts[$result]++;
            }
        });

        $this->info($dryRun ? 'Dry run complete. No records were changed.' : 'Employee wallet backfill complete.');
        $this->line('Employees scanned: '.array_sum($counts));
        $this->line($dryRun ? 'Would create: '.$counts['would_create'] : 'Created: '.$counts['created']);
        $this->line('Existing wallets left unchanged: '.$counts['existing']);
        $this->line('Skipped (ineligible employee/account or deleted wallet): '.$counts['skipped']);

        return self::SUCCESS;
    }
}
