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
        Schema::create('pengaturan_koperasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('nominal_simpanan_pokok', 15, 2)->default(100000.00);
            $table->decimal('nominal_simpanan_wajib', 15, 2)->default(50000.00);
            $table->decimal('persen_margin_barang', 5, 2)->default(15.00);
            $table->foreignUuid('diperbarui_oleh')->nullable()->constrained('anggota')->restrictOnDelete();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_koperasi');
    }
};
