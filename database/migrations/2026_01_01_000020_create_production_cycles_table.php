<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_cycles', function (Blueprint $table) {
            $table->id('cycle_id');
            $table->foreignId('pond_id')->constrained('ponds', 'pond_id')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('cycle_status', ['persiapan', 'berjalan', 'selesai', 'dibatalkan'])->default('persiapan');
            $table->timestamps();

            $table->index(['pond_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_cycles');
    }
};
