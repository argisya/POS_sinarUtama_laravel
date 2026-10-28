<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kas_keluar', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('keterangan');
            $table->bigInteger('jumlah');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('kas_keluar');
    }
};