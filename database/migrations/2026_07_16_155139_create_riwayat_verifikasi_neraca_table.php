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
        Schema::create('riwayat_verifikasi_neraca', function (Blueprint $table) {

            $table->id();

            $table->foreignId('neraca_pangan_id')
                ->constrained('neraca_pangan')
                ->cascadeOnDelete();

            $table->foreignId('verifikator_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('status_lama',[
                'menunggu',
                'revisi',
                'valid'
            ]);

            $table->enum('status_baru',[
                'menunggu',
                'revisi',
                'valid'
            ]);

            $table->text('catatan')->nullable();

            $table->timestamp('tanggal_verifikasi');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_verifikasi_neraca');
    }
};
