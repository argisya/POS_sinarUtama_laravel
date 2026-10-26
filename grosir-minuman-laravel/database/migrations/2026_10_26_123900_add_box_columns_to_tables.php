<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('barang', function (Blueprint $table) {
            $table->integer('pcs_per_box')->default(1)->after('nama');
        });
        Schema::table('barang_masuk', function (Blueprint $table) {
            $table->integer('jumlah_box')->default(0)->after('barang_id');
        });
        Schema::table('barang_keluar', function (Blueprint $table) {
            $table->integer('jumlah_box')->default(0)->after('barang_id');
        });
    }

    public function down(): void {
        Schema::table('barang', function (Blueprint $table) { $table->dropColumn('pcs_per_box'); });
        Schema::table('barang_masuk', function (Blueprint $table) { $table->dropColumn('jumlah_box'); });
        Schema::table('barang_keluar', function (Blueprint $table) { $table->dropColumn('jumlah_box'); });
    }
};