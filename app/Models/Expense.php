<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_no','category','sub_category','description',
        'amount','expense_date','expense_month',
        'payment_method','paid_to','reference_no','recorded_by','notes',
    ];

    protected $casts = ['expense_date'=>'date','amount'=>'decimal:2'];

    public static function categories(): array
    {
        return [
            'Staff Salary'  => ['Teacher Salary','Admin Salary','Support Staff','Guard/Security'],
            'Utilities'     => ['Electricity','Water','Gas','Internet','Telephone'],
            'Maintenance'   => ['Building Repair','Furniture Repair','Equipment Repair','Cleaning'],
            'Stationery'    => ['Office Stationery','Exam Supplies','Books & Materials'],
            'Miscellaneous' => ['Event/Function','Transportation','Medical','Other'],
        ];
    }

    public static function categoryColor(string $cat): array
    {
        return match($cat) {
            'Staff Salary'  => ['bg'=>'#eff6ff','border'=>'#3b82f6','text'=>'#1d4ed8','icon'=>'fa-users'],
            'Utilities'     => ['bg'=>'#fff7ed','border'=>'#f97316','text'=>'#c2410c','icon'=>'fa-bolt'],
            'Maintenance'   => ['bg'=>'#fefce8','border'=>'#eab308','text'=>'#854d0e','icon'=>'fa-tools'],
            'Stationery'    => ['bg'=>'#f0fdf4','border'=>'#22c55e','text'=>'#166534','icon'=>'fa-pencil-alt'],
            default         => ['bg'=>'#f5f3ff','border'=>'#8b5cf6','text'=>'#6d28d9','icon'=>'fa-tag'],
        };
    }
}
