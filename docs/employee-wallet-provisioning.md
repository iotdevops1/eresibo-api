# Employee wallet provisioning

## New employees

Both `POST /api/employer/team` and `POST /api/admin/merchants/{merchantUuid}/employees` use `EmployeeService::store`.

The service creates the login account, employee profile, and local PHP wallet in the same database transaction. If wallet creation fails, the account and profile are rolled back too. Existing payload and response fields are unchanged.

The wallet uses `owner_type = user` and `owner_id = employees.user_id` (the login account ID, not the employee or merchant ID). Its balance and held amount start at zero. Active employees get active wallets; inactive, suspended, or terminated employees get inactive wallets. Existing employee/account statuses are not changed.

`pusopay_wallet_id` remains the externally supplied reference stored on the employee. This does not create a wallet in the external PusoPay system or move any funds. Salary funding still happens through the existing payslip acknowledgement flow.

## Existing employees without a wallet

Deploy the updated files, then preview the backfill inside the application container:

```bash
docker exec -it eresibo_api_app php artisan wallets:backfill-employees --dry-run
```

If the counts look correct, run:

```bash
docker exec -it eresibo_api_app php artisan wallets:backfill-employees
```

The command processes non-deleted employees in batches of 200 and reports created, existing, and skipped counts. It only creates wallets for active employees linked to non-deleted, active, unlocked EMPLOYEE accounts in the same merchant. Missing account links, mismatched merchants, other roles, and inactive accounts/employees are skipped. It does not create login accounts or guess account ownership from email.

Existing PHP user wallets are never modified, including their balances, held amounts, status, and name. Deleted wallets are skipped and never restored or replaced. Merchant wallets and other currencies are not altered. Each employee is processed atomically, and rerunning the command does not duplicate wallets. The existing owner/currency unique index is required and protects concurrent creation.

No new migration is required; the existing wallet schema, including held-balance columns and the owner/currency unique index, must already be migrated. The backfill is an explicit deployment step, not something that runs automatically during an API request.

## Verification

After employee creation or backfill, call `GET /api/employee/wallet` using that employee's bearer token. A newly provisioned active wallet should have a `wallet_uuid`, `currency: PHP`, `status: ACTIVE`, and zero available balance.

Provisioning never credits funds or creates a wallet transaction. Backfill exclusions need individual review; do not reactivate locked accounts or deleted wallets just to make a backfill include them.
