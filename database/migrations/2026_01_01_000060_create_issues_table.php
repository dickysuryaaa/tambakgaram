<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id('issue_id');
            $table->foreignId('pond_id')->constrained('ponds', 'pond_id')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('members', 'member_id')->restrictOnDelete();
            $table->dateTime('reported_at');
            $table->enum('category', ['tanggul_bocor', 'pompa_saluran', 'cuaca_hujan', 'lainnya']);
            $table->text('description');
            $table->enum('priority', ['rendah', 'sedang', 'tinggi'])->default('sedang');
            $table->enum('status', ['terbuka', 'diproses', 'selesai'])->default('terbuka');
            $table->text('follow_up_notes')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['pond_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
