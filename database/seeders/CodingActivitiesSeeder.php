<?php

namespace Database\Seeders;

use App\Models\ActivityQuestion;
use App\Models\CodingActivity;
use App\Models\QuestionOption;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CodingActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        $activity = CodingActivity::updateOrCreate(
            ['title' => 'Algoritma Temelleri: Siralama ve Karar'],
            [
                'type' => 'daily_task',
                'instruction' => 'Bugun blok kodlama ve algoritma adimlarini kisa konu anlatimiyla ogreneceksin.',
                'lesson_pages' => [
                    'Algoritma, bir problemi cozmeye yarayan adimlar dizisidir. Adimlar sirali olmalidir.',
                    'Blok kodlamada akis genelde Basla -> Adimlar -> Kontrol -> Bitis seklindedir.',
                    'Karar yapisi if/else ile calisir. Kosul dogruysa bir yol, degilse diger yol secilir.'
                ],
                'base_xp' => 30,
                'active_on' => Carbon::today('Europe/Istanbul')->toDateString(),
                'is_active' => true,
                'is_random_pool' => true,
            ]
        );

        if ($activity->questions()->exists()) {
            return;
        }

        $q1 = ActivityQuestion::create([
            'coding_activity_id' => $activity->id,
            'question_type' => 'single_choice',
            'prompt' => 'Algoritmada adimlarin sirasi neden onemlidir?',
            'answer_key' => ['answer' => 'B'],
            'points' => 10,
            'order_no' => 1,
        ]);

        QuestionOption::insert([
            ['activity_question_id' => $q1->id, 'option_key' => 'A', 'label' => 'Onemli degildir', 'is_correct' => false, 'order_no' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['activity_question_id' => $q1->id, 'option_key' => 'B', 'label' => 'Sira degisirse sonuc degisebilir', 'is_correct' => true, 'order_no' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['activity_question_id' => $q1->id, 'option_key' => 'C', 'label' => 'Sadece zor sorularda onemlidir', 'is_correct' => false, 'order_no' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $q2 = ActivityQuestion::create([
            'coding_activity_id' => $activity->id,
            'question_type' => 'multi_choice',
            'prompt' => 'Algoritmanin temel parcalari hangileridir?',
            'answer_key' => ['correct' => ['A','C']],
            'points' => 12,
            'order_no' => 2,
        ]);

        QuestionOption::insert([
            ['activity_question_id' => $q2->id, 'option_key' => 'A', 'label' => 'Girdi', 'is_correct' => true, 'order_no' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['activity_question_id' => $q2->id, 'option_key' => 'B', 'label' => 'Duvar kagidi', 'is_correct' => false, 'order_no' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['activity_question_id' => $q2->id, 'option_key' => 'C', 'label' => 'Cikti', 'is_correct' => true, 'order_no' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['activity_question_id' => $q2->id, 'option_key' => 'D', 'label' => 'Tema rengi', 'is_correct' => false, 'order_no' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);

        ActivityQuestion::create([
            'coding_activity_id' => $activity->id,
            'question_type' => 'short_text',
            'prompt' => 'Kosul kontrolu yapan yapiyi yaziniz.',
            'answer_key' => ['answer' => 'if'],
            'points' => 8,
            'order_no' => 3,
        ]);
    }
}
