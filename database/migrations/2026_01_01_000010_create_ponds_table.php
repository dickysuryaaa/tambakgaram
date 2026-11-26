<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ponds', function (Blueprint $table) {
            $table->id('pond_id');
            $table->string('pond_code', 30)->unique();
            $table->string('pond_name')->nullable();
            $table->foreignId('member_id')->constrained('members', 'member_id')->restrictOnDelete();
            $table->string('village');
            $table->string('district');
            $table->string('regency');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('area_m2');
            $table->enum('operational_status', [
                'persiapan', 'pengisian_air', 'penguapan', 'kristalisasi_panen', 'jeda', 'tidak_aktif',
            ])->default('persiapan');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('operational_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ponds');
    }
};
