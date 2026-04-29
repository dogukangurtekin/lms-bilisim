<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $roleFilter = trim((string) $request->query('role', ''));
        $users = User::query()
            ->with(['role', 'teacher'])
            ->when($roleFilter !== '', fn ($q) => $q->whereHas('role', fn ($r) => $r->where('slug', $roleFilter)))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();
        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();
        return view('users.index', compact('users', 'classes', 'roleFilter'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:4'],
            'role' => ['required', 'in:admin,teacher,student'],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
        ]);

        $role = Role::query()->where('slug', $data['role'])->firstOrFail();
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        if ($data['role'] === 'teacher') {
            Teacher::query()->firstOrCreate(['user_id' => $user->id], ['branch' => null, 'phone' => null, 'hire_date' => null]);
        }
        if ($data['role'] === 'student') {
            Student::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['school_class_id' => $data['school_class_id'] ?? null, 'student_no' => null]
            );
        }

        return redirect()->route('users.index')->with('ok', 'Kullanici eklendi.');
    }

    public function destroy(User $user)
    {
        if ($user->hasRole('admin')) {
            return redirect()->route('users.index')->with('err', 'Admin hesabi silinemez.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('ok', 'Kullanici silindi.');
    }
}

