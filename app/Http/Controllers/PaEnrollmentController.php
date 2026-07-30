<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Student;
use App\Models\PaClass;
use App\Models\PaSession;
use App\Models\PaEnrollment;

class PaEnrollmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Enrollment Listing
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $search        = $request->search;
        $classFilter   = $request->class_id;
        $sessionFilter = $request->session_id;
        $statusFilter  = $request->status;

        $query = PaEnrollment::with(['student', 'class', 'session'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('student_name', 'like', "%{$search}%")
                       ->orWhere('admission_no', 'like', "%{$search}%")
                       ->orWhere('father_name', 'like', "%{$search}%");
                });
            })
            ->when($classFilter, function ($q) use ($classFilter) {
                $q->where('class_id', $classFilter);
            })
            ->when($sessionFilter, function ($q) use ($sessionFilter) {
                $q->where('session_id', $sessionFilter);
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->latest();

        $enrollments = $query->paginate(25)->withQueryString();

        $classes  = PaClass::orderBy('class_order')->get();
        $sessions = PaSession::orderBy('id')->get();

        // Hero stats — independent of the current filters/pagination.
        $stats = [
            'total'    => PaEnrollment::count(),
            'active'   => PaEnrollment::where('status', 'active')->count(),
            'inactive' => PaEnrollment::where('status', '!=', 'active')->count(),
            'sessions' => PaSession::where('is_active', 1)->count(),
        ];

        return view(
            'enrollments.index',
            compact('enrollments', 'classes', 'sessions', 'stats')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Enrollment Form
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $students = Student::orderBy('student_name')
            ->get();

        $classes = PaClass::where('is_active', 1)
            ->orderBy('class_order')
            ->get();

        $sessions = PaSession::where('is_active', 1)
            ->orderBy('id')
            ->get();

        return view(
            'enrollments.create',
            compact(
                'students',
                'classes',
                'sessions'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store Enrollment
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'student_id' => 'required|exists:students,id',

            'class_id' => 'required|exists:pa_classes,id',

            'session_id' => 'required|exists:pa_sessions,id',

            'roll_no' => 'nullable|max:50',

            'enrollment_date' => 'nullable|date',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Enrollment
        |--------------------------------------------------------------------------
        */

        $alreadyExists = PaEnrollment::where(
                'student_id',
                $request->student_id
            )
            ->where(
                'class_id',
                $request->class_id
            )
            ->where(
                'session_id',
                $request->session_id
            )
            ->exists();

        if ($alreadyExists) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Student already enrolled in this class and session.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Create Enrollment
        |--------------------------------------------------------------------------
        */

        PaEnrollment::create([

            'student_id' => $request->student_id,

            'class_id' => $request->class_id,

            'session_id' => $request->session_id,

            'roll_no' => $request->roll_no,

            'enrollment_date' => $request->enrollment_date,

            'is_active' => 1,

            'status' => 'active',
        ]);

        return redirect()
            ->route('enrollments.index')
            ->with(
                'success',
                'Enrollment created successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Enrollment Form
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $enrollment = PaEnrollment::with(['student', 'class', 'session'])
            ->findOrFail($id);

        $classes = PaClass::where('is_active', 1)
            ->orderBy('class_order')
            ->get();

        $sessions = PaSession::orderBy('id')->get();

        return view(
            'enrollments.edit',
            compact('enrollment', 'classes', 'sessions')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Enrollment
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $enrollment = PaEnrollment::findOrFail($id);

        $request->validate([
            'class_id'        => 'required|exists:pa_classes,id',
            'session_id'      => 'required|exists:pa_sessions,id',
            'roll_no'         => 'nullable|max:50',
            'enrollment_date' => 'nullable|date',
            'status'          => 'required|in:active,inactive,left,passed_out',
            'monthly_fee'     => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        // Block duplicate class+session combination on another record.
        $duplicate = PaEnrollment::where('student_id', $enrollment->student_id)
            ->where('class_id', $request->class_id)
            ->where('session_id', $request->session_id)
            ->where('id', '!=', $enrollment->id)
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->with('error', 'Student already has another enrollment record for this class and session.');
        }

        $enrollment->update([
            'class_id'         => $request->class_id,
            'session_id'       => $request->session_id,
            'roll_no'          => $request->roll_no,
            'enrollment_date'  => $request->enrollment_date,
            'status'           => $request->status,
            'is_active'        => $request->status === 'active' ? 1 : 0,
            'monthly_fee'      => $request->monthly_fee ?? 0,
            'discount_amount'  => $request->discount_amount ?? 0,
            'notes'            => $request->notes,
        ]);

        return redirect()
            ->route('enrollments.index')
            ->with('success', 'Enrollment updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Quick Status Toggle (Active ⇄ Inactive)
    |--------------------------------------------------------------------------
    */

    public function toggleStatus($id)
    {
        $enrollment = PaEnrollment::with('student')->findOrFail($id);

        $activating = $enrollment->status !== 'active';

        $enrollment->update([
            'status'    => $activating ? 'active' : 'inactive',
            'is_active' => $activating ? 1 : 0,
        ]);

        $label = $activating ? 'activated' : 'deactivated';

        return back()->with(
            'success',
            'Enrollment for "' . ($enrollment->student->student_name ?? 'student') . '" has been ' . $label . '.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Enrollment
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $enrollment = PaEnrollment::with('student')->findOrFail($id);
        $name = $enrollment->student->student_name ?? 'Enrollment';

        $enrollment->delete();

        return redirect()
            ->route('enrollments.index')
            ->with('success', $name . '\'s enrollment record was deleted.');
    }

    /*
    |--------------------------------------------------------------------------
    | Export Enrollments (CSV — honours current filters)
    |--------------------------------------------------------------------------
    */

    public function export(Request $request)
    {
        $search        = $request->search;
        $classFilter   = $request->class_id;
        $sessionFilter = $request->session_id;
        $statusFilter  = $request->status;

        $enrollments = PaEnrollment::with(['student', 'class', 'session'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('student_name', 'like', "%{$search}%")
                       ->orWhere('admission_no', 'like', "%{$search}%")
                       ->orWhere('father_name', 'like', "%{$search}%");
                });
            })
            ->when($classFilter, fn($q) => $q->where('class_id', $classFilter))
            ->when($sessionFilter, fn($q) => $q->where('session_id', $sessionFilter))
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->latest()
            ->get();

        $filename = 'enrollments_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'Admission No', 'Student Name', 'Father Name', 'Class', 'Session',
            'Roll No', 'Enrollment Date', 'Status', 'Monthly Fee', 'Discount', 'Mobile No',
        ];

        $callback = function () use ($enrollments, $columns) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM so Excel renders special characters correctly.
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, $columns);

            foreach ($enrollments as $enrollment) {
                fputcsv($file, [
                    $enrollment->student->admission_no ?? '',
                    $enrollment->student->student_name ?? '',
                    $enrollment->student->father_name ?? '',
                    $enrollment->class->class_name ?? '',
                    $enrollment->session->session_name ?? '',
                    $enrollment->roll_no,
                    $enrollment->enrollment_date,
                    ucfirst($enrollment->status ?? ($enrollment->is_active ? 'active' : 'inactive')),
                    $enrollment->monthly_fee,
                    $enrollment->discount_amount,
                    $enrollment->student->mobile_no ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /*
    |--------------------------------------------------------------------------
    | Promotion Preview Screen
    |--------------------------------------------------------------------------
    */

    public function promotionPreview()
    {
        $sessions = PaSession::where('is_active', 1)
            ->orderBy('id')
            ->get();

        $classes = PaClass::where('is_active', 1)
            ->orderBy('class_order')
            ->get();

        $students = PaEnrollment::with([
                'student',
                'class',
                'session'
            ])
            ->where('is_active', 1)
            ->get();

        return view(
            'promotion.preview',
            compact(
                'sessions',
                'classes',
                'students'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Promotion Process
    |--------------------------------------------------------------------------
    */

    public function bulkPromote(Request $request)
    {
        $request->validate([

            'from_session_id' => 'required|exists:pa_sessions,id',

            'to_session_id' => 'required|exists:pa_sessions,id',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Prevent Same Session Promotion
        |--------------------------------------------------------------------------
        */

        if (
            $request->from_session_id ==
            $request->to_session_id
        ) {
            return back()->with(
                'error',
                'Source and target session cannot be same.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Active Enrollments
        |--------------------------------------------------------------------------
        */

        $enrollments = PaEnrollment::with('class')
            ->where(
                'session_id',
                $request->from_session_id
            )
            ->where('is_active', 1)
            ->get();

        $promotedCount = 0;

        foreach ($enrollments as $enrollment) {

            /*
            |--------------------------------------------------------------------------
            | Find Next Class
            |--------------------------------------------------------------------------
            */

            $nextClass = PaClass::where(
                    'class_order',
                    '>',
                    $enrollment->class->class_order
                )
                ->where('is_active', 1)
                ->orderBy('class_order')
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Skip Final Class
            |--------------------------------------------------------------------------
            */

            if (!$nextClass) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Promotion
            |--------------------------------------------------------------------------
            */

            $alreadyExists = PaEnrollment::where(
                    'student_id',
                    $enrollment->student_id
                )
                ->where(
                    'session_id',
                    $request->to_session_id
                )
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Create New Enrollment
            |--------------------------------------------------------------------------
            */

            PaEnrollment::create([

                'student_id' => $enrollment->student_id,

                'session_id' => $request->to_session_id,

                'class_id' => $nextClass->id,

                'roll_no' => $enrollment->roll_no,

                'enrollment_date' => now(),

                'is_active' => 1,
            ]);

            $promotedCount++;
        }

        return back()->with(
            'success',
            $promotedCount . ' students promoted successfully.'
        );
    }
}