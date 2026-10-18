<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('subject', 120)->nullable();
            $table->unsignedTinyInteger('grade_level')->nullable();
            $table->string('class_label', 60)->nullable();
            $table->string('term', 80)->nullable();
            $table->date('exam_date')->nullable();
            $table->unsignedSmallInteger('duration_min')->nullable();
            $table->string('school_name', 200)->nullable();
            $table->string('template', 40)->default('classic');
            $table->unsignedTinyInteger('columns')->default(1); // 1 | 2
            $table->json('settings')->nullable();
            $table->string('status', 10)->default('draft'); // draft | final
            $table->timestamps();

            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('question_bank_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject', 120)->nullable();
            $table->unsignedTinyInteger('grade_level')->nullable();
            $table->string('unit', 160)->nullable();
            $table->string('difficulty', 10)->default('orta'); // kolay | orta | zor
            $table->string('type', 20)->default('multiple_choice');
            $table->text('body');
            $table->json('options')->nullable();
            $table->text('answer')->nullable();
            $table->string('image_path', 255)->nullable();
            $table->string('tags', 255)->nullable();
            $table->boolean('is_shared')->default(false);
            $table->timestamps();

            $table->index(['subject', 'grade_level', 'difficulty']);
        });

        Schema::create('exam_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('type', 20)->default('multiple_choice'); // multiple_choice | true_false | fill_blank | open_ended | matching
            $table->text('body');
            $table->json('options')->nullable();
            $table->text('answer')->nullable();
            $table->decimal('points', 6, 2)->default(0);
            $table->string('image_path', 255)->nullable();
            $table->string('section_title', 200)->nullable();
            $table->unsignedTinyInteger('answer_lines')->default(0);
            $table->foreignId('bank_item_id')->nullable()->constrained('question_bank_items')->nullOnDelete();
            $table->timestamps();

            $table->index(['exam_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('question_bank_items');
        Schema::dropIfExists('exams');
    }
};
