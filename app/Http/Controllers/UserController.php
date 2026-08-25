<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const ROLES = [
        'admin' => 'Administrator',
        'accountant' => 'Accountant',
        'viewer' => 'Viewer',
    ];

    public function index(Request $request)
    {
        $status = $request->input('status', 'active');
        $role = $request->input('role');
        $search = trim((string) $request->input('search', ''));

        $users = User::query()
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($role, fn ($q) => $q->where('role', $role))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'all' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('users.index', [
            'users' => $users,
            'counts' => $counts,
            'roles' => self::ROLES,
            'status' => $status,
            'role' => $role,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('users.create', ['roles' => self::ROLES]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User account created successfully.');
    }

    public function edit(User $user)
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User account updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        $currentUser = auth()->user();

        if ($currentUser && $currentUser->id === $user->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($user->is_active && $user->role === 'admin') {
            $activeAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->count();

            if ($activeAdmins <= 1) {
                return back()->with('error', 'The last active administrator cannot be deactivated.');
            }
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with(
            'success',
            $user->is_active
                ? "{$user->name} has been activated."
                : "{$user->name} has been deactivated."
        );
    }
}