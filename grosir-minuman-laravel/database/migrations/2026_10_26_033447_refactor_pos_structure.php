<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // ===== BARANG: kode_barang =====
        if (!Schema::hasColumn('barang', 'kode_barang')) {
            Schema::table('barang', function (Blueprint $table) {
                $table->string('kode_barang', 20)->nullable()->after('id');
            });
        }

        // Backfill kode_barang yang kosong
        $rows = DB::table('barang')
            ->where(function ($q) {
                $q->whereNull('kode_barang')->orWhere('kode_barang', '');
            })
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            DB::table('barang')->where('id', $row->id)->update([
                'kode_barang' => 'BRG-' . str_pad($row->id, 4, '0', STR_PAD_LEFT),
            ]);
        }

        // Paksa satuan & stok_minimum = pcs / 5
        DB::statement("UPDATE barang SET satuan = 'pcs'");
        DB::statement("UPDATE barang SET stok_minimum = 5");

        // Ubah kolom pakai raw SQL (hindari dependency doctrine/dbal)
        DB::statement("ALTER TABLE barang MODIFY kode_barang VARCHAR(20) NOT NULL");
        DB::statement("ALTER TABLE barang MODIFY satuan VARCHAR(255) NOT NULL DEFAULT 'pcs'");
        DB::statement("ALTER TABLE barang MODIFY stok_minimum INT NOT NULL DEFAULT 5");

        // Tambah unique index kalau belum ada
        $hasIndex = DB::select("SHOW INDEX FROM barang WHERE Key_name = 'barang_kode_barang_unique'");
        if (empty($hasIndex)) {
            DB::statement("ALTER TABLE barang ADD UNIQUE KEY barang_kode_barang_unique (kode_barang)");
        }

        // ===== BARANG MASUK =====
        if (Schema::hasColumn('barang_masuk', 'keterangan')) {
            Schema::table('barang_masuk', function (Blueprint $table) {
                $table->dropColumn('keterangan');
            });
        }
        if (!Schema::hasColumn('barang_masuk', 'harga_satuan')) {
            Schema::table('barang_masuk', function (Blueprint $table) {
                $table->bigInteger('harga_satuan')->default(0)->after('jumlah');
            });
        }
        if (!Schema::hasColumn('barang_masuk', 'status_pembayaran')) {
            Schema::table('barang_masuk', function (Blueprint $table) {
                $table->enum('status_pembayaran', ['lunas', 'belum_lunas'])
                      ->default('belum_lunas')->after('supplier');
            });
        }

        // ===== BARANG KELUAR =====
        if (Schema::hasColumn('barang_keluar', 'tipe_pembayaran')) {
            Schema::table('barang_keluar', function (Blueprint $table) {
                $table->dropColumn('tipe_pembayaran');
            });
        }
        if (!Schema::hasColumn('barang_keluar', 'keuntungan')) {
            Schema::table('barang_keluar', function (Blueprint $table) {
                $table->bigInteger('keuntungan')->default(0)->after('total_harga');
            });
        }
        if (!Schema::hasColumn('barang_keluar', 'status_pembayaran')) {
            Schema::table('barang_keluar', function (Blueprint $table) {
                $table->enum('status_pembayaran', ['lunas', 'belum_lunas'])
                      ->default('belum_lunas')->after('pembeli');
            });
        }
    }

    public function down(): void {
        Schema::table('barang', function (Blueprint $table) {
            if (Schema::hasColumn('barang', 'kode_barang')) {
                $table->dropColumn('kode_barang');
            }
        });
        if (Schema::hasColumn('barang_masuk', 'harga_satuan')) {
            Schema::table('barang_masuk', function (Blueprint $table) {
                $table->dropColumn(['harga_satuan', 'status_pembayaran']);
            });
        }
        if (Schema::hasColumn('barang_keluar', 'keuntungan')) {
            Schema::table('barang_keluar', function (Blueprint $table) {
                $table->dropColumn(['keuntungan', 'status_pembayaran']);
            });
        }
    }
};