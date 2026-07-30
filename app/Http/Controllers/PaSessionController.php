<?php

namespace App\Http\Controllers;

use App\Models\PaSession;
use App\Models\PaEnrollment;
use Illuminate\Http\Request;

class PaSessionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $sessions = PaSession::withCount([
                'enrollments as enrolled_students_count' => function ($q) {
                    $q->where('is_active', 1);
                }
            ])
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        return view('sessions.index', compact('sessions'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('sessions.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'session_name' => 'required|string|max:100|unique:pa_sessions,session_name',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
        ]);

        PaSession::create([
            'session_name' => $request->session_name,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'is_active'    => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()
            ->route('sessions.index')
            ->with('success', 'Session "' . $request->session_name . '" created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $session = PaSession::findOrFail($id);

        return view('sessions.edit', compact('session'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $session = PaSession::findOrFail($id);

        $request->validate([
            'session_name' => 'required|string|max:100|unique:pa_sessions,session_name,' . $id,
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
        ]);

        $session->update([
            'session_name' => $request->session_name,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'is_active'    => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()
            ->route('sessions.index')
            ->with('success', 'Session "' . $session->session_name . '" updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | SET AS CURRENT (deactivates all others, activates this one)
    |--------------------------------------------------------------------------
    */

    public function setActive($id)
    {
        // Deactivate all sessions
        PaSession::query()->update(['is_active' => 0]);

        // Activate selected
        $session = PaSession::findOrFail($id);
        $session->update(['is_active' => 1]);

        return redirect()
            ->route('sessions.index')
            ->with('success', '"' . $session->session_name . '" is now the current active session.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $session = PaSession::findOrFail($id);

        $enrolledCount = PaEnrollment::where('session_id', $id)
            ->where('is_active', 1)
            ->count();

        if ($enrolledCount > 0) {
            return redirect()
                ->route('sessions.index')
                ->with('error', 'Cannot delete "' . $session->session_name . '" — it has ' . $enrolledCount . ' active enrollment(s).');
        }

        $session->delete();

        return redirect()
            ->route('sessions.index')
            ->with('success', 'Session deleted successfully.');
    }
}
