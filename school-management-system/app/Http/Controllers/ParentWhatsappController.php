<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\StudentProgressReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ParentWhatsappController extends Controller
{
    public function __construct(private StudentProgressReportService $reportService)
    {
    }

    public function start(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'recipient_mode' => ['required', 'in:parents,manual'],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'manual_numbers' => ['nullable', 'string', 'max:20000'],
            'include_report_link' => ['nullable', 'boolean'],
            'send_mode' => ['required', 'in:text,template_document'],
            'template_name' => ['nullable', 'string', 'max:190'],
            'template_language' => ['nullable', 'string', 'max:20'],
            'include_pdf_attachment' => ['nullable', 'boolean'],
            'document_caption' => ['nullable', 'string', 'max:300'],
            'send_phone_number_id' => ['nullable', 'string', 'max:120'],
            'send_phone_display' => ['nullable', 'string', 'max:60'],
        ]);

        $targets = [];
        $includeReportLink = (bool) ($data['include_report_link'] ?? false);
        $includePdfAttachment = (bool) ($data['include_pdf_attachment'] ?? false);
        $messageTemplate = trim((string) $data['message']);
        $sendMode = (string) ($data['send_mode'] ?? 'text');
        $templateName = trim((string) ($data['template_name'] ?? (string) config('services.whatsapp.default_template_name')));
        $templateLanguage = trim((string) ($data['template_language'] ?? (string) config('services.whatsapp.default_template_language', 'tr')));
        $documentCaption = trim((string) ($data['document_caption'] ?? ''));
        $sendPhoneNumberId = trim((string) ($data['send_phone_number_id'] ?? ''));
        $sendPhoneDisplay = trim((string) ($data['send_phone_display'] ?? ''));

        if ($data['recipient_mode'] === 'parents') {
            $students = Student::query()
                ->with(['user', 'schoolClass'])
                ->when(!empty($data['school_class_id']), fn ($q) => $q->where('school_class_id', (int) $data['school_class_id']))
                ->whereNotNull('parent_phone')
                ->where('parent_phone', '!=', '')
                ->orderBy('id')
                ->get();

            foreach ($students as $student) {
                $phone = $this->normalizePhone((string) $student->parent_phone);
                if ($phone === '') {
                    continue;
                }

                $studentName = (string) ($student->user?->name ?? ('Ogrenci #' . $student->id));
                $className = $student->schoolClass ? ($student->schoolClass->name . '/' . $student->schoolClass->section) : '-';
                $reportLink = $includeReportLink
                    ? URL::temporarySignedRoute('parent.progress-report', now()->addDays(7), ['student' => $student->id])
                    : null;

                $targets[] = [
                    'phone' => $phone,
                    'student_id' => $student->id,
                    'student_name' => $studentName,
                    'class_name' => $className,
                    'report_link' => $reportLink,
                ];
            }
        } else {
            $manual = (string) ($data['manual_numbers'] ?? '');
            foreach ($this->extractPhones($manual) as $phone) {
                $targets[] = [
                    'phone' => $phone,
                    'student_id' => null,
                    'student_name' => null,
                    'class_name' => null,
                    'report_link' => null,
                ];
            }
        }

        if ($targets === []) {
            return response()->json(['message' => 'Gonderim icin gecerli numara bulunamadi.'], 422);
        }

        $task = [
            'id' => (string) Str::uuid(),
            'total' => count($targets),
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'created_at' => now()->toIso8601String(),
            'completed_at' => null,
            'message_template' => $messageTemplate,
            'send_mode' => $sendMode,
            'template_name' => $templateName,
            'template_language' => $templateLanguage !== '' ? $templateLanguage : 'tr',
            'include_pdf_attachment' => $includePdfAttachment,
            'document_caption' => $documentCaption,
            'send_phone_number_id' => $sendPhoneNumberId,
            'send_phone_display' => $sendPhoneDisplay,
            'targets' => $targets,
            'log' => [],
            'manual_links' => [],
            'provider' => $this->providerName(),
        ];
        $this->writeTask($task['id'], $task);

        return response()->json([
            'task_id' => $task['id'],
            'total' => $task['total'],
            'provider' => $task['provider'],
        ]);
    }

    public function step(string $taskId): JsonResponse
    {
        $task = $this->readTask($taskId);
        if (!$task) {
            return response()->json(['message' => 'Gonderim gorevi bulunamadi.'], 404);
        }

        if (($task['completed_at'] ?? null) !== null) {
            return response()->json($this->statusPayload($task));
        }

        $batch = 20;
        $start = (int) ($task['processed'] ?? 0);
        $targets = array_slice((array) ($task['targets'] ?? []), $start, $batch);

        foreach ($targets as $target) {
            $message = $this->buildMessage($task, $target);
            $result = $this->sendWhatsappMessage($target, $message, $task);

            $task['processed'] = (int) $task['processed'] + 1;
            if (($result['ok'] ?? false) === true) {
                $task['success'] = (int) $task['success'] + 1;
            } else {
                $task['failed'] = (int) $task['failed'] + 1;
            }

            if (!empty($result['manual_link'])) {
                $task['manual_links'][] = $result['manual_link'];
            }

            $task['log'][] = [
                'phone' => (string) ($target['phone'] ?? ''),
                'student' => (string) ($target['student_name'] ?? ''),
                'ok' => (bool) ($result['ok'] ?? false),
                'provider' => (string) ($result['provider'] ?? $task['provider']),
                'message' => (string) ($result['message'] ?? ''),
                'at' => now()->toIso8601String(),
            ];
        }

        if ((int) $task['processed'] >= (int) $task['total']) {
            $task['completed_at'] = now()->toIso8601String();
        }

        $this->writeTask($taskId, $task);

        return response()->json($this->statusPayload($task));
    }

    public function classes(): JsonResponse
    {
        $classes = SchoolClass::query()
            ->orderBy('name')
            ->orderBy('section')
            ->get(['id', 'name', 'section']);

        return response()->json(['classes' => $classes]);
    }

    private function buildMessage(array $task, array $target): string
    {
        $message = (string) ($task['message_template'] ?? '');
        $studentName = (string) ($target['student_name'] ?? '');
        $className = (string) ($target['class_name'] ?? '');
        $reportLink = (string) ($target['report_link'] ?? '');

        $message = str_replace(
            ['{ogrenci}', '{sinif}', '{rapor_linki}'],
            [$studentName !== '' ? $studentName : 'Ogrenci', $className !== '' ? $className : '-', $reportLink],
            $message
        );

        if ($reportLink !== '' && !str_contains($message, $reportLink)) {
            $message .= "\n\nGelisim Raporu: {$reportLink}";
        }

        return trim($message);
    }

    private function sendWhatsappMessage(array $target, string $message, array $task): array
    {
        $phone = (string) ($target['phone'] ?? '');
        $phoneNumberId = (string) ($task['send_phone_number_id'] ?? '');
        if ($phoneNumberId === '') {
            $phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        }
        $token = (string) config('services.whatsapp.access_token');
        $apiVersion = (string) config('services.whatsapp.api_version', 'v21.0');
        $sendMode = (string) ($task['send_mode'] ?? 'text');
        $templateName = (string) ($task['template_name'] ?? '');
        $templateLanguage = (string) ($task['template_language'] ?? 'tr');
        $includePdfAttachment = (bool) ($task['include_pdf_attachment'] ?? false);
        $documentCaption = (string) ($task['document_caption'] ?? '');
        $studentName = (string) ($target['student_name'] ?? '');
        $studentId = (int) ($target['student_id'] ?? 0);

        if ($phoneNumberId !== '' && $token !== '') {
            try {
                $url = "https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages";
                if ($sendMode === 'template_document') {
                    if ($templateName !== '') {
                        $templatePayload = [
                            'messaging_product' => 'whatsapp',
                            'to' => $phone,
                            'type' => 'template',
                            'template' => [
                                'name' => $templateName,
                                'language' => ['code' => $templateLanguage !== '' ? $templateLanguage : 'tr'],
                                'components' => [
                                    [
                                        'type' => 'body',
                                        'parameters' => [
                                            ['type' => 'text', 'text' => $studentName !== '' ? $studentName : 'Veli'],
                                        ],
                                    ],
                                ],
                            ],
                        ];
                        $templateResp = Http::withToken($token)->post($url, $templatePayload);
                        if (!$templateResp->successful()) {
                            return ['ok' => false, 'provider' => 'cloud_api', 'message' => 'Template hata: ' . $templateResp->status()];
                        }
                    }

                    if ($includePdfAttachment && $studentId > 0) {
                        $pdfInfo = $this->buildStudentReportPdf($studentId);
                        if (!$pdfInfo['ok']) {
                            return ['ok' => false, 'provider' => 'cloud_api', 'message' => (string) ($pdfInfo['message'] ?? 'PDF olusturulamadi')];
                        }

                        $uploadResp = Http::withToken($token)
                            ->attach('file', file_get_contents($pdfInfo['path']), basename($pdfInfo['path']))
                            ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/media", [
                                'messaging_product' => 'whatsapp',
                                'type' => 'application/pdf',
                            ]);

                        @unlink($pdfInfo['path']);

                        if (!$uploadResp->successful()) {
                            return ['ok' => false, 'provider' => 'cloud_api', 'message' => 'PDF upload hata: ' . $uploadResp->status()];
                        }
                        $mediaId = (string) ($uploadResp->json('id') ?? '');
                        if ($mediaId === '') {
                            return ['ok' => false, 'provider' => 'cloud_api', 'message' => 'PDF media id alinamadi'];
                        }

                        $caption = trim($documentCaption) !== '' ? $documentCaption : 'Ogrenci Gelisim Raporu';
                        $docResp = Http::withToken($token)->post($url, [
                            'messaging_product' => 'whatsapp',
                            'to' => $phone,
                            'type' => 'document',
                            'document' => [
                                'id' => $mediaId,
                                'filename' => 'ogrenci-gelisim-raporu.pdf',
                                'caption' => $caption,
                            ],
                        ]);
                        if (!$docResp->successful()) {
                            return ['ok' => false, 'provider' => 'cloud_api', 'message' => 'Document gonderim hata: ' . $docResp->status()];
                        }
                        return ['ok' => true, 'provider' => 'cloud_api', 'message' => 'Template + PDF gonderildi'];
                    }

                    return ['ok' => true, 'provider' => 'cloud_api', 'message' => 'Template gonderildi'];
                }

                $textResp = Http::withToken($token)->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);
                if ($textResp->successful()) {
                    return ['ok' => true, 'provider' => 'cloud_api', 'message' => 'Mesaj gonderildi'];
                }
                return ['ok' => false, 'provider' => 'cloud_api', 'message' => 'API hata: ' . $textResp->status()];
            } catch (\Throwable $e) {
                return ['ok' => false, 'provider' => 'cloud_api', 'message' => 'API exception: ' . $e->getMessage()];
            }
        }

        $manualLink = 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
        return [
            'ok' => true,
            'provider' => 'manual_link',
            'message' => 'Cloud API ayari yok. Manuel link olusturuldu.',
            'manual_link' => $manualLink,
        ];
    }

    private function buildStudentReportPdf(int $studentId): array
    {
        $student = Student::query()
            ->with(['user', 'schoolClass', 'badges', 'currentAvatar', 'avatars'])
            ->find($studentId);
        if (!$student) {
            return ['ok' => false, 'message' => 'Ogrenci bulunamadi'];
        }

        $report = $this->reportService->build($student);
        $pdfBinary = Pdf::loadView('reports.student-progress-whatsapp-pdf', [
            'student' => $student,
            'report' => $report,
        ])->setPaper('a4')->output();

        $safeName = 'student-report-' . $studentId . '-' . Str::lower(Str::random(10)) . '.pdf';
        $relative = 'reports/tmp/' . $safeName;
        Storage::disk('local')->put($relative, $pdfBinary);
        $absolute = storage_path('app/' . $relative);

        return ['ok' => true, 'path' => $absolute];
    }

    private function providerName(): string
    {
        return (config('services.whatsapp.phone_number_id') && config('services.whatsapp.access_token'))
            ? 'cloud_api'
            : 'manual_link';
    }

    private function extractPhones(string $content): array
    {
        $parts = preg_split('/[\s,;\n\r\t]+/', $content) ?: [];
        $phones = [];
        foreach ($parts as $part) {
            $phone = $this->normalizePhone($part);
            if ($phone !== '') {
                $phones[$phone] = true;
            }
        }
        return array_keys($phones);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '0')) {
            $digits = '9' . substr($digits, 1);
        }
        if (!str_starts_with($digits, '90') && strlen($digits) === 10) {
            $digits = '90' . $digits;
        }
        if (strlen($digits) < 10 || strlen($digits) > 15) {
            return '';
        }
        return $digits;
    }

    private function taskPath(string $taskId): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9\-]/', '', $taskId) ?: 'task';
        return storage_path('app/reports/whatsapp-send-' . $safe . '.json');
    }

    private function readTask(string $taskId): ?array
    {
        $path = $this->taskPath($taskId);
        if (!is_file($path)) {
            return null;
        }
        $json = @file_get_contents($path);
        $data = is_string($json) ? json_decode($json, true) : null;
        return is_array($data) ? $data : null;
    }

    private function writeTask(string $taskId, array $task): void
    {
        $path = $this->taskPath($taskId);
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        file_put_contents($path, json_encode($task, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private function statusPayload(array $task): array
    {
        $total = max(1, (int) ($task['total'] ?? 1));
        $processed = min($total, max(0, (int) ($task['processed'] ?? 0)));
        $percent = (int) floor(($processed / $total) * 100);
        $completed = ($task['completed_at'] ?? null) !== null;

        return [
            'task_id' => (string) ($task['id'] ?? ''),
            'provider' => (string) ($task['provider'] ?? ''),
            'sender' => (string) ($task['send_phone_display'] ?? ''),
            'processed' => $processed,
            'total' => $total,
            'percent' => $percent,
            'success' => (int) ($task['success'] ?? 0),
            'failed' => (int) ($task['failed'] ?? 0),
            'completed' => $completed,
            'manual_links' => array_slice((array) ($task['manual_links'] ?? []), 0, 200),
        ];
    }
}
