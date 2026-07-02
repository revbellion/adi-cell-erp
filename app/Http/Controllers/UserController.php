<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $permissionKeys = config('permissions.LIST');

        $query = User::orderBy('created_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);
        $totalUsers = User::count();

        return view('users.index', compact('users', 'permissionKeys', 'totalUsers'));
    }

    public function show(User $user): View
    {
        $permissionKeys = config('permissions.LIST');
        return view('users.show', compact('user', 'permissionKeys'));
    }

    public function create(): View
    {
        $permissionKeys = config('permissions.LIST');
        return view('users.form', compact('permissionKeys'));
    }

    public function store(Request $request): RedirectResponse
    {
        $permissionKeys = array_column(config('permissions.LIST'), 'key');

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:8',
            'is_admin' => 'nullable|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', $permissionKeys),
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'is_admin' => $request->boolean('is_admin'),
            'permissions' => $request->boolean('is_admin') ? null : $request->permissions,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $permissionKeys = config('permissions.LIST');
        return view('users.form', compact('user', 'permissionKeys'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $permissionKeys = array_column(config('permissions.LIST'), 'key');

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:8',
            'is_admin' => 'nullable|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', $permissionKeys),
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'is_admin' => $request->boolean('is_admin'),
            'permissions' => $request->boolean('is_admin') ? null : $request->permissions,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['Tidak bisa menghapus user admin.']);
        }

        if ($user->id === Auth::id()) {
            return back()->withErrors(['Tidak bisa menghapus akun sendiri.']);
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate(['ids' => 'required|array']);
        $deleted = 0;

        foreach ($request->ids as $id) {
            $user = User::find($id);
            if (!$user || $user->isAdmin() || $user->id === Auth::id()) {
                continue;
            }
            $user->delete();
            $deleted++;
        }

        return redirect()->route('users.index')->with('success', "{$deleted} user berhasil dihapus.");
    }
}
