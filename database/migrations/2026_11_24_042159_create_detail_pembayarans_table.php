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
        Schema::create('detail_pembayaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pembayaran_id')->constrained('pembayaran_anggota')->cascadeOnDelete();
            $table->string('jenis_pembayaran', 30);
            $table->foreignUuid('simpanan_anggota_id')->nullable()->constrained('simpanan_anggota')->restrictOnDelete();
            $table->foreignUuid('tagihan_sw_id')->nullable()->constrained('tagihan_simpanan_wajib')->restrictOnDelete();
            $table->foreignUuid('cicilan_pinjaman_id')->nullable()->constrained('cicilan_pinjaman')->restrictOnDelete();
            $table->decimal('nominal_bayar', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index(['pembayaran_id', 'jenis_pembayaran']);
            $table->index('simpanan_anggota_id');
            $table->index('tagihan_sw_id');
            $table->index('cicilan_pinjaman_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pembayaran');
    }
};
