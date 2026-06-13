<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('students')
            ->whereNull('parent_phone')
            ->orWhere('parent_phone', '')
            ->update(['parent_phone' => '+901111111111']);
    }

    public function down(): void
    {
        DB::table('students')
            ->where('parent_phone', '+901111111111')
            ->update(['parent_phone' => null]);
    }
};
