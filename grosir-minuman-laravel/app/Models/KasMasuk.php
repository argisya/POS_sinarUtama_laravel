<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KasMasuk extends Model {
    protected $table = 'kas_masuk';
    protected $fillable = [
        'tanggal', 'sumber', 'keterangan', 'jumlah', 'barang_keluar_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function barangKeluar() {
        return $this->belongsTo(BarangKeluar::class);
    }

    public function getSumberLabelAttribute(): string {
        return match ($this->sumber) {
            'penjualan_tunai'   => 'Penjualan Tunai',
            'pelunasan_tagihan' => 'Pelunasan Tagihan',
            'modal'             => 'Modal',
            'lain_lain'         => 'Lain-lain',
            default             => $this->sumber,
        };
    }
}