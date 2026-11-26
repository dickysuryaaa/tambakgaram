<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_activities', function (Blueprint $table) {
            $table->id('activity_id');
            $table->foreignId('cycle_id')->constrained('production_cycles', 'cycle_id')->cascadeOnDelete();
            $table->string('activity_type', 50);
            $table->date('activity_date');
            $table->text('description')->nullable();
            $table->foreignId('recorded_by')->constrained('members', 'member_id')->restrictOnDelete();
            $table->timestamps();

            $table->index(['cycle_id', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_activities');
    }
};
