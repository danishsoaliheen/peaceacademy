<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Permission catalogue
        |--------------------------------------------------------------------------
        */

        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'dashboard.view',
                'group' => 'Dashboard',
                'description' => 'View the dashboard',
            ],

            /*
            |--------------------------------------------------------------------------
            | Students
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'students.view',
                'group' => 'Students',
                'description' => 'View students',
            ],
            [
                'name' => 'students.create',
                'group' => 'Students',
                'description' => 'Create students',
            ],
            [
                'name' => 'students.edit',
                'group' => 'Students',
                'description' => 'Edit students',
            ],
            [
                'name' => 'students.delete',
                'group' => 'Students',
                'description' => 'Delete students',
            ],
            [
                'name' => 'students.import',
                'group' => 'Students',
                'description' => 'Import students',
            ],
            [
                'name' => 'students.export',
                'group' => 'Students',
                'description' => 'Export students',
            ],

            /*
            |--------------------------------------------------------------------------
            | Enrollments
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'enrollments.view',
                'group' => 'Enrollments',
                'description' => 'View enrollments',
            ],
            [
                'name' => 'enrollments.create',
                'group' => 'Enrollments',
                'description' => 'Create enrollments',
            ],
            [
                'name' => 'enrollments.edit',
                'group' => 'Enrollments',
                'description' => 'Edit enrollments',
            ],
            [
                'name' => 'enrollments.delete',
                'group' => 'Enrollments',
                'description' => 'Delete enrollments',
            ],
            [
                'name' => 'enrollments.export',
                'group' => 'Enrollments',
                'description' => 'Export enrollments',
            ],

            /*
            |--------------------------------------------------------------------------
            | Classes
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'classes.view',
                'group' => 'Classes',
                'description' => 'View classes',
            ],
            [
                'name' => 'classes.create',
                'group' => 'Classes',
                'description' => 'Create classes',
            ],
            [
                'name' => 'classes.edit',
                'group' => 'Classes',
                'description' => 'Edit classes',
            ],
            [
                'name' => 'classes.delete',
                'group' => 'Classes',
                'description' => 'Delete classes',
            ],
            [
                'name' => 'classes.reorder',
                'group' => 'Classes',
                'description' => 'Reorder classes',
            ],

            /*
            |--------------------------------------------------------------------------
            | Sessions
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'sessions.view',
                'group' => 'Sessions',
                'description' => 'View sessions',
            ],
            [
                'name' => 'sessions.create',
                'group' => 'Sessions',
                'description' => 'Create sessions',
            ],
            [
                'name' => 'sessions.edit',
                'group' => 'Sessions',
                'description' => 'Edit sessions',
            ],
            [
                'name' => 'sessions.delete',
                'group' => 'Sessions',
                'description' => 'Delete sessions',
            ],
            [
                'name' => 'sessions.activate',
                'group' => 'Sessions',
                'description' => 'Set active session',
            ],

            /*
            |--------------------------------------------------------------------------
            | Promotion
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'promotion.view',
                'group' => 'Promotion',
                'description' => 'View promotion preview',
            ],
            [
                'name' => 'promotion.execute',
                'group' => 'Promotion',
                'description' => 'Execute student promotion',
            ],

            /*
            |--------------------------------------------------------------------------
            | Fee Payments
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'fee-payments.view',
                'group' => 'Fee Payments',
                'description' => 'View fee payments',
            ],
            [
                'name' => 'fee-payments.create',
                'group' => 'Fee Payments',
                'description' => 'Record fee payments',
            ],
            [
                'name' => 'fee-payments.edit',
                'group' => 'Fee Payments',
                'description' => 'Edit fee payments',
            ],
            [
                'name' => 'fee-payments.delete',
                'group' => 'Fee Payments',
                'description' => 'Delete fee payments',
            ],
            [
                'name' => 'fee-payments.export',
                'group' => 'Fee Payments',
                'description' => 'Export fee payments',
            ],
            [
                'name' => 'fee-payments.receipt',
                'group' => 'Fee Payments',
                'description' => 'View payment receipts',
            ],

            /*
            |--------------------------------------------------------------------------
            | Fee Vouchers
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'fee-vouchers.view',
                'group' => 'Fee Vouchers',
                'description' => 'View fee vouchers',
            ],
            [
                'name' => 'fee-vouchers.create',
                'group' => 'Fee Vouchers',
                'description' => 'Create fee vouchers',
            ],
            [
                'name' => 'fee-vouchers.edit',
                'group' => 'Fee Vouchers',
                'description' => 'Edit fee vouchers',
            ],
            [
                'name' => 'fee-vouchers.delete',
                'group' => 'Fee Vouchers',
                'description' => 'Delete fee vouchers',
            ],
            [
                'name' => 'fee-vouchers.print',
                'group' => 'Fee Vouchers',
                'description' => 'Print fee vouchers',
            ],

            /*
            |--------------------------------------------------------------------------
            | Student Ledger
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'student-ledger.view',
                'group' => 'Student Ledger',
                'description' => 'View student ledger',
            ],
            [
                'name' => 'student-ledger.export',
                'group' => 'Student Ledger',
                'description' => 'Export student ledger',
            ],

            /*
            |--------------------------------------------------------------------------
            | Previous Balances
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'previous-balances.view',
                'group' => 'Previous Balances',
                'description' => 'View previous balances',
            ],
            [
                'name' => 'previous-balances.carry-forward',
                'group' => 'Previous Balances',
                'description' => 'Carry forward previous balances',
            ],

            /*
            |--------------------------------------------------------------------------
            | Monthly Fee Generator
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'monthly-fee-generator.view',
                'group' => 'Monthly Fee Generator',
                'description' => 'View monthly fee generator',
            ],
            [
                'name' => 'monthly-fee-generator.create',
                'group' => 'Monthly Fee Generator',
                'description' => 'Generate monthly fee vouchers',
            ],

            /*
            |--------------------------------------------------------------------------
            | Class Fee Structures
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'class-fee-structures.view',
                'group' => 'Class Fee Structures',
                'description' => 'View class fee structures',
            ],
            [
                'name' => 'class-fee-structures.create',
                'group' => 'Class Fee Structures',
                'description' => 'Create class fee structures',
            ],
            [
                'name' => 'class-fee-structures.edit',
                'group' => 'Class Fee Structures',
                'description' => 'Edit class fee structures',
            ],
            [
                'name' => 'class-fee-structures.delete',
                'group' => 'Class Fee Structures',
                'description' => 'Delete class fee structures',
            ],
            [
                'name' => 'class-fee-structures.import',
                'group' => 'Class Fee Structures',
                'description' => 'Import class fee structures',
            ],

            /*
            |--------------------------------------------------------------------------
            | Monthly Ledger
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'monthly-ledger.view',
                'group' => 'Monthly Ledger',
                'description' => 'View monthly ledger',
            ],

            /*
            |--------------------------------------------------------------------------
            | Fee Matrix
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'fee-matrix.view',
                'group' => 'Fee Matrix',
                'description' => 'View fee matrix',
            ],
            [
                'name' => 'fee-matrix.export',
                'group' => 'Fee Matrix',
                'description' => 'Export fee matrix',
            ],

            /*
            |--------------------------------------------------------------------------
            | Expenses
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'expenses.view',
                'group' => 'Expenses',
                'description' => 'View expenses',
            ],
            [
                'name' => 'expenses.create',
                'group' => 'Expenses',
                'description' => 'Create expenses',
            ],
            [
                'name' => 'expenses.edit',
                'group' => 'Expenses',
                'description' => 'Edit expenses',
            ],
            [
                'name' => 'expenses.delete',
                'group' => 'Expenses',
                'description' => 'Delete expenses',
            ],

            /*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'settings.view',
                'group' => 'Settings',
                'description' => 'View system settings',
            ],
            [
                'name' => 'settings.edit',
                'group' => 'Settings',
                'description' => 'Modify system settings',
            ],

            /*
            |--------------------------------------------------------------------------
            | User Management
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'users.view',
                'group' => 'User Management',
                'description' => 'View users',
            ],
            [
                'name' => 'users.create',
                'group' => 'User Management',
                'description' => 'Create users',
            ],
            [
                'name' => 'users.edit',
                'group' => 'User Management',
                'description' => 'Edit users',
            ],
            [
                'name' => 'users.deactivate',
                'group' => 'User Management',
                'description' => 'Activate or deactivate users',
            ],

            /*
            |--------------------------------------------------------------------------
            | Permissions
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'permissions.view',
                'group' => 'Permissions',
                'description' => 'View permissions',
            ],
            [
                'name' => 'permissions.manage',
                'group' => 'Permissions',
                'description' => 'Manage role permissions',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Insert permissions safely
        |--------------------------------------------------------------------------
        |
        | updateOrInsert means running the seeder again will not create
        | duplicate permissions or alter existing permission IDs.
        |
        */

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                [
                    'group' => $permission['group'],
                    'description' => $permission['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Role permission assignments
        |--------------------------------------------------------------------------
        */

        $allPermissions = DB::table('permissions')
            ->pluck('id', 'name');

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        | Administrator receives every permission.
        */

        foreach ($allPermissions as $permissionId) {
            DB::table('role_permissions')->updateOrInsert(
                [
                    'role' => 'admin',
                    'permission_id' => $permissionId,
                ],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Viewer
        |--------------------------------------------------------------------------
        | Viewer receives read-only permissions.
        */

        $viewerPermissions = [
            'dashboard.view',

            'students.view',
            'students.export',

            'enrollments.view',
            'enrollments.export',

            'classes.view',
            'sessions.view',

            'promotion.view',

            'fee-payments.view',
            'fee-payments.export',
            'fee-payments.receipt',

            'fee-vouchers.view',
            'fee-vouchers.print',

            'student-ledger.view',
            'student-ledger.export',

            'previous-balances.view',

            'monthly-fee-generator.view',

            'class-fee-structures.view',

            'monthly-ledger.view',

            'fee-matrix.view',
            'fee-matrix.export',

            'expenses.view',

            'settings.view',

            'users.view',

            'permissions.view',
        ];

        $this->assignPermissions('viewer', $viewerPermissions, $allPermissions);

        /*
        |--------------------------------------------------------------------------
        | Accountant
        |--------------------------------------------------------------------------
        | Accountant gets operational, student and financial permissions.
        | User/permission administration remains administrator-only.
        */

        $accountantPermissions = [
            'dashboard.view',

            'students.view',
            'students.create',
            'students.edit',
            'students.export',
            'students.import',

            'enrollments.view',
            'enrollments.create',
            'enrollments.edit',
            'enrollments.export',

            'classes.view',

            'sessions.view',

            'fee-payments.view',
            'fee-payments.create',
            'fee-payments.edit',
            'fee-payments.export',
            'fee-payments.receipt',

            'fee-vouchers.view',
            'fee-vouchers.create',
            'fee-vouchers.edit',
            'fee-vouchers.print',

            'student-ledger.view',
            'student-ledger.export',

            'previous-balances.view',
            'previous-balances.carry-forward',

            'monthly-fee-generator.view',
            'monthly-fee-generator.create',

            'class-fee-structures.view',

            'monthly-ledger.view',

            'fee-matrix.view',
            'fee-matrix.export',

            'expenses.view',
            'expenses.create',
            'expenses.edit',

            'settings.view',
        ];

        $this->assignPermissions('accountant', $accountantPermissions, $allPermissions);
    }

    private function assignPermissions(
        string $role,
        array $permissionNames,
        $allPermissions
    ): void {
        foreach ($permissionNames as $permissionName) {
            if (!isset($allPermissions[$permissionName])) {
                continue;
            }

            DB::table('role_permissions')->updateOrInsert(
                [
                    'role' => $role,
                    'permission_id' => $allPermissions[$permissionName],
                ],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}