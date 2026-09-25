<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Integration\PusoPayReceiptController;
use App\Http\Controllers\Api\V1\Internal\ReceiptLookupController;
use App\Http\Controllers\Api\V1\Public\VerifyDocumentController;
use App\Http\Controllers\ReceiptController;


use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\ChangePasswordController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\SettingsController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\ModuleController;

use App\Http\Controllers\Api\Admin\MerchantController;
use App\Http\Controllers\Api\Admin\MerchantEmployerController;
use App\Http\Controllers\Api\Admin\MerchantEmployeeController;
use App\Http\Controllers\Api\Admin\DocumentVaultController as AdminDocumentVaultController;
use App\Http\Controllers\Api\Admin\MerchantWalletController;

use App\Http\Controllers\Api\V1\Integration\PusoPayFundingController;

// Employer
use App\Http\Controllers\Api\Employer\TeamController;
use App\Http\Controllers\Api\Employer\PayrollBatchController;
use App\Http\Controllers\Api\Employer\PayslipController;
use App\Http\Controllers\Api\Employer\DocumentVaultController as EmployerDocumentVaultController;
use App\Http\Controllers\Api\Employer\InsightController;
use App\Http\Controllers\Api\Employer\WalletController as EmployerWalletController;
use App\Http\Controllers\Api\Employer\ReportController;
use App\Http\Controllers\Api\Employer\DisputeController;
use App\Http\Controllers\Api\Employer\DisbursementController;
use App\Http\Controllers\Api\Employer\FundHoldController;
use App\Http\Controllers\Api\Employer\AuditLogController;
use App\Http\Controllers\Api\Employee\PayslipAcknowledgementController;
use App\Http\Controllers\Api\Employee\PayslipController as EmployeePayslipController;
use App\Http\Controllers\Api\Employee\WalletController;
use App\Http\Controllers\Api\Employee\CaseController;
use App\Http\Controllers\Api\Employee\DocumentVaultController;
use App\Http\Controllers\Api\Wallet\TransactionController;

// Public document verification (no login or integration key required).
Route::get('/v1/public/documents/verify', VerifyDocumentController::class)
    ->middleware('throttle:60,1');

// Integrations
Route::prefix('v1/integrations/pusopay')->group(function () {
    Route::post('/receipts', [PusoPayReceiptController::class, 'store'])
        ->middleware('integration.api_key:receipts.create');
    Route::get('/receipts', [PusoPayReceiptController::class, 'lookup'])
        ->middleware('integration.api_key:receipts.read');
});

Route::middleware('integration.api_key:funding.confirm')->prefix('v1/integrations/pusopay')->group(function () {
    Route::post('/fundings/confirm', [PusoPayFundingController::class, 'confirm']);
});

// Internal API for Portal
Route::prefix('v1/internal')->middleware('internal.api')->group(function () {
    Route::get('/receipts/{token}', [ReceiptLookupController::class, 'show']);
});


// Auth
Route::prefix('auth')->group(function () {
    Route::post('/login', LoginController::class);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',      MeController::class);
        Route::post('/logout', LogoutController::class);
        Route::get('/sidebar', [ModuleController::class, 'sidebar'])->middleware('permission:sidebar.view');
        Route::get('/settings/profile', [SettingsController::class, 'profile'])->middleware('permission:settings.view');
        Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])->middleware('permission:settings.update');
        Route::get('/settings/receipt-preferences', [SettingsController::class, 'receiptPreferences'])->middleware('permission:settings.view');
        Route::patch('/settings/receipt-preferences', [SettingsController::class, 'updateReceiptPreferences'])->middleware('permission:settings.update');
        Route::post('/change-password', ChangePasswordController::class);
        Route::post('/users/{userUuid}/temporary-password', [UserController::class, 'generateTemporaryPassword']);
    }); 
});

Route::prefix('admin')->middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN',])->group(function () {
    // Merchant
    Route::get('/merchants',           [MerchantController::class, 'index'])->middleware('permission:management.view');
    Route::post('/merchants',          [MerchantController::class, 'store'])->middleware('permission:management.create');
    Route::get('/merchants/{uuid}',    [MerchantController::class, 'show'])->middleware('permission:management.view');
    Route::patch('/merchants/{uuid}',  [MerchantController::class, 'update'])->middleware('permission:management.update');
    Route::delete('/merchants/{uuid}', [MerchantController::class, 'destroy'])->middleware('permission:management.delete');

    Route::get('/merchants/{merchantUuid}/employers',              [MerchantEmployerController::class, 'index'])->middleware('permission:management.view');
    Route::post('/merchants/{merchantUuid}/employers',             [MerchantEmployerController::class, 'store'])->middleware('permission:management.create');
    Route::get('/merchants/{merchantUuid}/employers/{userUuid}',   [MerchantEmployerController::class, 'show'])->middleware('permission:management.view');
    Route::patch('/merchants/{merchantUuid}/employers/{userUuid}', [MerchantEmployerController::class, 'update'])->middleware('permission:management.update');
    Route::delete('/merchants/{merchantUuid}/employers/{userUuid}',[MerchantEmployerController::class, 'destroy'])->middleware('permission:management.delete');

    Route::get('/merchants/{merchantUuid}/employees', [MerchantEmployeeController::class, 'index'])->middleware('permission:management.view');
    Route::post('/merchants/{merchantUuid}/employees', [MerchantEmployeeController::class, 'store'])->middleware('permission:management.create');
    Route::put('/merchants/{merchantUuid}/employees/{uuid}', [MerchantEmployeeController::class, 'update'])->middleware('permission:management.update');

    // User Management
    Route::get('/users',                [UserController::class, 'index'])->middleware('permission:users.view');
    Route::post('/users',               [UserController::class, 'store'])->middleware('permission:users.create');
    Route::get('/users/{uuid}',         [UserController::class, 'show'])->middleware('permission:users.view');
    Route::put('/users/{user:uuid}',    [UserController::class, 'update'])->middleware('permission:users.update');
    Route::delete('/users/{user:uuid}', [UserController::class, 'destroy'])->middleware('permission:users.delete');

    // Role Management
    Route::get('/roles',           [RoleController::class, 'index'])->middleware('permission:roles.view');
    Route::post('/roles',          [RoleController::class, 'store'])->middleware('permission:roles.create');
    Route::get('/roles/{uuid}',    [RoleController::class, 'show'])->middleware('permission:roles.view');
    Route::put('/roles/{uuid}',    [RoleController::class, 'update'])->middleware('permission:roles.update');
    Route::delete('/roles/{uuid}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete');

    Route::get('/documents', [AdminDocumentVaultController::class, 'index'])->middleware('permission:document_vault.view');
    Route::get('/documents/{uuid}', [AdminDocumentVaultController::class, 'show'])->middleware('permission:document_vault.view');
    Route::patch('/documents/{uuid}', [AdminDocumentVaultController::class, 'update'])->middleware('permission:document_vault.view');
    // Permission Management
    Route::get('/permissions',              [PermissionController::class, 'index'])->middleware('permission:roles.view');
    Route::put('/roles/{uuid}/permissions', [RoleController::class, 'updatePermissions'])->middleware('permission:roles.update');
});






/*
|--------------------------------------------------------------------------
| Employer Route
|--------------------------------------------------------------------------
*/

Route::prefix('employer')->middleware(['auth:sanctum', 'role:EMPLOYER',])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Team Directory
    |--------------------------------------------------------------------------
    */
    Route::get('/team',           [TeamController::class, 'index'])->middleware('permission:team.view');
    Route::post('/team',          [TeamController::class, 'store'])->middleware('permission:team.create');
    Route::get('/team/{uuid}',    [TeamController::class, 'show'])->middleware('permission:team.view');
    Route::put('/team/{uuid}',    [TeamController::class, 'update'])->middleware('permission:team.update');
    Route::patch('/team/{uuid}',  [TeamController::class, 'update'])->middleware('permission:team.update');
    Route::delete('/team/{uuid}', [TeamController::class, 'destroy'])->middleware('permission:team.delete');
    /*
    |--------------------------------------------------------------------------
    | Payroll Batches
    |--------------------------------------------------------------------------
    */
    Route::get('/payroll-batches',                [PayrollBatchController::class, 'index'])->middleware('permission:payroll_batches.view');
    Route::post('/payroll-batches',               [PayrollBatchController::class, 'store'])->middleware('permission:payroll_batches.create');
    Route::get('/payroll-batches/{uuid}',         [PayrollBatchController::class, 'show'])->middleware('permission:payroll_batches.view');
    Route::patch('/payroll-batches/{uuid}',       [PayrollBatchController::class, 'update'])->middleware('permission:payroll_batches.update');
    Route::post('/payroll-batches/{uuid}/submit', [PayrollBatchController::class, 'submit'])->middleware('permission:payroll_batches.submit');

    Route::get('/insights/overview', [InsightController::class, 'overview'])->middleware('permission:insights.view');
    Route::get('/insights/payroll-deep-dive', [InsightController::class, 'payrollDeepDive'])->middleware('permission:insights.view');
    Route::get('/insights/payslips', [InsightController::class, 'payslips'])->middleware('permission:insights.view');
    Route::get('/insights/fund-holds', [InsightController::class, 'fundHolds'])->middleware('permission:insights.view');
    /*
    | Payslips
    */
    Route::get('/reports/payroll-summary', [ReportController::class, 'summary'])->middleware('permission:reports.view');
    Route::get('/reports/payroll-summary/export', [ReportController::class, 'export'])->middleware('permission:reports.view');
    Route::get('/disbursements', [DisbursementController::class, 'index'])->middleware('permission:disbursements.view');
    Route::post('/disbursements', [DisbursementController::class, 'store'])->middleware('permission:disbursements.create');
    Route::get('/disbursements/{uuid}', [DisbursementController::class, 'show'])->middleware('permission:disbursements.view');
    Route::post('/disbursements/{uuid}/release', [DisbursementController::class, 'release'])->middleware('permission:disbursements.release');
    Route::get('/fund-holds/summary', [FundHoldController::class, 'summary'])->middleware('permission:fund_holds.view');
    Route::get('/fund-holds', [FundHoldController::class, 'index'])->middleware('permission:fund_holds.view');
    Route::post('/fund-holds', [FundHoldController::class, 'store'])->middleware('permission:fund_holds.create');
    Route::get('/fund-holds/{uuid}', [FundHoldController::class, 'show'])->middleware('permission:fund_holds.view');
    Route::post('/fund-holds/{uuid}/release', [FundHoldController::class, 'release'])->middleware('permission:fund_holds.create');
    Route::post('/fund-holds/{uuid}/return', [FundHoldController::class, 'return'])->middleware('permission:fund_holds.create');
    Route::post('/fund-holds/{uuid}/override', [FundHoldController::class, 'override'])->middleware('permission:fund_holds.create');
    Route::post('/disbursements/{uuid}/cancel', [DisbursementController::class, 'cancel'])->middleware('permission:disbursements.create');
    Route::get('/documents', [EmployerDocumentVaultController::class, 'index'])->middleware('permission:document_vault.view');
    Route::get('/documents/{uuid}', [EmployerDocumentVaultController::class, 'show'])->middleware('permission:document_vault.view');
    Route::patch('/documents/{uuid}', [EmployerDocumentVaultController::class, 'update'])->middleware('permission:document_vault.view');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit_logs.view');
    Route::post('/payslips', [PayslipController::class, 'store'])->middleware('permission:payslips.create');
    Route::get('/disputes', [DisputeController::class, 'index'])->middleware('permission:disputes.view');
    Route::post('/disputes', [DisputeController::class, 'store'])->middleware('permission:disputes.create');
    Route::get('/disputes/{uuid}', [DisputeController::class, 'show'])->middleware('permission:disputes.view');
    Route::patch('/disputes/{uuid}', [DisputeController::class, 'update'])->middleware('permission:disputes.update');
    Route::post('/disputes/{uuid}/messages', [DisputeController::class, 'addMessage'])->middleware('permission:disputes.update');
    Route::get('/transactions', [TransactionController::class, 'employerIndex']);
    Route::get('/transactions/{uuid}', [TransactionController::class, 'employerShow']);
    Route::get('/merchant-transactions', [TransactionController::class, 'merchantIndex']);
    Route::get('/merchant-transactions/{uuid}', [TransactionController::class, 'merchantShow']);
    Route::get('/wallet', [EmployerWalletController::class, 'employer']);
    Route::get('/merchant-wallet', [EmployerWalletController::class, 'merchant']);
});





Route::prefix('employee')->middleware(['auth:sanctum', 'role:EMPLOYEE'])->group(function () {
    Route::post('/payslips/{uuid}/acknowledge', [PayslipAcknowledgementController::class, 'store']);
    Route::get('/payslips', [EmployeePayslipController::class, 'index']);
    Route::get('/payslips/{uuid}', [EmployeePayslipController::class, 'show']);
    Route::get('/cases', [CaseController::class, 'index']);
    Route::post('/cases', [CaseController::class, 'store']);
    Route::get('/cases/{uuid}', [CaseController::class, 'show']);
    Route::post('/cases/{uuid}/messages', [CaseController::class, 'addMessage']);
    Route::get('/documents', [DocumentVaultController::class, 'index'])->middleware('permission:document_vault.view');
    Route::get('/documents/{uuid}', [DocumentVaultController::class, 'show'])->middleware('permission:document_vault.view');
    Route::patch('/documents/{uuid}', [DocumentVaultController::class, 'update'])->middleware('permission:document_vault.view');
    Route::get('/wallet', [WalletController::class, 'show']);
    Route::get('/transactions', [TransactionController::class, 'employeeIndex']);
    Route::get('/transactions/{uuid}', [TransactionController::class, 'employeeShow']);
});

Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN',])->prefix('admin/wallets')->group(function () {
    Route::get('/merchants', [MerchantWalletController::class, 'merchants']);
    Route::post('/merchants/{merchantUuid}/prefund', [MerchantWalletController::class, 'prefund']);
});
