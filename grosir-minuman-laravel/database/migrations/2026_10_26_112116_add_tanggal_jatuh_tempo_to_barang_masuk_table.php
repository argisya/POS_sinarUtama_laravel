<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('barang_masuk', function (Blueprint $table) {
            // Nullable karena mungkin ada barang masuk yang langsung lunas (cash)
            $table->date('tanggal_jatuh_tempo')->nullable()->after('tanggal');
        });
    }

    public function down(): void {
        Schema::table('barang_masuk', function (Blueprint $table) {
            $table->dropColumn('tanggal_jatuh_tempo');
        });
    }
};