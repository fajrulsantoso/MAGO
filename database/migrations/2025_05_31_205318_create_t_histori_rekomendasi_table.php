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
        Schema::create('t_histori_rekomendasi', function (Blueprint $table) {
            $table->id('id_histori');
            $table->unsignedBigInteger('id_mahasiswa')->index();
            $table->unsignedBigInteger('id_lowongan')->index();
            $table->unsignedBigInteger('id_preferensi')->nullable()->index();
            $table->unsignedBigInteger('id_periode')->nullable()->index();
            $table->integer('ranking'); // 1=teratas dst
            $table->timestamps();

            $table->foreign('id_mahasiswa')->references('id_mahasiswa')->on('m_mahasiswa')->onDelete('cascade');
            $table->foreign('id_lowongan')->references('id_lowongan')->on('t_lowongan_magang')->onDelete('cascade');
            $table->foreign('id_preferensi')->references('id_preferensi')->on('r_preferensi_mahasiswa')->onDelete('set null');
            $table->foreign('id_periode')->references('id_periode')->on('m_periode')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_histori_rekomendasi');
    }
};
