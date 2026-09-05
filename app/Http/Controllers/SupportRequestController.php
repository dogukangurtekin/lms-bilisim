<?php

namespace App\Http\Controllers;

use App\Models\SupportRequest;
use App\Models\SupportRequestReply;
use App\Models\Teacher;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportRequestController extends Controller
{
    public function __construct(private PushNotificationService $pushService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user?->hasRole('admin', 'teacher'), 403);

        $isAdmin = $user?->hasRole('admin') === true;
        $isTeacher = $user?->hasRole('teacher') === true;
        $teacher = $isTeacher ? $user?->teacher : null;

        $status = trim((string) $request->query('status', ''));
        $search = trim((string) $request->query('q', ''));
        $selectedId = (int) $request->query('selected', 0);

        $query = SupportRequest::query()
            ->with([
                'sender:id,name,role_id',
                'sender.role:id,slug',
                'recipient:id,name,role_id',
                'recipient.role:id,slug',
                'replies.sender:id,name,role_id',
                'replies.sender.role:id,slug',
            ])
            ->orderByDesc('id');

        if (! $isAdmin && $teacher) {
            $query->where('sender_user_id', $user->id);
        }

        if ($status !== '' && in_array($status, ['open', 'read', 'answered', 'closed', 'archived'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('subject', 'like', '%' . $search . '%')
                    ->orWhere('message', 'like', '%' . $search . '%')
                    ->orWhere('guest_name', 'like', '%' . $search . '%')
                    ->orWhere('guest_email', 'like', '%' . $search . '%')
                    ->orWhereHas('sender', fn ($sq) => $sq->where('name', 'like', '%' . $search . '%'));
            });
        }

        $requests = $query->paginate(12)->withQueryString();
        $selectedRequest = $selectedId > 0
            ? SupportRequest::query()
                ->with([
                    'sender:id,name,role_id',
                    'sender.role:id,slug',
                    'recipient:id,name,role_id',
                    'recipient.role:id,slug',
                    'replies.sender:id,name,role_id',
                    'replies.sender.role:id,slug',
                ])
                ->find($selectedId)
            : null;

        if ($selectedRequest && ! $isAdmin && $teacher && (int) $selectedRequest->sender_user_id !== (int) $user->id) {
            abort(403);
        }

        return view('support-requests.index', [
            'requests' => $requests,
            'selectedRequest' => $selectedRequest,
            'isAdmin' => $isAdmin,
            'isTeacher' => $isTeacher,
            'status' => $status,
            'search' => $search,
            'teachers' => Teacher::query()->with('user:id,name')->whereNotNull('user_id')->orderBy('id')->get(['id', 'user_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->hasRole('teacher'), 403);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:6000'],
            'category' => ['required', Rule::in(['technical_support', 'lesson_content', 'user_permission', 'bug_report', 'other'])],
            'priority' => ['required', Rule::in(['low', 'normal', 'high'])],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('support-requests', 'public');
        }

        $ticket = SupportRequest::query()->create([
            'sender_user_id' => $user->id,
            'recipient_user_id' => null,
            'subject' => (string) $data['subject'],
            'message' => (string) $data['message'],
            'category' => (string) $data['category'],
            'priority' => (string) $data['priority'],
            'source' => 'teacher',
            'status' => 'open',
            'attachment_path' => $path,
            'read_at' => null,
        ]);

        $this->pushService->sendToUsers(
            User::query()->whereHas('role', fn ($q) => $q->where('slug', 'admin'))->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'support_request_created',
            'Yeni Talep',
            $user->name . ' yeni bir talep oluşturdu: ' . $ticket->subject,
            route('support-requests.index', ['selected' => $ticket->id]),
            ['ticket_id' => $ticket->id, 'from_user_id' => $user->id]
        );

        return redirect()->route('support-requests.index', ['selected' => $ticket->id])->with('ok', 'Talep gönderildi.');
    }

    public function storeDemo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:190'],
            'guest_email' => ['required', 'email', 'max:190'],
            'message' => ['required', 'string', 'max:6000'],
        ]);

        $admin = User::query()->whereHas('role', fn ($q) => $q->where('slug', 'admin'))->orderBy('id')->first();
        abort_unless($admin, 404);

        $ticket = SupportRequest::query()->create([
            'sender_user_id' => null,
            'guest_name' => (string) $data['guest_name'],
            'guest_email' => (string) $data['guest_email'],
            'recipient_user_id' => $admin->id,
            'subject' => 'Demo Talebi: ' . $data['guest_name'],
            'message' => "Ad Soyad: {$data['guest_name']}\nE-posta: {$data['guest_email']}\n\nMesaj:\n" . $data['message'],
            'category' => 'other',
            'priority' => 'normal',
            'source' => 'demo',
            'status' => 'open',
            'attachment_path' => null,
            'read_at' => null,
        ]);

        $this->pushService->sendToUsers(
            [$admin->id],
            'support_request_created',
            'Yeni Demo Talebi',
            $data['guest_name'] . ' demo talebi gönderdi.',
            route('support-requests.index', ['selected' => $ticket->id]),
            ['ticket_id' => $ticket->id, 'source' => 'demo']
        );

        return back()->with('ok', 'Demo talebiniz gönderildi.');
    }

    public function update(Request $request, SupportRequest $supportRequest): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->hasRole('admin'), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'read', 'answered', 'closed', 'archived'])],
        ]);

        $supportRequest->update([
            'status' => (string) $data['status'],
            'closed_at' => in_array($data['status'], ['closed', 'archived'], true) ? now() : $supportRequest->closed_at,
            'read_at' => $supportRequest->read_at ?? now(),
        ]);

        return redirect()->route('support-requests.index', ['selected' => $supportRequest->id])->with('ok', 'Talep güncellendi.');
    }

    public function reply(Request $request, SupportRequest $supportRequest): RedirectResponse
    {
        $user = $request->user();
        $isAdmin = $user?->hasRole('admin') === true;
        $isTeacher = $user?->hasRole('teacher') === true;
        abort_unless($isAdmin || ($isTeacher && (int) $supportRequest->sender_user_id === (int) $user->id), 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:6000'],
            'internal_note' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['open', 'read', 'answered', 'closed', 'archived'])],
        ]);

        $internalNote = (bool) ($data['internal_note'] ?? false);
        if ($isTeacher) {
            $internalNote = false;
        }

        SupportRequestReply::query()->create([
            'support_request_id' => $supportRequest->id,
            'sender_user_id' => $user->id,
            'message' => (string) $data['message'],
            'internal_note' => $internalNote,
        ]);

        $supportRequest->update([
            'status' => (string) $data['status'],
            'read_at' => $supportRequest->read_at ?? now(),
            'closed_at' => in_array($data['status'], ['closed', 'archived'], true) ? now() : $supportRequest->closed_at,
            'recipient_user_id' => $isAdmin ? $supportRequest->sender_user_id : null,
        ]);

        if ($isAdmin && $supportRequest->sender_user_id) {
            $this->pushService->sendToUsers(
                [$supportRequest->sender_user_id],
                'support_request_replied',
                'Talebinize cevap var',
                'Talebiniz cevaplandı: ' . $supportRequest->subject,
                route('support-requests.index', ['selected' => $supportRequest->id]),
                ['ticket_id' => $supportRequest->id, 'reply_by' => $user->id]
            );
        } else {
            $adminIds = User::query()->whereHas('role', fn ($q) => $q->where('slug', 'admin'))->pluck('id')->map(fn ($id) => (int) $id)->all();
            $this->pushService->sendToUsers(
                $adminIds,
                'support_request_replied',
                'Talebe yeni mesaj',
                $user->name . ' talebe yeni mesaj yazdı: ' . $supportRequest->subject,
                route('support-requests.index', ['selected' => $supportRequest->id]),
                ['ticket_id' => $supportRequest->id, 'reply_by' => $user->id]
            );
        }

        return redirect()->route('support-requests.index', ['selected' => $supportRequest->id])->with('ok', 'Cevap gönderildi.');
    }

    public function archive(SupportRequest $supportRequest): RedirectResponse
    {
        $user = request()->user();
        abort_unless($user?->hasRole('admin'), 403);

        $supportRequest->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);

        return redirect()->route('support-requests.index', ['selected' => $supportRequest->id])->with('ok', 'Talep arşivlendi.');
    }

    public function destroy(SupportRequest $supportRequest): RedirectResponse
    {
        $user = request()->user();
        $isAdmin = $user?->hasRole('admin') === true;
        $isOwner = (int) $supportRequest->sender_user_id === (int) $user?->id;

        abort_unless($isAdmin || $isOwner, 403);

        if ($supportRequest->attachment_path) {
            Storage::delete($supportRequest->attachment_path);
        }

        $supportRequest->delete();

        return redirect()->route('support-requests.index')->with('ok', 'Talep silindi.');
    }
}
