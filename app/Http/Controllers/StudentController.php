<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\PaEnrollment;
use App\Models\PaClass;
use App\Models\PaSession;
use App\Models\FeeVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $search = $request->search;
        $classFilter = $request->class_id;
        $sessionFilter = $request->session_id;
        $statusFilter = $request->status;
        $familyCodeFilter = $request->family_code;

        $students = Student::with([
                'enrollments.class',
                'enrollments.session',
            ])
            ->when($search, function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%");
            })
            ->when($statusFilter !== null && $statusFilter !== '', function ($q) use ($statusFilter) {
                $q->where('is_active', $statusFilter);
            })
            ->when($classFilter, function ($q) use ($classFilter) {
                $q->whereHas('enrollments', function ($eq) use ($classFilter) {
                    $eq->where('class_id', $classFilter)->where('is_active', 1);
                });
            })
            ->when($sessionFilter, function ($q) use ($sessionFilter) {
                $q->whereHas('enrollments', function ($eq) use ($sessionFilter) {
                    $eq->where('session_id', $sessionFilter)->where('is_active', 1);
                });
            })
            ->when($familyCodeFilter, function ($q) use ($familyCodeFilter) {
                $q->where('family_code', $familyCodeFilter);
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $classes  = PaClass::orderBy('class_order')->get();
        $sessions = PaSession::where('is_active', 1)->get();

        // When viewing a single family (via the "Family" badge on a row),
        // show the combined outstanding balance across every sibling in
        // that family so staff don't have to add it up manually.
        $familyOutstanding = null;
        if ($familyCodeFilter) {
            $familyStudentIds = Student::where('family_code', $familyCodeFilter)->pluck('id');
            $familyOutstanding = FeeVoucher::whereIn('student_id', $familyStudentIds)->sum('balance_amount');
        }

        return view('students.index', compact(
            'students',
            'classes',
            'sessions',
            'familyCodeFilter',
            'familyOutstanding'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $classes  = PaClass::orderBy('class_order')->get();
        $sessions = PaSession::where('is_active', 1)->get();

        return view('students.create', compact('classes', 'sessions'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE STUDENT + AUTO ENROLLMENT
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'class_id'     => 'required|exists:pa_classes,id',
            'session_id'   => 'required|exists:pa_sessions,id',
            'student_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Auto-generate admission number (max-based, not count-based — a
        // count breaks as soon as any student has ever been deleted or a
        // number was skipped, causing duplicate-key collisions like
        // ADM-2026-0100 already existing while count() still says 99).
        $admissionNo = $this->generateAdmissionNo();

        // Family Code — auto-generated unless the staff typed one in (e.g.
        // to link this student to an already-existing family group).
        $familyCode = $request->filled('family_code')
            ? strtoupper(trim($request->family_code))
            : $this->generateFamilyCode();

        // Handle photo upload
        $imagePath = null;
        if ($request->hasFile('student_image')) {
            $imagePath = $request->file('student_image')
                ->store('students', 'public');
            $imagePath = basename($imagePath);
        }

        // Create student
        $student = Student::create([
            'admission_no'      => $admissionNo,
            'family_code'       => $familyCode,
            'student_name'      => $request->student_name,
            'father_name'       => $request->father_name,
            'mother_name'       => $request->mother_name,
            'gender'            => $request->gender,
            'date_of_birth'     => $request->date_of_birth,
            'blood_group'       => $request->blood_group,
            'religion'          => $request->religion,
            'b_form_no'         => $request->b_form_no,
            'mobile_no'         => $request->mobile_no,
            'mother_mobile_no'  => $request->mother_mobile_no,
            'whatsapp_no'       => $request->whatsapp_no,
            'mother_whatsapp_no' => $request->mother_whatsapp_no,
            'address'           => $request->address,
            'guardian_name'     => $request->guardian_name,
            'guardian_relation' => $request->guardian_relation,
            'guardian_mobile'   => $request->guardian_mobile,
            'father_occupation' => $request->father_occupation,
            'admission_date'    => $request->admission_date ?? now()->toDateString(),
            'previous_school'   => $request->previous_school,
            'previous_class'    => $request->previous_class,
            'student_image'     => $imagePath,
            'is_active'         => 1,
        ]);

        // Auto-create enrollment
        PaEnrollment::create([
            'student_id'      => $student->id,
            'class_id'        => $request->class_id,
            'session_id'      => $request->session_id,
            'roll_no'         => null,
            'enrollment_date' => now()->toDateString(),
            'status'          => 'active',
            'is_active'       => 1,
        ]);

        return redirect()
            ->route('students.show', $student->id)
            ->with('success', 'Student admitted successfully. Admission No: ' . $admissionNo);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW PROFILE
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $student = Student::findOrFail($id);

        $enrollments = PaEnrollment::with(['class', 'session'])
            ->where('student_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        $vouchers = FeeVoucher::with('payments')
            ->where('student_id', $id)
            ->latest()
            ->get();

        // Flatten every payment/receipt across all vouchers for this student,
        // newest first, so we can show a single "Receipts" section on the
        // profile with a direct link to each receipt's edit/print page.
        $receipts = $vouchers->flatMap(function ($voucher) {
                return $voucher->payments->map(function ($payment) use ($voucher) {
                    // Voucher is already loaded — attach it directly instead
                    // of letting the view lazy-load it per row (N+1).
                    $payment->setRelation('voucher', $voucher);
                    return $payment;
                });
            })
            ->sortByDesc(function ($payment) {
                return $payment->payment_date ?? $payment->created_at;
            })
            ->values();

        // Siblings — other students sharing this student's family_code,
        // each annotated with their own outstanding balance so the profile
        // can show a combined family total.
        $siblings = collect();
        $siblingsOutstanding = 0;

        if ($student->family_code) {
            $siblings = Student::with(['enrollments.class'])
                ->where('family_code', $student->family_code)
                ->where('id', '!=', $student->id)
                ->get()
                ->map(function ($sibling) {
                    $sibling->setAttribute(
                        'sibling_outstanding',
                        $sibling->vouchers()->sum('balance_amount')
                    );
                    return $sibling;
                });

            $siblingsOutstanding = $siblings->sum('sibling_outstanding')
                + $vouchers->sum('balance_amount');
        }

        return view('students.show', compact(
            'student',
            'enrollments',
            'vouchers',
            'receipts',
            'siblings',
            'siblingsOutstanding'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $student  = Student::findOrFail($id);
        $classes  = PaClass::orderBy('class_order')->get();
        $sessions = PaSession::where('is_active', 1)->get();

        return view('students.edit', compact('student', 'classes', 'sessions'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STUDENT
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $request->validate([
            'student_name'  => 'required|string|max:255',
            'student_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $student = Student::findOrFail($id);

        // Handle photo upload
        $imagePath = $student->student_image;
        if ($request->hasFile('student_image')) {
            // Delete old photo if exists
            if ($imagePath) {
                Storage::disk('public')->delete('students/' . $imagePath);
            }
            $imagePath = basename(
                $request->file('student_image')->store('students', 'public')
            );
        }

        $student->update([
            'admission_no'      => $request->admission_no ?? $student->admission_no,
            'family_code'       => $request->filled('family_code')
                                        ? strtoupper(trim($request->family_code))
                                        : $student->family_code,
            'student_name'      => $request->student_name,
            'father_name'       => $request->father_name,
            'mother_name'       => $request->mother_name,
            'gender'            => $request->gender,
            'date_of_birth'     => $request->date_of_birth,
            'blood_group'       => $request->blood_group,
            'religion'          => $request->religion,
            'b_form_no'         => $request->b_form_no,
            'mobile_no'         => $request->mobile_no,
            'mother_mobile_no'  => $request->mother_mobile_no,
            'whatsapp_no'       => $request->whatsapp_no,
            'mother_whatsapp_no' => $request->mother_whatsapp_no,
            'address'           => $request->address,
            'guardian_name'     => $request->guardian_name,
            'guardian_relation' => $request->guardian_relation,
            'guardian_mobile'   => $request->guardian_mobile,
            'father_occupation' => $request->father_occupation,
            'admission_date'    => $request->admission_date,
            'previous_school'   => $request->previous_school,
            'previous_class'    => $request->previous_class,
            'student_image'     => $imagePath,
            'is_active'         => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()
            ->route('students.show', $student->id)
            ->with('success', 'Student record updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | SERVE STUDENT PHOTO (symlink-independent)
    |--------------------------------------------------------------------------
    |
    | On many Windows/XAMPP setups, `php artisan storage:link` either was
    | never run or fails silently without admin rights, leaving
    | public/storage as an empty real folder instead of a symlink to
    | storage/app/public. That breaks every student photo <img> tag that
    | points at asset('storage/...'). This route streams the file straight
    | from the storage disk instead, so photos always load regardless of
    | the symlink's state.
    |--------------------------------------------------------------------------
    */

    public function photo(string $filename)
    {
        // Guard against path traversal — only allow a bare filename.
        $filename = basename($filename);
        $path = 'students/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('public')->path($path);

        return response()->file($fullPath, [
            'Content-Type'  => Storage::disk('public')->mimeType($path) ?: 'image/jpeg',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FAMILY / SIBLINGS
    |--------------------------------------------------------------------------
    */

    /**
     * AJAX lookup used by the "Link an Existing Sibling" search box on the
     * create/edit forms. Returns matching students with their current
     * family_code (if any) so the frontend can offer to link them.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Student::with('enrollments.class')
            ->where(function ($query) use ($q) {
                $query->where('student_name', 'like', "%{$q}%")
                      ->orWhere('admission_no', 'like', "%{$q}%")
                      ->orWhere('father_name', 'like', "%{$q}%");
            })
            ->when($request->exclude_id, function ($query) use ($request) {
                $query->where('id', '!=', $request->exclude_id);
            })
            ->limit(10)
            ->get()
            ->map(function ($s) {
                $enrollment = $s->enrollments->last();

                return [
                    'id'           => $s->id,
                    'student_name' => $s->student_name,
                    'admission_no' => $s->admission_no,
                    'class_name'   => $enrollment && $enrollment->class ? $enrollment->class->class_name : null,
                    'family_code'  => $s->family_code,
                ];
            });

        return response()->json($results);
    }

    /**
     * Ensures the given student has a family_code (generating one if they
     * don't), then returns it. Called when staff "link" a sibling found via
     * the search box, so both students end up sharing the same code even if
     * the existing one had never been assigned a family_code before.
     */
    public function assignFamilyCode($id)
    {
        $student = Student::findOrFail($id);

        if (!$student->family_code) {
            $student->family_code = $this->generateFamilyCode();
            $student->save();
        }

        return response()->json(['family_code' => $student->family_code]);
    }

    /**
     * Generates the next admission number for the current year, e.g.
     * ADM-2026-0101. Based on the highest existing number for the year
     * (not a row count), with a collision-safety loop in case of gaps
     * from deleted students or concurrent requests.
     */
    private function generateAdmissionNo(): string
    {
        $prefix = 'ADM-' . date('Y') . '-';

        $last = Student::where('admission_no', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(admission_no, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->value('admission_no');

        $next = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        $admissionNo = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);

        // Extra safety net — keep bumping if that number somehow already exists.
        while (Student::where('admission_no', $admissionNo)->exists()) {
            $next++;
            $admissionNo = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        }

        return $admissionNo;
    }

    /**
     * Generates the next sequential family code, e.g. FAM-0001, FAM-0002...
     */
    private function generateFamilyCode(): string
    {
        $last = Student::where('family_code', 'like', 'FAM-%')
            ->orderByRaw('CAST(SUBSTRING(family_code, 5) AS UNSIGNED) DESC')
            ->value('family_code');

        $next = 1;
        if ($last && preg_match('/FAM-(\d+)/', $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return 'FAM-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        // Delete photo if exists
        if ($student->student_image) {
            Storage::disk('public')->delete('students/' . $student->student_image);
        }

        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }
}