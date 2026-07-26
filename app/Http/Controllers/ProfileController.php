<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
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
        $profile = UserProfile::query()->firstOrNew(['user_id' => $user->id]);
        $meta = (array) ($profile->meta ?? []);

        return view('profile.edit', [
            'user' => $user,
            'username' => $username,
            'pwaSettings' => [
                'enabled' => (bool) ($meta['pwa_enabled'] ?? false),
                'title' => (string) ($meta['pwa_title'] ?? config('app.name', 'Egitim Portali')),
                'subtitle' => (string) ($meta['pwa_subtitle'] ?? 'Yukleniyor...'),
                'logo_url' => (string) ($meta['pwa_logo_url'] ?? url('/public/logo192.png')),
            ],
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
            'pwa_enabled' => ['nullable', 'boolean'],
            'pwa_title' => ['nullable', 'string', 'max:60'],
            'pwa_subtitle' => ['nullable', 'string', 'max:80'],
            'pwa_logo_url' => ['nullable', 'string', 'max:255'],
        ], [
            'username.regex' => 'Kullanici adi sadece harf, rakam, nokta, alt cizgi ve kisa cizgi icerebilir.',
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
            return back()->withErrors(['username' => 'Bu kullanici adi zaten kullanilmada.'])->withInput();
        }

        $user->name = $fullName;
        $user->email = $newEmail;
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        $profile = UserProfile::query()->firstOrNew(['user_id' => $user->id]);
        $meta = (array) ($profile->meta ?? []);
        $meta['pwa_enabled'] = (bool) ($validated['pwa_enabled'] ?? false);
        $meta['pwa_title'] = trim((string) ($validated['pwa_title'] ?? '')) ?: config('app.name', 'Egitim Portali');
        $meta['pwa_subtitle'] = trim((string) ($validated['pwa_subtitle'] ?? '')) ?: 'Yukleniyor...';
        $meta['pwa_logo_url'] = trim((string) ($validated['pwa_logo_url'] ?? '')) ?: url('/public/logo192.png');
        $profile->meta = $meta;
        $profile->save();

        return back()->with('success', 'Profil bilgileriniz guncellendi.');
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'pwa_enabled' => ['nullable', 'boolean'],
            'pwa_title' => ['nullable', 'string', 'max:60'],
            'pwa_subtitle' => ['nullable', 'string', 'max:80'],
            'pwa_logo_url' => ['nullable', 'string', 'max:255'],
        ]);

        $profile = UserProfile::query()->firstOrNew(['user_id' => $user->id]);
        $meta = (array) ($profile->meta ?? []);
        $meta['pwa_enabled'] = (bool) ($validated['pwa_enabled'] ?? false);
        $meta['pwa_title'] = trim((string) ($validated['pwa_title'] ?? '')) ?: config('app.name', 'Egitim Portali');
        $meta['pwa_subtitle'] = trim((string) ($validated['pwa_subtitle'] ?? '')) ?: 'Yukleniyor...';
        $meta['pwa_logo_url'] = trim((string) ($validated['pwa_logo_url'] ?? '')) ?: url('/public/logo192.png');
        $profile->meta = $meta;
        $profile->save();

        return back()->with('success', 'Logo ve acilis ekranı ayarlari guncellendi.');
    }
}
