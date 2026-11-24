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
        Schema::create('pinjaman', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_pinjaman', 30)->unique();
            $table->foreignUuid('anggota_id')->constrained('anggota')->restrictOnDelete();
            $table->string('jenis_pinjaman', 20);
            $table->string('nama_barang')->nullable();
            $table->decimal('pokok_pinjaman', 15, 2)->default(0.00);
            $table->decimal('persen_margin', 5, 2)->default(0.00);
            $table->decimal('total_tagihan', 15, 2)->default(0.00);
            $table->decimal('sisa_tagihan', 15, 2)->default(0.00);
            $table->unsignedInteger('tenor_bulan');
            $table->decimal('cicilan_per_bulan', 15, 2)->default(0.00);
            $table->string('status', 20)->default('BERJALAN');
            $table->date('tanggal_cair');
            $table->boolean('is_migrasi')->default(false);
            $table->foreignUuid('dicairkan_oleh')->nullable()->constrained('anggota')->restrictOnDelete();
            $table->timestamps();

            $table->index(['anggota_id', 'status']);
            $table->index('jenis_pinjaman');
            $table->index('tanggal_cair');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinjaman');
    }
};
