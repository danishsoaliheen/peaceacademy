<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PaEnrollmentController;
use App\Http\Controllers\PaClassController;
use App\Http\Controllers\PaSessionController;
use App\Http\Controllers\FeePaymentController;
use App\Http\Controllers\MonthlyFeeGeneratorController;
use App\Http\Controllers\ClassFeeStructureController;
use App\Http\Controllers\PaPromotionController;
use App\Http\Controllers\StudentLedgerController;
use App\Http\Controllers\FeeVoucherController;
use App\Http\Controllers\PreviousBalanceController;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MonthlyLedgerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FeeMatrixController;

/*
|--------------------------------------------------------------------------
| Authentication Routes (public)
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
| All routes below require login.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth.custom'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Settings - Payment Methods
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('payment-methods', [\App\Http\Controllers\Settings\PaymentMethodSettingsController::class, 'index'])
            ->name('payment-methods.index');
        Route::post('payment-methods', [\App\Http\Controllers\Settings\PaymentMethodSettingsController::class, 'update'])
            ->name('payment-methods.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Student Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/students',           [StudentController::class, 'index'])  ->name('students.index');
    Route::get('/students/create',    [StudentController::class, 'create']) ->name('students.create');
    Route::post('/students',          [StudentController::class, 'store'])  ->name('students.store');

    Route::get('/students/import',         [StudentImportController::class, 'showImportForm'])->name('students.import');
    Route::post('/students/import/process',[StudentImportController::class, 'import'])        ->name('students.import.process');
    Route::get('/students/import/sample',  [StudentImportController::class, 'downloadSample'])->name('students.import.sample');

    // Serves student photos directly from storage — does not depend on the
    // public/storage symlink, so it keeps working even if `storage:link`
    // was never run or fails (common on Windows without admin rights).
    Route::get('/student-photo/{filename}', [StudentController::class, 'photo'])->name('students.photo');

    // Sibling / family-code linking (AJAX) — must stay above the /{id}
    // wildcard routes below.
    Route::get('/students/search', [StudentController::class, 'search'])->name('students.search');
    Route::post('/students/{id}/family-code', [StudentController::class, 'assignFamilyCode'])->name('students.family-code.assign');

    Route::get('/students/{id}',      [StudentController::class, 'show'])   ->name('students.show');
    Route::get('/students/{id}/edit', [StudentController::class, 'edit'])   ->name('students.edit');
    Route::put('/students/{id}',      [StudentController::class, 'update']) ->name('students.update');
    Route::delete('/students/{id}',   [StudentController::class, 'destroy'])->name('students.destroy');

    /*
    |--------------------------------------------------------------------------
    | Enrollment Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/enrollments',        [PaEnrollmentController::class, 'index'])  ->name('enrollments.index');
    Route::get('/enrollments/export', [PaEnrollmentController::class, 'export']) ->name('enrollments.export');
    Route::get('/enrollments/create', [PaEnrollmentController::class, 'create']) ->name('enrollments.create');
    Route::post('/enrollments/store', [PaEnrollmentController::class, 'store'])  ->name('enrollments.store');

    Route::get('/enrollments/{id}/edit',    [PaEnrollmentController::class, 'edit'])         ->name('enrollments.edit');
    Route::put('/enrollments/{id}',         [PaEnrollmentController::class, 'update'])       ->name('enrollments.update');
    Route::patch('/enrollments/{id}/toggle',[PaEnrollmentController::class, 'toggleStatus']) ->name('enrollments.toggle');
    Route::delete('/enrollments/{id}',      [PaEnrollmentController::class, 'destroy'])      ->name('enrollments.destroy');

    /*
    |--------------------------------------------------------------------------
    | Class Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/classes',                [PaClassController::class, 'index'])        ->name('classes.index');
    Route::get('/classes/create',         [PaClassController::class, 'create'])       ->name('classes.create');
    Route::post('/classes',               [PaClassController::class, 'store'])        ->name('classes.store');
    Route::get('/classes/{id}/edit',      [PaClassController::class, 'edit'])         ->name('classes.edit');
    Route::put('/classes/{id}',           [PaClassController::class, 'update'])       ->name('classes.update');
    Route::patch('/classes/{id}/toggle',  [PaClassController::class, 'toggleStatus']) ->name('classes.toggle');
    Route::delete('/classes/{id}',        [PaClassController::class, 'destroy'])      ->name('classes.destroy');
    Route::post('/classes/reorder',       [PaClassController::class, 'reorder'])      ->name('classes.reorder');

    /*
    |--------------------------------------------------------------------------
    | Session Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/sessions',                   [PaSessionController::class, 'index'])     ->name('sessions.index');
    Route::get('/sessions/create',            [PaSessionController::class, 'create'])    ->name('sessions.create');
    Route::post('/sessions',                  [PaSessionController::class, 'store'])     ->name('sessions.store');
    Route::get('/sessions/{id}/edit',         [PaSessionController::class, 'edit'])      ->name('sessions.edit');
    Route::put('/sessions/{id}',              [PaSessionController::class, 'update'])    ->name('sessions.update');
    Route::patch('/sessions/{id}/set-active', [PaSessionController::class, 'setActive'])->name('sessions.set-active');
    Route::delete('/sessions/{id}',           [PaSessionController::class, 'destroy'])   ->name('sessions.destroy');

    /*
    |--------------------------------------------------------------------------
    | Promotion Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/promotion/preview',  [PaPromotionController::class, 'index'])  ->name('promotion.preview');
    Route::post('/promotion/execute', [PaPromotionController::class, 'promote'])->name('promotion.execute');

    /*
    |--------------------------------------------------------------------------
    | Fee Payment Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/fee-payments',                [FeePaymentController::class, 'index'])  ->name('fee-payments.index');
    Route::get('/fee-payments/create/{voucher}', [FeePaymentController::class, 'create'])->name('fee-payments.create');
    Route::post('/fee-payments/store',           [FeePaymentController::class, 'store']) ->name('fee-payments.store');
    Route::get('/fee-payments/{id}/receipt',     [FeePaymentController::class, 'receipt'])->name('fee-payments.receipt');

    Route::get('/fee-payments/{id}/edit', [FeePaymentController::class, 'edit'])->name('fee-payments.edit');
    Route::put('/fee-payments/{id}', [FeePaymentController::class, 'update'])->name('fee-payments.update');
    Route::delete('/fee-payments/{id}', [FeePaymentController::class, 'destroy'])->name('fee-payments.destroy');

    /*
    |--------------------------------------------------------------------------
    | Fee Voucher Routes
    | NOTE: Static routes (/create) MUST appear before wildcard routes (/{id})
    |--------------------------------------------------------------------------
    */

    Route::get('/fee-vouchers',            [FeeVoucherController::class, 'index'])  ->name('fee-vouchers.index');
    Route::get('/fee-vouchers/create',     [FeeVoucherController::class, 'create']) ->name('fee-vouchers.create');
    Route::post('/fee-vouchers/store',     [FeeVoucherController::class, 'store'])  ->name('fee-vouchers.store');
    Route::get('/fee-vouchers/{id}/edit',  [FeeVoucherController::class, 'edit'])   ->name('fee-vouchers.edit');
    Route::put('/fee-vouchers/{id}',       [FeeVoucherController::class, 'update']) ->name('fee-vouchers.update');
    Route::get('/fee-vouchers/{id}/print', [FeeVoucherController::class, 'print'])  ->name('fee-vouchers.print');
    Route::delete('fee-vouchers/{id}', [FeeVoucherController::class, 'destroy'])->name('fee-vouchers.destroy');
    /*
    |--------------------------------------------------------------------------
    | Student Ledger Routes
    |--------------------------------------------------------------------------
    */
  /*
    |--------------------------------------------------------------------------
    | Student Ledger Routes
    |--------------------------------------------------------------------------
    */
 
    Route::get('/student-ledger',
        [StudentLedgerController::class, 'index']
    )->name('student-ledger.index');
 
    Route::get('/student-ledger/previous-balance',
        [StudentLedgerController::class, 'getPreviousBalance']
    )->name('student-ledger.previous-balance');
 
    Route::get('/student-ledger/export',
        [StudentLedgerController::class, 'exportBalanceSheet']
    )->name('student-ledger.export');
 
    Route::get('/student-ledger/{studentId}',
        [StudentLedgerController::class, 'show']
    )->name('student-ledger.show');
 

    /*
    |--------------------------------------------------------------------------
    | Previous Balance Routes (carry-forward)
    |--------------------------------------------------------------------------
    */

    Route::get('/previous-balances',
        [PreviousBalanceController::class, 'index']
    )->name('previous-balances.index');

    Route::post('/previous-balances/carry-forward',
        [PreviousBalanceController::class, 'carryForward']
    )->name('previous-balances.carry-forward');

    Route::post('/previous-balances/bulk-carry-forward',
        [PreviousBalanceController::class, 'bulkCarryForward']
    )->name('previous-balances.bulk-carry-forward');

    /*
    |--------------------------------------------------------------------------
    | Monthly Fee Generator
    |--------------------------------------------------------------------------
    */

    Route::get('/monthly-fee-generator',          [MonthlyFeeGeneratorController::class, 'create']) ->name('monthly-fee-generator.create');
    Route::post('/monthly-fee-generator/preview', [MonthlyFeeGeneratorController::class, 'preview'])->name('monthly-fee-generator.preview');
    Route::post('/monthly-fee-generator',         [MonthlyFeeGeneratorController::class, 'store'])  ->name('monthly-fee-generator.store');

    /*
    |--------------------------------------------------------------------------
    | Class Fee Structure Routes
    |--------------------------------------------------------------------------
    */

    Route::get('class-fee-structures/bulk/create',  [ClassFeeStructureController::class, 'bulkCreate']) ->name('class-fee-structures.bulk.create');
    Route::post('class-fee-structures/bulk/store',  [ClassFeeStructureController::class, 'bulkStore'])  ->name('class-fee-structures.bulk.store');
    Route::get('class-fee-structures/import',       [ClassFeeStructureController::class, 'importForm']) ->name('class-fee-structures.import.form');
    Route::post('class-fee-structures/import',      [ClassFeeStructureController::class, 'importStore'])->name('class-fee-structures.import.store');
    Route::get('class-fee-structures/sample-csv',   [ClassFeeStructureController::class, 'sampleCsv']) ->name('class-fee-structures.sample.csv');
    Route::resource('class-fee-structures', ClassFeeStructureController::class);

    /*
    |--------------------------------------------------------------------------
    | Monthly Accounting Ledger
    |--------------------------------------------------------------------------
    */

    Route::get('/monthly-ledger', [MonthlyLedgerController::class, 'index'])->name('monthly-ledger.index');

    /*
    |--------------------------------------------------------------------------
    | Fee Matrix — grid view of every student x every month
    |--------------------------------------------------------------------------
    */

    Route::get('/fee-matrix',        [FeeMatrixController::class, 'index'])  ->name('fee-matrix.index');
    Route::get('/fee-matrix/export', [FeeMatrixController::class, 'export']) ->name('fee-matrix.export');

    /*
    |--------------------------------------------------------------------------
    | Expenses (CRUD)
    |--------------------------------------------------------------------------
    */

    Route::resource('expenses', ExpenseController::class);
});