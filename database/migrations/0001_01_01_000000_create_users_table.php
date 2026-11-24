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
        Schema::create('anggota', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_anggota', 20)->unique();
            $table->string('nama_lengkap');
            $table->string('no_hp', 20)->unique();
            $table->string('password');
            $table->enum('peran', ['ADMIN', 'ANGGOTA'])->default('ANGGOTA');
            $table->enum('status', ['AKTIF', 'KELUAR'])->default('AKTIF');
            $table->boolean('wajib_simpanan')->default(true);
            $table->date('tanggal_gabung');
            $table->date('tanggal_keluar')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Sesuaikan tabel sessions bawaan Laravel agar mendukung UUID
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('anggota');
    }
};
