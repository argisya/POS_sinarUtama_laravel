<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('harga');
        });
    }

    public function down(): void {
        Schema::table('barang', function (Blueprint $table) {
            $table->bigInteger('harga')->default(0);
        });
    }
};