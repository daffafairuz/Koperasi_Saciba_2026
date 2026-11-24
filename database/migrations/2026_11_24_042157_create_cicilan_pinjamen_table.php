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
        Schema::create('cicilan_pinjaman', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pinjaman_id')->constrained('pinjaman')->cascadeOnDelete();
            $table->unsignedInteger('cicilan_ke');
            $table->date('jatuh_tempo');
            $table->decimal('nominal_tagihan', 15, 2)->default(0.00);
            $table->decimal('nominal_terbayar', 15, 2)->default(0.00);
            $table->string('status', 20)->default('BELUM_BAYAR');
            $table->timestamp('tanggal_bayar')->nullable();
            $table->timestamps();

            $table->unique(['pinjaman_id', 'cicilan_ke']);
            $table->index(['status', 'jatuh_tempo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cicilan_pinjaman');
    }
};
