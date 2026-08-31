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
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.post');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth.custom'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    Route::prefix('settings')->name('settings.')->group(function () {

        Route::get(
            'payment-methods',
            [\App\Http\Controllers\Settings\PaymentMethodSettingsController::class, 'index']
        )
            ->middleware('permission:settings.view')
            ->name('payment-methods.index');

        Route::post(
            'payment-methods',
            [\App\Http\Controllers\Settings\PaymentMethodSettingsController::class, 'update']
        )
            ->middleware('permission:settings.edit')
            ->name('payment-methods.update');
    });


    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    |
    | Keep role:admin for now as an additional safety layer.
    | Stage 5 will eventually move this completely to permissions.
    |
    */

    Route::middleware('role:admin')->group(function () {

        Route::get('/users', [UserController::class, 'index'])
            ->middleware('permission:users.view')
            ->name('users.index');

        Route::get('/users/create', [UserController::class, 'create'])
            ->middleware('permission:users.create')
            ->name('users.create');

        Route::post('/users', [UserController::class, 'store'])
            ->middleware('permission:users.create')
            ->name('users.store');

        Route::get('/users/{user}/edit', [UserController::class, 'edit'])
            ->middleware('permission:users.edit')
            ->name('users.edit');

        Route::put('/users/{user}', [UserController::class, 'update'])
            ->middleware('permission:users.edit')
            ->name('users.update');

        Route::patch('/users/{user}/toggle', [UserController::class, 'toggleStatus'])
            ->middleware('permission:users.deactivate')
            ->name('users.toggle');

        
Route::get('/permissions', [PermissionController::class, 'index'])
    ->middleware('permission:permissions.view')
    ->name('permissions.index');
 
Route::put('/permissions', [PermissionController::class, 'update'])
    ->middleware('permission:permissions.manage')
    ->name('permissions.update');
        
    });


    /*
    |--------------------------------------------------------------------------
    | Students
    |--------------------------------------------------------------------------
    */

    Route::get('/students', [StudentController::class, 'index'])
        ->middleware('permission:students.view')
        ->name('students.index');

    Route::get('/students/create', [StudentController::class, 'create'])
        ->middleware('permission:students.create')
        ->name('students.create');

    Route::post('/students', [StudentController::class, 'store'])
        ->middleware('permission:students.create')
        ->name('students.store');

    Route::get('/students/import', [StudentImportController::class, 'showImportForm'])
        ->middleware('permission:students.import')
        ->name('students.import');

    Route::post('/students/import/process', [StudentImportController::class, 'import'])
        ->middleware('permission:students.import')
        ->name('students.import.process');

    Route::get('/students/import/sample', [StudentImportController::class, 'downloadSample'])
        ->middleware('permission:students.import')
        ->name('students.import.sample');

    Route::get('/students/export', [StudentController::class, 'export'])
        ->middleware('permission:students.export')
        ->name('students.export');

    Route::get('/student-photo/{filename}', [StudentController::class, 'photo'])
        ->middleware('permission:students.view')
        ->name('students.photo');

    Route::get('/students/search', [StudentController::class, 'search'])
        ->middleware('permission:students.view')
        ->name('students.search');

    Route::post('/students/{id}/family-code', [StudentController::class, 'assignFamilyCode'])
        ->middleware('permission:students.edit')
        ->name('students.family-code.assign');

    Route::get('/students/{id}', [StudentController::class, 'show'])
        ->middleware('permission:students.view')
        ->name('students.show');

    Route::get('/students/{id}/edit', [StudentController::class, 'edit'])
        ->middleware('permission:students.edit')
        ->name('students.edit');

    Route::put('/students/{id}', [StudentController::class, 'update'])
        ->middleware('permission:students.edit')
        ->name('students.update');

    Route::delete('/students/{id}', [StudentController::class, 'destroy'])
        ->middleware('permission:students.delete')
        ->name('students.destroy');


    /*
    |--------------------------------------------------------------------------
    | Enrollments
    |--------------------------------------------------------------------------
    */

    Route::get('/enrollments', [PaEnrollmentController::class, 'index'])
        ->middleware('permission:enrollments.view')
        ->name('enrollments.index');

    Route::get('/enrollments/export', [PaEnrollmentController::class, 'export'])
        ->middleware('permission:enrollments.export')
        ->name('enrollments.export');

    Route::get('/enrollments/create', [PaEnrollmentController::class, 'create'])
        ->middleware('permission:enrollments.create')
        ->name('enrollments.create');

    Route::post('/enrollments/store', [PaEnrollmentController::class, 'store'])
        ->middleware('permission:enrollments.create')
        ->name('enrollments.store');

    Route::get('/enrollments/{id}/edit', [PaEnrollmentController::class, 'edit'])
        ->middleware('permission:enrollments.edit')
        ->name('enrollments.edit');

    Route::put('/enrollments/{id}', [PaEnrollmentController::class, 'update'])
        ->middleware('permission:enrollments.edit')
        ->name('enrollments.update');

    Route::patch('/enrollments/{id}/toggle', [PaEnrollmentController::class, 'toggleStatus'])
        ->middleware('permission:enrollments.edit')
        ->name('enrollments.toggle');

    Route::delete('/enrollments/{id}', [PaEnrollmentController::class, 'destroy'])
        ->middleware('permission:enrollments.delete')
        ->name('enrollments.destroy');


    /*
    |--------------------------------------------------------------------------
    | Classes
    |--------------------------------------------------------------------------
    */

    Route::get('/classes', [PaClassController::class, 'index'])
        ->middleware('permission:classes.view')
        ->name('classes.index');

    Route::get('/classes/create', [PaClassController::class, 'create'])
        ->middleware('permission:classes.create')
        ->name('classes.create');

    Route::post('/classes', [PaClassController::class, 'store'])
        ->middleware('permission:classes.create')
        ->name('classes.store');

    Route::get('/classes/{id}/edit', [PaClassController::class, 'edit'])
        ->middleware('permission:classes.edit')
        ->name('classes.edit');

    Route::put('/classes/{id}', [PaClassController::class, 'update'])
        ->middleware('permission:classes.edit')
        ->name('classes.update');

    Route::patch('/classes/{id}/toggle', [PaClassController::class, 'toggleStatus'])
        ->middleware('permission:classes.edit')
        ->name('classes.toggle');

    Route::delete('/classes/{id}', [PaClassController::class, 'destroy'])
        ->middleware('permission:classes.delete')
        ->name('classes.destroy');

    Route::post('/classes/reorder', [PaClassController::class, 'reorder'])
        ->middleware('permission:classes.reorder')
        ->name('classes.reorder');


    /*
    |--------------------------------------------------------------------------
    | Sessions
    |--------------------------------------------------------------------------
    */

    Route::get('/sessions', [PaSessionController::class, 'index'])
        ->middleware('permission:sessions.view')
        ->name('sessions.index');

    Route::get('/sessions/create', [PaSessionController::class, 'create'])
        ->middleware('permission:sessions.create')
        ->name('sessions.create');

    Route::post('/sessions', [PaSessionController::class, 'store'])
        ->middleware('permission:sessions.create')
        ->name('sessions.store');

    Route::get('/sessions/{id}/edit', [PaSessionController::class, 'edit'])
        ->middleware('permission:sessions.edit')
        ->name('sessions.edit');

    Route::put('/sessions/{id}', [PaSessionController::class, 'update'])
        ->middleware('permission:sessions.edit')
        ->name('sessions.update');

    Route::patch('/sessions/{id}/set-active', [PaSessionController::class, 'setActive'])
        ->middleware('permission:sessions.activate')
        ->name('sessions.set-active');

    Route::delete('/sessions/{id}', [PaSessionController::class, 'destroy'])
        ->middleware('permission:sessions.delete')
        ->name('sessions.destroy');


    /*
    |--------------------------------------------------------------------------
    | Promotion
    |--------------------------------------------------------------------------
    */

    Route::get('/promotion/preview', [PaPromotionController::class, 'index'])
        ->middleware('permission:promotion.view')
        ->name('promotion.preview');

    Route::post('/promotion/execute', [PaPromotionController::class, 'promote'])
        ->middleware('permission:promotion.execute')
        ->name('promotion.execute');


    /*
    |--------------------------------------------------------------------------
    | Fee Payments
    |--------------------------------------------------------------------------
    */

    Route::get('/fee-payments', [FeePaymentController::class, 'index'])
        ->middleware('permission:fee-payments.view')
        ->name('fee-payments.index');

    Route::get('/fee-payments/export', [FeePaymentController::class, 'export'])
        ->middleware('permission:fee-payments.export')
        ->name('fee-payments.export');

    Route::get('/fee-payments/create/{voucher}', [FeePaymentController::class, 'create'])
        ->middleware('permission:fee-payments.create')
        ->name('fee-payments.create');

    Route::post('/fee-payments/store', [FeePaymentController::class, 'store'])
        ->middleware('permission:fee-payments.create')
        ->name('fee-payments.store');

    Route::get('/fee-payments/{id}/receipt', [FeePaymentController::class, 'receipt'])
        ->middleware('permission:fee-payments.receipt')
        ->name('fee-payments.receipt');

    Route::get('/fee-payments/{id}/edit', [FeePaymentController::class, 'edit'])
        ->middleware('permission:fee-payments.edit')
        ->name('fee-payments.edit');

    Route::put('/fee-payments/{id}', [FeePaymentController::class, 'update'])
        ->middleware('permission:fee-payments.edit')
        ->name('fee-payments.update');

    Route::delete('/fee-payments/{id}', [FeePaymentController::class, 'destroy'])
        ->middleware('permission:fee-payments.delete')
        ->name('fee-payments.destroy');


    /*
    |--------------------------------------------------------------------------
    | Fee Vouchers
    |--------------------------------------------------------------------------
    */

    Route::get('/fee-vouchers', [FeeVoucherController::class, 'index'])
        ->middleware('permission:fee-vouchers.view')
        ->name('fee-vouchers.index');

    Route::get('/fee-vouchers/create', [FeeVoucherController::class, 'create'])
        ->middleware('permission:fee-vouchers.create')
        ->name('fee-vouchers.create');

    Route::post('/fee-vouchers/store', [FeeVoucherController::class, 'store'])
        ->middleware('permission:fee-vouchers.create')
        ->name('fee-vouchers.store');

    Route::get('/fee-vouchers/{id}/edit', [FeeVoucherController::class, 'edit'])
        ->middleware('permission:fee-vouchers.edit')
        ->name('fee-vouchers.edit');

    Route::put('/fee-vouchers/{id}', [FeeVoucherController::class, 'update'])
        ->middleware('permission:fee-vouchers.edit')
        ->name('fee-vouchers.update');

    Route::get('/fee-vouchers/{id}/print', [FeeVoucherController::class, 'print'])
        ->middleware('permission:fee-vouchers.print')
        ->name('fee-vouchers.print');

    Route::delete('/fee-vouchers/{id}', [FeeVoucherController::class, 'destroy'])
        ->middleware('permission:fee-vouchers.delete')
        ->name('fee-vouchers.destroy');


    /*
    |--------------------------------------------------------------------------
    | Student Ledger
    |--------------------------------------------------------------------------
    */

    Route::get('/student-ledger', [StudentLedgerController::class, 'index'])
        ->middleware('permission:student-ledger.view')
        ->name('student-ledger.index');

    Route::get('/student-ledger/previous-balance', [StudentLedgerController::class, 'getPreviousBalance'])
        ->middleware('permission:previous-balances.view')
        ->name('student-ledger.previous-balance');

    Route::get('/student-ledger/export', [StudentLedgerController::class, 'exportBalanceSheet'])
        ->middleware('permission:student-ledger.export')
        ->name('student-ledger.export');

    Route::get('/student-ledger/{studentId}', [StudentLedgerController::class, 'show'])
        ->middleware('permission:student-ledger.view')
        ->name('student-ledger.show');


    /*
    |--------------------------------------------------------------------------
    | Previous Balances
    |--------------------------------------------------------------------------
    */

    Route::get('/previous-balances', [PreviousBalanceController::class, 'index'])
        ->middleware('permission:previous-balances.view')
        ->name('previous-balances.index');

    Route::post('/previous-balances/carry-forward', [PreviousBalanceController::class, 'carryForward'])
        ->middleware('permission:previous-balances.carry-forward')
        ->name('previous-balances.carry-forward');

    Route::post('/previous-balances/bulk-carry-forward', [PreviousBalanceController::class, 'bulkCarryForward'])
        ->middleware('permission:previous-balances.carry-forward')
        ->name('previous-balances.bulk-carry-forward');


    /*
    |--------------------------------------------------------------------------
    | Monthly Fee Generator
    |--------------------------------------------------------------------------
    */

    Route::get('/monthly-fee-generator', [MonthlyFeeGeneratorController::class, 'create'])
        ->middleware('permission:monthly-fee-generator.view')
        ->name('monthly-fee-generator.create');

    Route::post('/monthly-fee-generator/preview', [MonthlyFeeGeneratorController::class, 'preview'])
        ->middleware('permission:monthly-fee-generator.create')
        ->name('monthly-fee-generator.preview');

    Route::post('/monthly-fee-generator', [MonthlyFeeGeneratorController::class, 'store'])
        ->middleware('permission:monthly-fee-generator.create')
        ->name('monthly-fee-generator.store');


    /*
    |--------------------------------------------------------------------------
    | Class Fee Structures
    |--------------------------------------------------------------------------
    */

    Route::get(
        'class-fee-structures/bulk/create',
        [ClassFeeStructureController::class, 'bulkCreate']
    )
        ->middleware('permission:class-fee-structures.create')
        ->name('class-fee-structures.bulk.create');

    Route::post(
        'class-fee-structures/bulk/store',
        [ClassFeeStructureController::class, 'bulkStore']
    )
        ->middleware('permission:class-fee-structures.create')
        ->name('class-fee-structures.bulk.store');

    Route::get(
        'class-fee-structures/import',
        [ClassFeeStructureController::class, 'importForm']
    )
        ->middleware('permission:class-fee-structures.import')
        ->name('class-fee-structures.import.form');

    Route::post(
        'class-fee-structures/import',
        [ClassFeeStructureController::class, 'importStore']
    )
        ->middleware('permission:class-fee-structures.import')
        ->name('class-fee-structures.import.store');

    Route::get(
        'class-fee-structures/sample-csv',
        [ClassFeeStructureController::class, 'sampleCsv']
    )
        ->middleware('permission:class-fee-structures.import')
        ->name('class-fee-structures.sample.csv');

    /*
    |--------------------------------------------------------------------------
    | Class Fee Structure Resource Routes
    |--------------------------------------------------------------------------
    */

    Route::get(
        'class-fee-structures',
        [ClassFeeStructureController::class, 'index']
    )
        ->middleware('permission:class-fee-structures.view')
        ->name('class-fee-structures.index');

    Route::post(
        'class-fee-structures',
        [ClassFeeStructureController::class, 'store']
    )
        ->middleware('permission:class-fee-structures.create')
        ->name('class-fee-structures.store');

    Route::get(
        'class-fee-structures/create',
        [ClassFeeStructureController::class, 'create']
    )
        ->middleware('permission:class-fee-structures.create')
        ->name('class-fee-structures.create');

    Route::get(
        'class-fee-structures/{class_fee_structure}',
        [ClassFeeStructureController::class, 'show']
    )
        ->middleware('permission:class-fee-structures.view')
        ->name('class-fee-structures.show');

    Route::get(
        'class-fee-structures/{class_fee_structure}/edit',
        [ClassFeeStructureController::class, 'edit']
    )
        ->middleware('permission:class-fee-structures.edit')
        ->name('class-fee-structures.edit');

    Route::put(
        'class-fee-structures/{class_fee_structure}',
        [ClassFeeStructureController::class, 'update']
    )
        ->middleware('permission:class-fee-structures.edit')
        ->name('class-fee-structures.update');

    Route::patch(
        'class-fee-structures/{class_fee_structure}',
        [ClassFeeStructureController::class, 'update']
    )
        ->middleware('permission:class-fee-structures.edit')
        ->name('class-fee-structures.update.patch');

    Route::delete(
        'class-fee-structures/{class_fee_structure}',
        [ClassFeeStructureController::class, 'destroy']
    )
        ->middleware('permission:class-fee-structures.delete')
        ->name('class-fee-structures.destroy');


    /*
    |--------------------------------------------------------------------------
    | Monthly Ledger
    |--------------------------------------------------------------------------
    */

    Route::get('/monthly-ledger', [MonthlyLedgerController::class, 'index'])
        ->middleware('permission:monthly-ledger.view')
        ->name('monthly-ledger.index');


    /*
    |--------------------------------------------------------------------------
    | Fee Matrix
    |--------------------------------------------------------------------------
    */

    Route::get('/fee-matrix', [FeeMatrixController::class, 'index'])
        ->middleware('permission:fee-matrix.view')
        ->name('fee-matrix.index');

    Route::get('/fee-matrix/export', [FeeMatrixController::class, 'export'])
        ->middleware('permission:fee-matrix.export')
        ->name('fee-matrix.export');


    /*
    |--------------------------------------------------------------------------
    | Expenses
    |--------------------------------------------------------------------------
    */

    Route::get('/expenses', [ExpenseController::class, 'index'])
        ->middleware('permission:expenses.view')
        ->name('expenses.index');

    Route::get('/expenses/create', [ExpenseController::class, 'create'])
        ->middleware('permission:expenses.create')
        ->name('expenses.create');

    Route::post('/expenses', [ExpenseController::class, 'store'])
        ->middleware('permission:expenses.create')
        ->name('expenses.store');

    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])
        ->middleware('permission:expenses.view')
        ->name('expenses.show');

    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])
        ->middleware('permission:expenses.edit')
        ->name('expenses.edit');

    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])
        ->middleware('permission:expenses.edit')
        ->name('expenses.update');

    Route::patch('/expenses/{expense}', [ExpenseController::class, 'update'])
        ->middleware('permission:expenses.edit')
        ->name('expenses.update.patch');

    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->middleware('permission:expenses.delete')
        ->name('expenses.destroy');
});