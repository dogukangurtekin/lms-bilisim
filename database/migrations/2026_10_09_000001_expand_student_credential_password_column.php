<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_credentials', function (Blueprint $table) {
            // Laravel encrypted values are longer than the previous 120 character limit.
            $table->text('plain_password')->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_credentials', function (Blueprint $table) {
            $table->string('plain_password', 120)->change();
        });
    }
};
