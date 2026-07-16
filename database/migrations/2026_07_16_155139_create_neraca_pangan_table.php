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
        Schema::create('neraca_pangan', function (Blueprint $table) {

            $table->id();

            $table->foreignId('komoditas_id')
                ->constrained('komoditas')
                ->cascadeOnDelete();

            $table->date('periode');

            $table->decimal('stok_awal',15,2)->default(0);
            $table->decimal('produksi',15,2)->default(0);
            $table->decimal('masuk',15,2)->default(0);
            $table->decimal('keluar',15,2)->default(0);

            $table->decimal('kebutuhan_rumah_tangga',15,2)->default(0);
            $table->decimal('kebutuhan_non_rumah_tangga',15,2)->default(0);

            $table->enum('status',[
                'menunggu',
                'revisi',
                'valid'
            ])->default('menunggu');

            $table->text('keterangan')->nullable();

            $table->foreignId('diinput_oleh')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamp('diajukan_pada')->nullable();

            $table->foreignId('diverifikasi_oleh')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamp('diverifikasi_pada')->nullable();

            $table->timestamps();

            $table->unique(['komoditas_id','periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neraca_pangan');
    }
};
