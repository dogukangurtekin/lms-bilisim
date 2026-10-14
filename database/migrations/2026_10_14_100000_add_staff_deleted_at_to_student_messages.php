<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Yönetici bir mesajı "sildiğinde" yalnızca personel ekranından kalkar; öğrencinin kendi mesaj geçmişi korunur.
        Schema::table('student_messages', function (Blueprint $table): void {
            $table->timestamp('staff_deleted_at')->nullable()->after('student_read_at');
            $table->foreignId('staff_deleted_by')->nullable()->after('staff_deleted_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_messages', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('staff_deleted_by');
            $table->dropColumn('staff_deleted_at');
        });
    }
};
