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
        Schema::create('transaksi_kas_usaha', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_transaksi', 30)->unique();
            $table->date('tanggal_transaksi');
            $table->string('jenis_arus', 20);
            $table->string('kategori', 100);
            $table->text('keterangan')->nullable();
            $table->decimal('nominal', 15, 2)->default(0.00);
            $table->foreignUuid('diinput_oleh')->constrained('anggota')->restrictOnDelete();
            $table->timestamps();

            $table->index(['tanggal_transaksi', 'jenis_arus']);
            $table->index('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_kas_usaha');
    }
};
