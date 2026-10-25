<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('satuan')->default('dus'); // dus, botol, pcs
            $table->integer('harga')->default(0);
            $table->integer('stok')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('barang');
    }
};