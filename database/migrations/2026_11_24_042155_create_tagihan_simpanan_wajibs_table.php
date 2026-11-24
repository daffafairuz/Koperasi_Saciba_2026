<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tagihan_simpanan_wajib', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('anggota_id')->constrained('anggota')->restrictOnDelete();
            $table->string('periode', 6);
            $table->decimal('nominal_tagihan', 15, 2)->default(0.00);
            $table->decimal('nominal_terbayar', 15, 2)->default(0.00);
            $table->string('status', 20)->default('BELUM_BAYAR');
            $table->timestamp('tanggal_lunas')->nullable();
            $table->timestamps();

            $table->unique(['anggota_id', 'periode']);
            $table->index(['periode', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_simpanan_wajib');
    }
};
