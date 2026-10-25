<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('barang_keluar', function (Blueprint $table) {
            $table->bigInteger('total_harga')->default(0)->after('jumlah');
        });

        // Backfill data lama (hitung dari harga barang saat ini)
        DB::statement('
            UPDATE barang_keluar bk
            JOIN barang b ON bk.barang_id = b.id
            SET bk.total_harga = bk.jumlah * b.harga
        ');
    }

    public function down(): void {
        Schema::table('barang_keluar', function (Blueprint $table) {
            $table->dropColumn('total_harga');
        });
    }
};