<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'consent_version')) {
                $table->unsignedSmallInteger('consent_version')->default(0)->after('password');
            }
            if (! Schema::hasColumn('users', 'consent_accepted_at')) {
                $table->timestamp('consent_accepted_at')->nullable()->after('consent_version');
            }
        });

        if (! Schema::hasTable('user_consents')) {
            Schema::create('user_consents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedSmallInteger('version');
                $table->string('ip', 64)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('accepted_at');
                $table->timestamps();
                $table->index(['user_id', 'version']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_consents');
        Schema::table('users', function (Blueprint $table) {
            foreach (['consent_version', 'consent_accepted_at'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
