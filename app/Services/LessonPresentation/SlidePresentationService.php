<?php

namespace App\Services\LessonPresentation;

use App\Models\Course;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SlidePresentationService
{
    public function prepareCourseSlides(Course $course, bool $withSummary = true): array
    {
        $payload = (array) ($course->lesson_payload ?? []);
        $slides = array_values(array_filter((array) data_get($payload, 'slides', []), fn ($slide) => is_array($slide)));
        $normalized = array_map(fn (array $slide, int $index) => $this->normalizeSlide($slide, $index, $payload), $slides, array_keys($slides));

        if ($withSummary && $normalized !== []) {
            $normalized[] = $this->buildSummarySlide($course, $payload, $normalized);
        }

        return $normalized;
    }

    public function normalizeSlide(array $slide, int $index = 0, array $payload = []): array
    {
        $title = trim((string) ($slide['title'] ?? $slide['lesson_title'] ?? 'Sayfa ' . ($index + 1)));
        $content = trim((string) ($slide['content'] ?? $slide['text'] ?? $slide['body'] ?? ''));
        $subtitle = trim((string) ($slide['subtitle'] ?? $slide['instructions'] ?? ''));
        $code = trim((string) ($slide['code'] ?? ''));
        $image = trim((string) ($slide['image_url'] ?? ''));
        $video = trim((string) ($slide['video_url'] ?? ''));
        $questionPrompt = trim((string) ($slide['question_prompt'] ?? ''));
        $interactionType = trim((string) ($slide['interaction_type'] ?? 'none'));
        $kind = trim((string) ($slide['kind'] ?? 'topic'));
        $layout = trim((string) ($slide['layout'] ?? ''));

        if ($layout === '') {
            $layout = $this->inferLayout($title, $content, $image, $code, $questionPrompt, $interactionType, $kind);
        }

        $blocks = $this->buildBlocks($slide, $content, $image, $video, $code, $questionPrompt, $interactionType);

        return array_merge($slide, [
            'title' => $title,
            'subtitle' => $subtitle,
            'layout' => $layout,
            'presentation_blocks' => $blocks,
            'presentation_kind' => $kind,
            'presentation_index' => $index + 1,
            'presentation_total' => max(1, (int) data_get($payload, 'slides_count', 0)),
        ]);
    }

    public function inferLayout(string $title, string $content, string $image, string $code, string $questionPrompt, string $interactionType, string $kind = 'topic'): string
    {
        if ($kind === 'summary') return 'summary';
        if (Str::contains(mb_strtolower($questionPrompt), ['sonuç', 'final', 'özet'])) return 'summary';
        if ($interactionType !== 'none') {
            return match ($interactionType) {
                'multiple_choice', 'true_false', 'short_answer', 'checklist', 'matching', 'drag_drop' => 'interactive',
                default => 'interactive',
            };
        }
        if ($code !== '') return 'code';
        if ($image !== '' && $content !== '') return 'split';
        if ($image !== '') return 'image';
        if ($this->isTimelineCandidate($content)) return 'timeline';
        if ($this->isStepsCandidate($content)) return 'steps';
        if ($this->isFeatureCandidate($content)) return 'features';
        if ($content !== '' && Str::length($content) < 260 && $title !== '') return 'hero';
        if ($content !== '') return 'text';
        return 'section';
    }

    private function buildBlocks(array $slide, string $content, string $image, string $video, string $code, string $questionPrompt, string $interactionType): array
    {
        $blocks = [];
        if ($content !== '') {
            $sentences = collect(preg_split('/(?<=[.!?])\s+/u', $content) ?: [])
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->values();
            $blocks[] = [
                'type' => 'paragraph',
                'text' => $sentences->take(2)->implode(' '),
            ];
            $rest = $sentences->slice(2)->values()->all();
            if ($rest !== []) {
                $blocks[] = [
                    'type' => 'bullets',
                    'items' => array_slice($rest, 0, 6),
                ];
            }
        }
        if ($image !== '') {
            $blocks[] = [
                'type' => 'image',
                'url' => $image,
                'alt' => (string) ($slide['title'] ?? 'slide image'),
            ];
        }
        if ($video !== '') {
            $blocks[] = [
                'type' => 'video',
                'url' => $video,
            ];
        }
        if ($code !== '') {
            $blocks[] = [
                'type' => 'code',
                'source' => $code,
            ];
        }
        if ($questionPrompt !== '' || $interactionType !== 'none') {
            $blocks[] = [
                'type' => 'question',
                'prompt' => $questionPrompt,
                'interaction_type' => $interactionType,
                'question' => (array) ($slide['question'] ?? []),
                'points' => (int) ($slide['points'] ?? 5),
                'time_limit' => (int) ($slide['time_limit'] ?? 10),
            ];
        }
        return $blocks;
    }

    private function isStepsCandidate(string $content): bool
    {
        return (bool) preg_match('/(^|\n)\s*(adım|1\.|2\.|3\.|önce|sonra|ardından)\b/ui', $content);
    }

    private function isTimelineCandidate(string $content): bool
    {
        return (bool) preg_match('/(^|\n)\s*(tarih|yıl|aşama|evre|zaman çizelgesi)\b/ui', $content);
    }

    private function isFeatureCandidate(string $content): bool
    {
        return (bool) preg_match('/(^|\n)\s*[-•*]\s+/u', $content) || count(array_filter(preg_split('/\r?\n/', $content) ?: [])) >= 3;
    }

    private function buildSummarySlide(Course $course, array $payload, array $slides): array
    {
        $curriculum = (array) data_get($payload, 'curriculum', []);
        return [
            '__summary' => true,
            'title' => 'Ders Özeti',
            'layout' => 'summary',
            'summary' => [
                'lesson_title' => (string) ($course->name ?? ''),
                'topic' => (string) ($curriculum['konu'] ?? ''),
                'lesson_number' => max(1, (int) ($curriculum['lesson_number'] ?? 1)),
                'outcomes' => array_values(array_filter((array) (data_get($curriculum, 'kazanımlar') ?? data_get($curriculum, 'kazanimlar') ?? []), fn ($item) => trim((string) $item) !== '')),
                'activities' => array_values(array_filter((array) ($curriculum['etkinlikler'] ?? []), fn ($item) => trim((string) $item) !== '')),
                'progress' => max(0, min(100, (int) ($curriculum['progress'] ?? 0))),
                'slide_count' => count($slides),
                'lesson_total_xp' => collect($slides)->sum(fn ($s) => max(0, (int) data_get($s, 'xp', 0))),
            ],
        ];
    }
}
