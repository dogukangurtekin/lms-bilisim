<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_homework_entries', function (Blueprint $t) {
            $t->id();
            $t->string('public_token', 40)->index();
            $t->string('student_name', 120);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_homework_entries');
    }
};
