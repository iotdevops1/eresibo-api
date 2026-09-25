<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Merchant;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wallet;
use App\Repositories\Wallet\WalletRepository;
use App\Services\Employee\EmployeeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class EmployeeWalletProvisioningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'database.connections.sqlite.foreign_key_constraints' => true,
            'cache.default' => 'array',
        ]);
        DB::purge('sqlite');

        // Modern account/profile fixtures; wallets use the actual production migrations.
        (require database_path('migrations/0001_01_01_000000_create_user_roles_table.php'))->up();
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('business_name');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('role_id')->constrained('user_roles');
            $table->foreignId('merchant_id')->nullable()->constrained('merchants');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile')->nullable();
            $table->string('password');
            $table->unsignedTinyInteger('status')->default(1);
            $table->boolean('is_lock')->default(false);
            $table->boolean('must_change_password')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('merchant_id')->constrained('merchants');
            $table->foreignId('user_id')->nullable()->unique()->constrained('users');
            $table->string('employee_no');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->string('pusopay_wallet_id')->unique();
            $table->unsignedTinyInteger('status')->default(1);
            $table->date('hired_at')->nullable();
            $table->date('terminated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['merchant_id', 'employee_no']);
        });
        foreach ([
            '2026_09_04_043729_create_wallets_table.php',
            '2026_09_04_084553_add_name_to_wallets_table.php',
            '2026_09_04_084735_add_unique_owner_currency_to_wallets_table.php',
            '2026_09_04_044227_create_wallet_transactions_table.php',
            '2026_09_04_044632_create_wallet_entries_table.php',
            '2026_09_24_040000_create_fund_holds_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        foreach (['EMPLOYEE', 'EMPLOYER', 'ADMIN'] as $code) {
            UserRole::create(['code' => $code, 'name' => $code]);
        }
        Merchant::create(['business_name' => 'Merchant One']);
        Merchant::create(['business_name' => 'Merchant Two']);
    }

    private function payload(array $overrides = []): array
    {
        $identifier = (string) Str::uuid();

        return array_replace([
            'employee_no' => $identifier,
            'first_name' => 'Jane',
            'last_name' => 'Employee',
            'email' => $identifier.'@example.test',
            'temporaryPassword' => 'temporary-password',
            'pusopay_wallet_id' => 'PUSO-'.$identifier,
            'status' => Employee::STATUS_ACTIVE,
        ], $overrides);
    }

    private function user(array $overrides = []): User
    {
        return User::create(array_replace([
            'role_id' => UserRole::where('code', 'EMPLOYEE')->value('id'),
            'merchant_id' => 1,
            'name' => 'Employee Account',
            'email' => Str::uuid().'@example.test',
            'password' => 'test-password',
            'status' => User::STATUS_ACTIVE,
            'is_lock' => false,
        ], $overrides));
    }

    private function legacyEmployee(array $overrides = [], array $userOverrides = []): Employee
    {
        $user = $this->user($userOverrides);
        $data = $this->payload();
        unset($data['temporaryPassword']);

        return Employee::create(array_replace($data, [
            'user_id' => $user->id,
            'merchant_id' => 1,
        ], $overrides));
    }

    private function authenticate(string $roleCode, string $permission): User
    {
        $role = UserRole::where('code', $roleCode)->firstOrFail();
        $role->setRelation('permissions', new Collection([new Permission(['code' => $permission])]));
        $user = $this->user(['role_id' => $role->id]);
        $user->setRelation('role', $role);
        Sanctum::actingAs($user);

        return $user;
    }

    public function test_team_api_creates_employee_and_zero_balance_wallet_atomically(): void
    {
        $employer = $this->authenticate('EMPLOYER', 'team.create');
        $payload = $this->payload(['merchant_id' => 2]);

        $response = $this->postJson('/api/employer/team', $payload)
            ->assertCreated()->assertJsonPath('success', true);
        $employee = Employee::where('uuid', $response->json('data.uuid'))->firstOrFail();
        $wallet = app(WalletRepository::class)->findUserWallet($employee->user_id);

        $this->assertSame(1, (int) $employee->merchant_id);
        $this->assertNotEquals($employer->id, $wallet->owner_id);
        $this->assertSame(Wallet::OWNER_TYPE_USER, $wallet->owner_type);
        $this->assertSame('PHP', $wallet->currency);
        $this->assertSame(Wallet::STATUS_ACTIVE, $wallet->status);
        $this->assertSame(0, $wallet->balance_minor_units);
        $this->assertSame('0.00', $wallet->balance_major_units);
        $this->assertSame(0, $wallet->held_minor_units);
        $this->assertSame('0.00', $wallet->held_major_units);
        $this->assertSame($payload['pusopay_wallet_id'], $employee->pusopay_wallet_id);
        $this->assertDatabaseCount('wallet_transactions', 0);
        $this->assertDatabaseCount('wallet_entries', 0);

        Sanctum::actingAs($employee->user);
        $this->getJson('/api/employee/wallet')->assertOk()
            ->assertJsonPath('data.wallet_uuid', $wallet->uuid)
            ->assertJsonPath('data.status', 'ACTIVE')
            ->assertJsonPath('data.available_balance_minor_units', 0);
    }

    public function test_admin_employee_creation_also_provisions_wallet_for_selected_merchant(): void
    {
        $this->authenticate('ADMIN', 'management.create');
        $merchant = Merchant::findOrFail(2);

        $response = $this->postJson('/api/admin/merchants/'.$merchant->uuid.'/employees', $this->payload())
            ->assertCreated();
        $employee = Employee::where('uuid', $response->json('data.uuid'))->firstOrFail();

        $this->assertSame(2, (int) $employee->merchant_id);
        $this->assertSame(2, (int) $employee->user->merchant_id);
        $this->assertDatabaseHas('wallets', [
            'owner_type' => 'user', 'owner_id' => $employee->user_id,
            'currency' => 'PHP', 'balance_minor_units' => 0, 'status' => Wallet::STATUS_ACTIVE,
        ]);
    }

    public function test_non_active_new_employees_receive_inactive_zero_balance_wallets(): void
    {
        foreach ([Employee::STATUS_INACTIVE, Employee::STATUS_SUSPENDED, Employee::STATUS_TERMINATED] as $status) {
            $employee = app(EmployeeService::class)->store(1, $this->payload(['status' => $status]));
            $wallet = app(WalletRepository::class)->findUserWallet($employee->user_id);

            $this->assertSame(Wallet::STATUS_INACTIVE, $wallet->status);
            $this->assertSame(0, $wallet->balance_minor_units);
        }
    }

    public function test_wallet_failure_rolls_back_both_login_and_employee_profile(): void
    {
        $repository = Mockery::mock(WalletRepository::class);
        $repository->shouldReceive('firstOrCreateUserWallet')->once()
            ->andThrow(new RuntimeException('Simulated wallet insert failure.'));
        $this->app->instance(WalletRepository::class, $repository);

        try {
            app(EmployeeService::class)->store(1, $this->payload());
            $this->fail('Expected wallet creation to fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated wallet insert failure.', $exception->getMessage());
        }

        $this->assertDatabaseCount('employees', 0);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('wallets', 0);
    }

    public function test_profile_failure_does_not_leave_an_orphan_account_or_wallet(): void
    {
        app(EmployeeService::class)->store(1, $this->payload(['employee_no' => 'EMP-1']));
        try {
            app(EmployeeService::class)->store(1, $this->payload(['employee_no' => 'EMP-1']));
            $this->fail('Expected duplicate employee number to fail.');
        } catch (QueryException) {
            $this->assertDatabaseCount('employees', 1);
            $this->assertDatabaseCount('users', 1);
            $this->assertDatabaseCount('wallets', 1);
        }
    }

    public function test_backfill_preview_writes_nothing_and_repeated_runs_are_idempotent(): void
    {
        $employee = $this->legacyEmployee();
        $before = $employee->getRawOriginal();

        $this->artisan('wallets:backfill-employees', ['--dry-run' => true])
            ->expectsOutput('Would create: 1')->assertSuccessful();
        $this->assertDatabaseCount('wallets', 0);

        $this->artisan('wallets:backfill-employees')->expectsOutput('Created: 1')->assertSuccessful();
        $wallet = Wallet::firstOrFail()->refresh();
        $snapshot = $wallet->getRawOriginal();
        $this->assertSame((int) $employee->user_id, (int) $wallet->owner_id);
        $this->assertSame(0, $wallet->balance_minor_units);
        $this->assertSame(0, $wallet->held_minor_units);
        $this->assertSame(Wallet::STATUS_ACTIVE, $wallet->status);

        $this->artisan('wallets:backfill-employees')->expectsOutput('Created: 0')
            ->expectsOutput('Existing wallets left unchanged: 1')->assertSuccessful();
        $this->assertSame($snapshot, $wallet->refresh()->getRawOriginal());
        $this->assertEquals($before, array_intersect_key($employee->refresh()->getRawOriginal(), $before));
        $this->assertDatabaseCount('wallets', 1);
        $this->assertDatabaseCount('wallet_transactions', 0);
        $this->assertDatabaseCount('wallet_entries', 0);
    }

    public function test_backfill_preserves_existing_balances_holds_statuses_and_deleted_wallets(): void
    {
        $repository = app(WalletRepository::class);
        foreach ([Wallet::STATUS_ACTIVE, Wallet::STATUS_INACTIVE, Wallet::STATUS_LOCKED, 'deleted'] as $status) {
            $employee = $this->legacyEmployee();
            $wallet = $repository->firstOrCreateUserWallet($employee->user_id, 'Keep this name');
            $wallet->update([
                'balance_minor_units' => 250000, 'balance_major_units' => '2500.00',
                'held_minor_units' => 50000, 'held_major_units' => '500.00',
                'status' => $status === 'deleted' ? Wallet::STATUS_LOCKED : $status,
            ]);
            if ($status === 'deleted') {
                $wallet->delete();
            }
            $returned = $repository->firstOrCreateUserWallet($employee->user_id, 'Do not replace');
            $this->assertSame($wallet->id, $returned->id);
        }
        $before = DB::table('wallets')->orderBy('id')->get();

        $this->artisan('wallets:backfill-employees')->expectsOutput('Created: 0')
            ->expectsOutput('Existing wallets left unchanged: 3')
            ->expectsOutput('Skipped (ineligible employee/account or deleted wallet): 1')
            ->assertSuccessful();

        $this->assertEquals($before, DB::table('wallets')->orderBy('id')->get());
        $this->assertDatabaseCount('wallets', 4);
    }

    public function test_backfill_skips_invalid_links_and_inactive_or_locked_accounts(): void
    {
        $this->legacyEmployee(['user_id' => null]);
        foreach ([Employee::STATUS_INACTIVE, Employee::STATUS_SUSPENDED, Employee::STATUS_TERMINATED] as $status) {
            $this->legacyEmployee(['status' => $status]);
        }
        foreach ([User::STATUS_INACTIVE, User::STATUS_SUSPENDED, User::STATUS_LOCKED, User::STATUS_DELETED] as $status) {
            $this->legacyEmployee([], ['status' => $status]);
        }
        $this->legacyEmployee([], ['is_lock' => true]);
        $this->legacyEmployee([], ['merchant_id' => 2]);
        $this->legacyEmployee([], ['role_id' => UserRole::where('code', 'EMPLOYER')->value('id')]);
        $deletedUserEmployee = $this->legacyEmployee();
        $deletedUserEmployee->user->delete();
        $deletedEmployee = $this->legacyEmployee();
        $deletedEmployee->delete();

        $this->artisan('wallets:backfill-employees')->expectsOutput('Created: 0')
            ->expectsOutput('Skipped (ineligible employee/account or deleted wallet): 12')
            ->assertSuccessful();
        $this->assertDatabaseCount('wallets', 0);
    }

    public function test_backfill_does_not_confuse_merchant_wallets_or_other_currencies_with_php_user_wallet(): void
    {
        $employee = $this->legacyEmployee();
        $repository = app(WalletRepository::class);
        $usd = $repository->firstOrCreateUserWallet($employee->user_id, 'USD wallet', currency: 'USD');
        $merchantWallet = Wallet::create([
            'owner_type' => Wallet::OWNER_TYPE_MERCHANT, 'owner_id' => $employee->user_id,
            'currency' => 'PHP', 'name' => 'Merchant wallet', 'balance_minor_units' => 50000,
            'balance_major_units' => '500.00', 'status' => Wallet::STATUS_ACTIVE,
        ]);
        $before = $merchantWallet->refresh()->getRawOriginal();

        $this->artisan('wallets:backfill-employees')->expectsOutput('Created: 1')->assertSuccessful();

        $php = $repository->findUserWallet($employee->user_id);
        $this->assertNotEquals($usd->id, $php->id);
        $this->assertNotEquals($merchantWallet->id, $php->id);
        $this->assertSame($before, $merchantWallet->refresh()->getRawOriginal());
        $this->assertDatabaseCount('wallets', 3);
    }

    public function test_backfill_processes_more_than_one_batch(): void
    {
        for ($index = 0; $index < 201; $index++) {
            $this->legacyEmployee();
        }

        $this->artisan('wallets:backfill-employees')->expectsOutput('Employees scanned: 201')
            ->expectsOutput('Created: 201')->assertSuccessful();
        $this->assertDatabaseCount('wallets', 201);
        $this->assertSame(0, (int) Wallet::sum('balance_minor_units'));
    }
}
