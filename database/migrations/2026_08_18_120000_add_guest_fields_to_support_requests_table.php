<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_requests', function (Blueprint $table) {
            $table->string('guest_name')->nullable()->after('sender_user_id');
            $table->string('guest_email')->nullable()->after('guest_name');
            $table->string('source', 30)->default('user')->index()->after('priority');
            $table->foreignId('sender_user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('support_requests', function (Blueprint $table) {
            $table->dropColumn(['guest_name', 'guest_email', 'source']);
            $table->foreignId('sender_user_id')->nullable(false)->change();
        });
    }
};
