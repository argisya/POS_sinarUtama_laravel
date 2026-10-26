<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model {
    protected $table = 'barang_keluar';
    protected $fillable = [
        'barang_id', 'jumlah', 'total_harga', 'keuntungan', 'tanggal',
        'pembeli', 'status_pembayaran', 'keterangan',
    ];
    protected $casts = ['tanggal' => 'date'];

    public function barang() {
        return $this->belongsTo(Barang::class);
    }
    public function kasMasuk() {
        return $this->hasMany(KasMasuk::class);
    }
    public function pembayaranTagihan() {
        return $this->hasMany(KasMasuk::class)->where('sumber', 'pelunasan_tagihan');
    }

    public function getTotalKeuntunganAttribute(): int {
        return $this->keuntungan * $this->jumlah;
    }

    public function getStatusLabelAttribute(): string {
        return $this->status_pembayaran === 'lunas' ? 'Lunas' : 'Belum Lunas';
    }
}