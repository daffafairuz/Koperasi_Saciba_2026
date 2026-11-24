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
        Schema::create('pembayaran_anggota', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_transaksi', 30)->unique();
            $table->foreignUuid('anggota_id')->constrained('anggota')->restrictOnDelete();
            $table->timestamp('tanggal_bayar');
            $table->decimal('total_bayar', 15, 2)->default(0.00);
            $table->string('metode_bayar', 20);
            $table->text('catatan')->nullable();
            $table->foreignUuid('diinput_oleh')->constrained('anggota')->restrictOnDelete();
            $table->timestamps();

            $table->index(['anggota_id', 'tanggal_bayar']);
            $table->index('tanggal_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_anggota');
    }
};
