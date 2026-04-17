<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $recentAnnouncements = $this->announcementQueryForUser(auth()->user())
            ->latest('id')
            ->limit(20)
            ->get(['id', 'title', 'content', 'audience', 'published_at'])
            ->reverse()
            ->values();

        return view('notifications.index', [
            'recentAnnouncements' => $recentAnnouncements,
        ]);
    }

    public function storeMessage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'content' => ['required', 'string', 'max:4000'],
            'audience' => ['required', 'in:all,students,teachers'],
        ]);

        $announcement = Announcement::query()->create([
            'title' => trim((string) $data['title']),
            'content' => trim((string) $data['content']),
            'audience' => (string) $data['audience'],
            'published_by' => (int) auth()->id(),
            'published_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'id' => $announcement->id,
            'message' => 'Bildirim gonderildi.',
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $query = $this->announcementQueryForUser(auth()->user());

        if ($request->boolean('latest_id_only')) {
            $latestId = (int) ($query->max('id') ?? 0);
            return response()->json(['latest_id' => $latestId]);
        }

        $afterId = (int) $request->integer('after_id', 0);
        $items = $query
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->limit(50)
            ->get(['id', 'title', 'content', 'audience', 'published_at'])
            ->map(fn (Announcement $item) => [
                'id' => $item->id,
                'title' => (string) $item->title,
                'content' => (string) $item->content,
                'audience' => (string) $item->audience,
                'published_at' => optional($item->published_at)->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'items' => $items,
            'latest_id' => (int) ($items->last()['id'] ?? $afterId),
        ]);
    }

    private function announcementQueryForUser($user)
    {
        $audiences = ['all'];
        if ($user?->hasRole('student')) {
            $audiences[] = 'students';
        } else {
            $audiences[] = 'teachers';
        }

        return Announcement::query()
            ->whereNotNull('published_at')
            ->whereIn('audience', $audiences);
    }
}
