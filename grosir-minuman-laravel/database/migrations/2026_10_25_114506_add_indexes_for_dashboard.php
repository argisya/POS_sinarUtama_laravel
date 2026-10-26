<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('barang_keluar', function (Blueprint $table) {
            $table->index('tanggal');
            $table->index('tipe_pembayaran');
        });
        Schema::table('barang_masuk', function (Blueprint $table) {
            $table->index('tanggal');
        });
    }

    public function down(): void {
        Schema::table('barang_keluar', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['tipe_pembayaran']);
        });
        Schema::table('barang_masuk', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
        });
    }
};