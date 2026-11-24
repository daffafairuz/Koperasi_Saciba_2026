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
        Schema::create('riwayat_status_anggota', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('anggota_id')->constrained('anggota')->restrictOnDelete();
            $table->string('status_sebelumnya', 20);
            $table->string('status_baru', 20);
            $table->text('alasan')->nullable();
            $table->timestamp('tanggal_pengajuan')->nullable();
            $table->foreignUuid('disetujui_oleh')->nullable()->constrained('anggota')->restrictOnDelete();
            $table->timestamp('tanggal_disetujui')->nullable();
            $table->timestamps();

            $table->index(['anggota_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_anggota');
    }
};
