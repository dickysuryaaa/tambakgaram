<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_observations', function (Blueprint $table) {
            $table->id('observation_id');
            $table->foreignId('pond_id')->constrained('ponds', 'pond_id')->cascadeOnDelete();
            $table->dateTime('observed_at');
            // Parameter yang tidak diukur wajib NULL, bukan 0.
            $table->decimal('salinity_value', 6, 2)->nullable();
            $table->decimal('temperature_c', 5, 2)->nullable();
            $table->decimal('ph_value', 4, 2)->nullable();
            $table->string('instrument')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('members', 'member_id')->restrictOnDelete();
            $table->timestamps();

            $table->index(['pond_id', 'observed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_observations');
    }
};
