<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flowcharts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 140);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('flowchart_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flowchart_id')->constrained('flowcharts')->cascadeOnDelete();
            $table->string('node_key', 60);
            $table->string('type', 30);
            $table->string('text', 255)->nullable();
            $table->text('code')->nullable();
            $table->decimal('position_x', 10, 2)->default(0);
            $table->decimal('position_y', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['flowchart_id', 'node_key']);
            $table->index(['flowchart_id', 'type']);
        });

        Schema::create('flowchart_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flowchart_id')->constrained('flowcharts')->cascadeOnDelete();
            $table->string('edge_key', 60);
            $table->string('from_node', 60);
            $table->string('to_node', 60);
            $table->string('condition', 10)->nullable();
            $table->timestamps();

            $table->unique(['flowchart_id', 'edge_key']);
            $table->index(['flowchart_id', 'from_node']);
            $table->index(['flowchart_id', 'to_node']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flowchart_edges');
        Schema::dropIfExists('flowchart_nodes');
        Schema::dropIfExists('flowcharts');
    }
};

