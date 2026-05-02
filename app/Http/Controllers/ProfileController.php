<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $email = (string) ($user->email ?? '');
        $username = $email !== '' ? (string) strtok($email, '@') : '';

        return view('profile.edit', [
            'user' => $user,
            'username' => $username,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9._-]+$/i'],
            'password' => ['nullable', 'string', 'min:6', 'max:72', 'confirmed'],
        ], [
            'username.regex' => 'Kullanıcı adı sadece harf, rakam, nokta, alt çizgi ve kısa çizgi içerebilir.',
        ]);

        $first = trim($validated['first_name']);
        $last = trim($validated['last_name']);
        $fullName = trim($first . ' ' . $last);

        $currentEmail = (string) ($user->email ?? '');
        $domain = str_contains($currentEmail, '@') ? (string) substr($currentEmail, strpos($currentEmail, '@') + 1) : 'school.local';
        $newEmail = strtolower(trim($validated['username'])) . '@' . strtolower($domain);

        $exists = \App\Models\User::query()
            ->where('id', '!=', $user->id)
            ->whereRaw('LOWER(email) = ?', [strtolower($newEmail)])
            ->exists();
        if ($exists) {
            return back()->withErrors(['username' => 'Bu kullanıcı adı zaten kullanımda.'])->withInput();
        }

        $user->name = $fullName;
        $user->email = $newEmail;
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return back()->with('success', 'Profil bilgileriniz güncellendi.');
    }
}

