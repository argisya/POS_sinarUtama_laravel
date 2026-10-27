<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model {
    protected $table = 'barang';
    protected $fillable = ['kode_barang', 'nama', 'satuan', 'harga', 'stok', 'stok_minimum', 'pcs_per_box'];

    protected static function boot() {
        parent::boot();
        static::creating(function ($barang) {
            // Kode otomatis
            if (empty($barang->kode_barang)) {
                $last = static::orderByDesc('id')->first();
                $next = $last ? ((int) substr($last->kode_barang, 4)) + 1 : 1;
                $barang->kode_barang = 'BRG-' . str_pad($next, 4, '0', STR_PAD_LEFT);
            }
            // Default untuk barang baru dari Master
            $barang->satuan       = $barang->satuan       ?? 'pcs';
            $barang->stok_minimum = $barang->stok_minimum ?? 5;
            $barang->harga        = $barang->harga        ?? 0;
            $barang->stok         = $barang->stok         ?? 0;
        });
    }

    public function barangMasuk() {
        return $this->hasMany(BarangMasuk::class);
    }
    public function barangKeluar() {
        return $this->hasMany(BarangKeluar::class);
    }

    // Helper status: aman / tidak_aman / habis (Berdasarkan Box)
    public function getStatusStokAttribute(): string {
        // Konversi total pcs di database menjadi box
        $totalBox = floor($this->stok / ($this->pcs_per_box ?: 1));

        if ($totalBox <= 0) {
            return 'habis';
        }
        if ($totalBox <= 10) {
            return 'tidak_aman';
        }
        return 'aman';
    }
}