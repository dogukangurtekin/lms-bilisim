<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class ActivityRunnerController extends Controller
{
    public function block3d()
    {
        return $this->serveRunner('block-3d-runner');
    }

    public function blockGrid()
    {
        return $this->serveRunner('block-grid-runner');
    }

    public function computeIt()
    {
        return $this->serveRunner('compute-it-runner');
    }

    public function lightbot()
    {
        return $this->serveRunner('lightbot-runner');
    }

    public function lineTrace()
    {
        return $this->serveRunner('line-trace-runner');
    }

    public function silentTeacher()
    {
        return $this->serveRunner('silent-teacher-runner');
    }

    public function open(Request $request, string $slug)
    {
        $games = array_keys(ActivityController::games());
        if (! in_array($slug, $games, true)) {
            abort(404);
        }

        $user = auth()->user();
        if (! $user || ! $user->hasRole('student')) {
            return redirect(url("/{$slug}"));
        }

        $from = max(1, (int) $request->query('from', 1));
        $to = max($from, (int) $request->query('to', 2));

        request()->session()->put('runner_grant', [
            'slug' => $slug,
            'from' => $from,
            'to' => $to,
            'homework_id' => null,
            'expires_at' => now()->addHours(3)->timestamp,
        ]);

        return redirect(url("/{$slug}?from={$from}&to={$to}"));
    }

    public function grant(string $slug): JsonResponse
    {
        $slug = trim($slug, "/ \t\n\r\0\x0B");
        $user = auth()->user();
        if (! $user) {
            return response()->json(['ok' => false], 401);
        }

        if (! $user->hasRole('student')) {
            return response()->json([
                'ok' => true,
                'role' => 'staff',
                'slug' => $slug,
                'from' => 1,
                'to' => 999,
                'homework_id' => '',
                'expires_at' => now()->addDays(3650)->timestamp,
            ]);
        }

        $grant = session('runner_grant');
        $valid = is_array($grant)
            && ($grant['slug'] ?? null) === $slug
            && (($grant['expires_at'] ?? 0) >= time());

        if (! $valid) {
            return response()->json([
                'ok' => true,
                'role' => 'student',
                'slug' => $slug,
                'from' => 1,
                'to' => 2,
                'homework_id' => '',
                'expires_at' => now()->addHours(3)->timestamp,
            ]);
        }

        return response()->json([
            'ok' => true,
            'role' => 'student',
            'slug' => $slug,
            'from' => (int) ($grant['from'] ?? 1),
            'to' => (int) ($grant['to'] ?? 1),
            'homework_id' => (string) ($grant['homework_id'] ?? ''),
            'expires_at' => (int) ($grant['expires_at'] ?? 0),
        ]);
    }

    private function serveRunner(string $slug)
    {
        $request = request();
        $requestUri = $request->getRequestUri();

        if (str_contains($requestUri, '/public/index.php/') || str_contains($requestUri, '/index.php/')) {
            $query = $request->getQueryString();
            $target = rtrim(config('app.url'), '/') . '/' . $slug;

            if ($query) {
                $target .= '?' . $query;
            }

            return redirect()->to($target, 302);
        }

        $user = auth()->user();
        if ($user && ! $user->hasRole('student')) {
            $role = (string) $request->query('role', '');
            $targetRole = $user->hasRole('admin') ? 'admin' : 'teacher';
            $hasRangeParams = $request->query('from') !== null
                || $request->query('to') !== null
                || $request->query('levelStart') !== null
                || $request->query('levelEnd') !== null
                || $request->query('assignmentId') !== null
                || $request->query('grant') !== null
                || $request->query('enforceGrant') !== null;
            if ($role !== $targetRole || $hasRangeParams) {
                return redirect()->to(url("/{$slug}") . '?role=' . $targetRole);
            }
        }

        if ($user?->hasRole('student')) {
            $grant = session('runner_grant');
            $from = (int) request('from', 0);
            $to = (int) request('to', 0);
            $valid = is_array($grant)
                && ($grant['slug'] ?? null) === $slug
                && ($grant['from'] ?? null) === $from
                && ($grant['to'] ?? null) === $to
                && (($grant['expires_at'] ?? 0) >= time());

            if (! $valid) {
                request()->session()->put('runner_grant', [
                    'slug' => $slug,
                    'from' => 1,
                    'to' => 2,
                    'homework_id' => null,
                    'expires_at' => now()->addHours(3)->timestamp,
                ]);
                return redirect()->to(url("/{$slug}?from=1&to=2"));
            }
        }

        $runnerAssetPath = public_path('runner-assets/' . $slug . '/index.html');
        $html = File::get($runnerAssetPath);
        $appBase = rtrim(config('app.url'), '/');
        $runnerBase = $appBase . '/runner-assets/' . $slug . '/';
        $inject = '<base href="' . e($runnerBase) . '">' .
            '<script>window.RUNNER_APP_BASE=' . json_encode($appBase, JSON_UNESCAPED_SLASHES) . ';</script>';
        $html = preg_replace('/<head>/i', '<head>' . $inject, $html, 1) ?? $html;
        $html = str_replace('href="../manifest.webmanifest"', 'href="' . e($appBase . '/manifest.webmanifest') . '"', $html);
        $html = str_replace('href="../logo192.png"', 'href="' . e($appBase . '/logo192.png') . '"', $html);
        $html = str_replace('src="../pwa-init.js"', 'src="' . e($appBase . '/pwa-init.js') . '"', $html);
        $html = str_replace('src="./app.js"', 'src="' . e($runnerBase . 'app.js') . '"', $html);
        $html = str_replace('src="app.js"', 'src="' . e($runnerBase . 'app.js') . '"', $html);
        $html = str_replace('href="./style.css"', 'href="' . e($runnerBase . 'style.css') . '"', $html);
        $html = str_replace('href="style.css"', 'href="' . e($runnerBase . 'style.css') . '"', $html);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
