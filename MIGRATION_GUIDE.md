# Student / PaStudent Consolidation — Migration Guide

## What Changed

The original codebase had **two models pointing at the same `students` table**, causing confusion:

| Old | New |
|-----|-----|
| `App\Models\Student` | ✅ **Kept** — now the single canonical model |
| `App\Models\PaStudent` | ❌ **Delete this file** |
| `PaStudentController` | ❌ **Delete this file** |
| Route names `pa_students.*` | ❌ **Removed** — now `students.*` |

---

## Files to DELETE from your project

```
app/Models/PaStudent.php
app/Http/Controllers/PaStudentController.php
resources/views/pa_students/           ← entire folder (rename to students/)
```

---

## Files to REPLACE (copy from this patch)

| This patch file | Goes to |
|-----------------|---------|
| `app/Models/Student.php` | `app/Models/Student.php` |
| `app/Models/PaEnrollment.php` | `app/Models/PaEnrollment.php` |
| `app/Models/FeeVoucher.php` | `app/Models/FeeVoucher.php` |
| `app/Models/FeePayment.php` | `app/Models/FeePayment.php` |
| `app/Http/Controllers/StudentController.php` | `app/Http/Controllers/StudentController.php` |
| `app/Http/Controllers/DashboardController.php` | `app/Http/Controllers/DashboardController.php` |
| `app/Http/Controllers/FeeVoucherController.php` | `app/Http/Controllers/FeeVoucherController.php` |
| `app/Http/Controllers/MonthlyFeeGeneratorController.php` | `app/Http/Controllers/MonthlyFeeGeneratorController.php` |
| `routes/web.php` | `routes/web.php` |

---

## View Folder Rename

Rename `resources/views/pa_students/` → `resources/views/students/`

The views themselves need one change each — update all route references:

| Old route | New route |
|-----------|-----------|
| `pa_students.index` | `students.index` |
| `pa_students.create` | `students.create` |
| `pa_students.store` | `students.store` |
| `pa_students.show` | `students.show` |
| `pa_students.edit` | `students.edit` |
| `pa_students.update` | `students.update` |
| `pa_students.destroy` | `students.destroy` |

Quick bash command to do all view renames at once:
```bash
cd resources/views
cp -r pa_students students
grep -rl "pa_students\." students/ | xargs sed -i "s/pa_students\./students\./g"
```

---

## Other Controllers — No Changes Needed

`PaPromotionController`, `PaEnrollmentController`, `ClassFeeStructureController`, and `FeePaymentController` already worked correctly and are **unchanged**.

---

## Summary of Model Relationships (After Fix)

```
Student
  ├── hasMany → PaEnrollment   (student_id)
  ├── hasMany → FeeVoucher     (student_id)
  └── hasMany → FeePayment     (student_id)

PaEnrollment
  ├── belongsTo → Student      (student_id)   ← was PaStudent, now Student
  ├── belongsTo → PaClass      (class_id)
  └── belongsTo → PaSession    (session_id)

FeeVoucher
  ├── belongsTo → Student      (student_id)   ← was Student (same table, now clean)
  ├── hasMany   → FeeVoucherItem
  └── hasMany   → FeePayment

FeePayment
  ├── belongsTo → Student      (student_id)   ← was Student (same table, now clean)
  └── belongsTo → FeeVoucher
```
