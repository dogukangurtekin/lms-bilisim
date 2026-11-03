<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'password_change_required')) {
                $table->boolean('password_change_required')->default(false)->after('password');
            }
            if (! Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('password_change_required');
            }
        });

        // Mevcut tüm öğrenci hesapları bir sonraki girişlerinde şifrelerini bir kez güncellemek zorundadır.
        $studentRoleIds = DB::table('roles')->where('slug', 'student')->pluck('id');
        if ($studentRoleIds->isNotEmpty()) {
            DB::table('users')->whereIn('role_id', $studentRoleIds)->update(['password_change_required' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['password_change_required', 'password_changed_at']);
        });
    }
};
