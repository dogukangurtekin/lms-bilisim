<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\LiveQuizQuestion;
use App\Models\User;
use App\Support\Utf8Text;
use Illuminate\Console\Command;

class Utf8AuditCommand extends Command
{
    protected $signature = 'app:utf8-audit {--fix : Normalize common text fields in place}';

    protected $description = 'Audit and optionally normalize UTF-8 text fields that show mojibake or ?? placeholders.';

    public function handle(): int
    {
        $fix = (bool) $this->option('fix');
        $found = [];

        $this->scanUsers($found, $fix);
        $this->scanCourses($found, $fix);
        $this->scanLiveQuizQuestions($found, $fix);

        if ($found === []) {
            $this->info('No obvious mojibake found in audited records.');
        } else {
            $this->warn('Potentially affected records:');
            foreach ($found as $row) {
                $this->line($row);
            }
        }

        if ($fix) {
            $this->info('Normalization pass completed.');
        }

        return self::SUCCESS;
    }

    private function scanUsers(array &$found, bool $fix): void
    {
        User::query()->select(['id', 'name'])->chunkById(200, function ($users) use (&$found, $fix) {
            foreach ($users as $user) {
                $normalized = (string) Utf8Text::normalize($user->name);
                if ($this->looksBroken($user->name) || $normalized !== (string) $user->name) {
                    $found[] = "users#{$user->id}: {$user->name} => {$normalized}";
                    if ($fix && $normalized !== '') {
                        $user->name = $normalized;
                        $user->save();
                    }
                }
            }
        });
    }

    private function scanCourses(array &$found, bool $fix): void
    {
        Course::query()->select(['id', 'name', 'lesson_payload'])->chunkById(100, function ($courses) use (&$found, $fix) {
            foreach ($courses as $course) {
                $dirty = false;
                $payload = (array) $course->lesson_payload;
                $name = (string) $course->name;
                $normalizedName = (string) Utf8Text::normalize($name);
                if ($this->looksBroken($name) || $normalizedName !== $name) {
                    $found[] = "courses#{$course->id}.name: {$name} => {$normalizedName}";
                    $dirty = $fix && $normalizedName !== '';
                    if ($dirty) {
                        $course->name = $normalizedName;
                    }
                }

                $fields = [
                    ['lesson_description', data_get($payload, 'lesson_description')],
                    ['curriculum.title', data_get($payload, 'curriculum.title')],
                    ['curriculum.konu', data_get($payload, 'curriculum.konu')],
                ];

                foreach ($fields as [$path, $value]) {
                    if (! is_string($value)) {
                        continue;
                    }
                    $normalized = (string) Utf8Text::normalize($value);
                    if ($this->looksBroken($value) || $normalized !== $value) {
                        $found[] = "courses#{$course->id}.{$path}: {$value} => {$normalized}";
                        if ($fix && $normalized !== '') {
                            data_set($payload, $path, $normalized);
                            $dirty = true;
                        }
                    }
                }

                foreach ((array) data_get($payload, 'curriculum.kazanimlar', []) as $idx => $item) {
                    if (! is_string($item)) {
                        continue;
                    }
                    $normalized = (string) Utf8Text::normalize($item);
                    if ($this->looksBroken($item) || $normalized !== $item) {
                        $found[] = "courses#{$course->id}.curriculum.kazanimlar[$idx]: {$item} => {$normalized}";
                        if ($fix && $normalized !== '') {
                            $payload['curriculum']['kazanimlar'][$idx] = $normalized;
                            $dirty = true;
                        }
                    }
                }

                if ($fix && $dirty) {
                    $course->lesson_payload = $payload;
                    $course->save();
                }
            }
        });
    }

    private function scanLiveQuizQuestions(array &$found, bool $fix): void
    {
        LiveQuizQuestion::query()->select(['id', 'question_text', 'options'])->chunkById(200, function ($rows) use (&$found, $fix) {
            foreach ($rows as $row) {
                $dirty = false;
                $normalizedQuestion = (string) Utf8Text::normalize($row->question_text);
                if ($this->looksBroken($row->question_text) || $normalizedQuestion !== (string) $row->question_text) {
                    $found[] = "live_quiz_questions#{$row->id}.question_text: {$row->question_text} => {$normalizedQuestion}";
                    if ($fix && $normalizedQuestion !== '') {
                        $row->question_text = $normalizedQuestion;
                        $dirty = true;
                    }
                }

                $options = is_array($row->options) ? $row->options : [];
                $normalizedOptions = Utf8Text::sanitizeArray($options);
                if ($this->looksBroken(json_encode($options, JSON_UNESCAPED_UNICODE) ?: '') || $normalizedOptions !== $options) {
                    $found[] = "live_quiz_questions#{$row->id}.options: normalized";
                    if ($fix) {
                        $row->options = $normalizedOptions;
                        $dirty = true;
                    }
                }

                if ($fix && $dirty) {
                    $row->save();
                }
            }
        });
    }

    private function looksBroken(?string $value): bool
    {
        $value = (string) $value;

        return str_contains($value, 'Ã') || str_contains($value, 'Ä') || str_contains($value, 'Â') || str_contains($value, '??');
    }
}
