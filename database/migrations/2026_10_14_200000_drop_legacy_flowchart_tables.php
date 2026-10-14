<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eski Vue tabanlı Flowchart Programming uygulaması kaldırıldı; tabloları yalnızca BOŞSA düşürür (veri varsa dokunmaz).
        foreach (['flowchart_edges', 'flowchart_nodes', 'flowcharts'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->count() > 0) {
                return;
            }
        }
        Schema::dropIfExists('flowchart_edges');
        Schema::dropIfExists('flowchart_nodes');
        Schema::dropIfExists('flowcharts');
    }

    public function down(): void
    {
        // Geri döndürülemez: eski tablolar yeniden oluşturulmaz.
    }
};
