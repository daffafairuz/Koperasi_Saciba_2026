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
        Schema::create('simpanan_anggota', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('anggota_id')->unique()->constrained('anggota')->restrictOnDelete();
            $table->decimal('nominal_simpanan_pokok', 15, 2)->default(0.00);
            $table->string('status_simpanan_pokok', 20)->default('BELUM_BAYAR');
            $table->timestamp('tanggal_lunas_pokok')->nullable();
            $table->decimal('saldo_sw_awal', 15, 2)->default(0.00);
            $table->decimal('total_simpanan_wajib', 15, 2)->default(0.00);
            $table->string('periode_sw_terakhir', 6)->nullable();
            $table->timestamps();

            $table->index('status_simpanan_pokok');
            $table->index('periode_sw_terakhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simpanan_anggota');
    }
};
