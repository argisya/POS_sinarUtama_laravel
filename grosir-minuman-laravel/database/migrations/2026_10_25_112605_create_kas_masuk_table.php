<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kas_masuk', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('sumber', [
                'penjualan_tunai',
                'pelunasan_tagihan',
                'modal',
                'lain_lain',
            ])->default('lain_lain');
            $table->string('keterangan')->nullable();
            $table->bigInteger('jumlah');                            // rupiah
            $table->foreignId('barang_keluar_id')->nullable()
                  ->constrained('barang_keluar')->onDelete('cascade');
            $table->timestamps();

            $table->index('tanggal');
            $table->index('sumber');
        });
    }
    public function down(): void {
        Schema::dropIfExists('kas_masuk');
    }
};