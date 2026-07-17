<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom `name` dan `status` pada tabel users
     * yang dibutuhkan oleh halaman Manajemen Pengguna (Admin).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('login_id')->default('');
            $table->enum('status', ['Aktif', 'Nonaktif'])->after('role')->default('Aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name', 'status']);
        });
    }
};
