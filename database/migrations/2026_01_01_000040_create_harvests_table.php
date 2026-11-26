<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvests', function (Blueprint $table) {
            $table->id('harvest_id');
            $table->foreignId('cycle_id')->constrained('production_cycles', 'cycle_id')->cascadeOnDelete();
            $table->foreignId('pond_id')->constrained('ponds', 'pond_id')->cascadeOnDelete();
            $table->date('harvest_date');
            // Disimpan apa adanya sesuai input; konversi ke kg dilakukan saat rekap.
            $table->decimal('quantity', 12, 2);
            $table->enum('unit', ['kg', 'ton'])->default('kg');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('members', 'member_id')->restrictOnDelete();
            $table->timestamps();

            $table->index(['pond_id', 'harvest_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
