<?php

namespace App\Http\Controllers;

use App\Models\PaClass;
use App\Models\PaEnrollment;
use Illuminate\Http\Request;

class PaClassController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $classes = PaClass::withCount([
                'enrollments as active_students_count' => function ($q) {
                    $q->where('is_active', 1);
                }
            ])
            ->orderBy('class_order')
            ->get();

        return view('classes.index', compact('classes'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        // Suggest next class_order value
        $nextOrder = PaClass::max('class_order') + 1;

        return view('classes.create', compact('nextOrder'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'class_name'  => 'required|string|max:100|unique:pa_classes,class_name',
            'class_code'  => 'nullable|string|max:20|unique:pa_classes,class_code',
            'class_order' => 'required|integer|min:1',
        ]);

        PaClass::create([
            'class_name'  => $request->class_name,
            'class_code'  => $request->class_code,
            'class_order' => $request->class_order,
            'is_active'   => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()
            ->route('classes.index')
            ->with('success', 'Class "' . $request->class_name . '" created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $class = PaClass::findOrFail($id);

        return view('classes.edit', compact('class'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $class = PaClass::findOrFail($id);

        $request->validate([
            'class_name'  => 'required|string|max:100|unique:pa_classes,class_name,' . $id,
            'class_code'  => 'nullable|string|max:20|unique:pa_classes,class_code,' . $id,
            'class_order' => 'required|integer|min:1',
        ]);

        $class->update([
            'class_name'  => $request->class_name,
            'class_code'  => $request->class_code,
            'class_order' => $request->class_order,
            'is_active'   => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()
            ->route('classes.index')
            ->with('success', 'Class "' . $class->class_name . '" updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE ACTIVE STATUS
    |--------------------------------------------------------------------------
    */

    public function toggleStatus($id)
    {
        $class = PaClass::findOrFail($id);

        $class->update(['is_active' => !$class->is_active]);

        $status = $class->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('classes.index')
            ->with('success', 'Class "' . $class->class_name . '" has been ' . $status . '.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $class = PaClass::findOrFail($id);

        // Prevent deletion if students are enrolled
        $enrolledCount = PaEnrollment::where('class_id', $id)
            ->where('is_active', 1)
            ->count();

        if ($enrolledCount > 0) {
            return redirect()
                ->route('classes.index')
                ->with('error', 'Cannot delete "' . $class->class_name . '" — it has ' . $enrolledCount . ' active student(s) enrolled.');
        }

        $class->delete();

        return redirect()
            ->route('classes.index')
            ->with('success', 'Class deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | REORDER (AJAX) — saves drag-drop order
    |--------------------------------------------------------------------------
    */

    public function reorder(Request $request)
    {
        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:pa_classes,id',
        ]);

        foreach ($request->order as $position => $classId) {
            PaClass::where('id', $classId)
                ->update(['class_order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }
}
