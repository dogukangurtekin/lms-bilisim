<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scratch_projects', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('name', 120);
            $t->longText('data');
            $t->unsignedInteger('size')->default(0);
            $t->unsignedBigInteger('client_ts')->default(0);
            $t->timestamps();
            $t->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scratch_projects');
    }
};
