<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    private function ensureNotSuperadminTarget(User $user): void
    {
        if ($user->hasRole('Superadmin') && !auth()->user()?->hasRole('Superadmin')) {
            abort(403);
        }
    }

    private function sanitizeRolesForNonSuperadmin(array $roles): array
    {
        if (!auth()->user()?->hasRole('Superadmin')) {
            return array_values(array_filter($roles, fn ($role) => $role !== 'Superadmin'));
        }

        return $roles;
    }

    public function index()
    {
        $users = User::with(['roles', 'permissions'])
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Superadmin');
            })
            ->orderBy('name')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::where('name', '!=', 'Superadmin')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('users.create', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
            'pnpki_full_name' => ['nullable', 'string', 'max:255'],
            'pnpki_serial_number' => ['nullable', 'string', 'max:100'],
            'pnpki_valid_until' => ['nullable', 'date'],
            'pnpki_certificate' => ['nullable', 'file', 'mimes:cer,crt,pem,p12,pfx', 'max:5120'],
        ]);

        $roles = $this->sanitizeRolesForNonSuperadmin($validated['roles'] ?? []);
        if (!auth()->user()?->hasRole('Superadmin') && in_array('Superadmin', $validated['roles'] ?? [], true)) {
            return back()->withErrors(['roles' => 'You are not allowed to assign the Superadmin role.'])->withInput();
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'] ?? \Illuminate\Support\Str::random(32)),
            'password_set_at' => empty($validated['password']) ? null : now(),
            'pnpki_full_name' => $validated['pnpki_full_name'] ?? null,
            'pnpki_serial_number' => $validated['pnpki_serial_number'] ?? null,
            'pnpki_valid_until' => $validated['pnpki_valid_until'] ?? null,
        ]);

        if ($request->hasFile('pnpki_certificate')) {
            $file = $request->file('pnpki_certificate');
            if ($file && $file->isValid()) {
                $filename = time() . '_' . uniqid() . '_pnpki_' . str_replace(' ', '_', $file->getClientOriginalName() ?: 'certificate');
                $path = $file->storeAs('pnpki-certificates', $filename, 'public');
                $user->pnpki_certificate_path = $path;
                $user->save();
            }
        }

        $user->syncRoles($roles);
        $user->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->ensureNotSuperadminTarget($user);
        $user->load(['roles', 'permissions']);
        $roles = Role::where('name', '!=', 'Superadmin')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureNotSuperadminTarget($user);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
            'pnpki_full_name' => ['nullable', 'string', 'max:255'],
            'pnpki_serial_number' => ['nullable', 'string', 'max:100'],
            'pnpki_valid_until' => ['nullable', 'date'],
            'pnpki_certificate' => ['nullable', 'file', 'mimes:cer,crt,pem,p12,pfx', 'max:5120'],
            'pnpki_certificate_clear' => ['nullable', 'boolean'],
        ]);

        $roles = $this->sanitizeRolesForNonSuperadmin($validated['roles'] ?? []);
        if (!auth()->user()?->hasRole('Superadmin') && in_array('Superadmin', $validated['roles'] ?? [], true)) {
            return back()->withErrors(['roles' => 'You are not allowed to assign the Superadmin role.'])->withInput();
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->pnpki_full_name = $validated['pnpki_full_name'] ?? null;
        $user->pnpki_serial_number = $validated['pnpki_serial_number'] ?? null;
        $user->pnpki_valid_until = $validated['pnpki_valid_until'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->password_set_at = now();
        }

        if (!empty($validated['pnpki_certificate_clear']) && !empty($user->pnpki_certificate_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->pnpki_certificate_path);
            $user->pnpki_certificate_path = null;
        }

        if ($request->hasFile('pnpki_certificate')) {
            $file = $request->file('pnpki_certificate');
            if ($file && $file->isValid()) {
                if (!empty($user->pnpki_certificate_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($user->pnpki_certificate_path);
                }
                $filename = time() . '_' . uniqid() . '_pnpki_' . str_replace(' ', '_', $file->getClientOriginalName() ?: 'certificate');
                $path = $file->storeAs('pnpki-certificates', $filename, 'public');
                $user->pnpki_certificate_path = $path;
            }
        }

        $user->save();
        $user->syncRoles($roles);
        $user->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->ensureNotSuperadminTarget($user);
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
