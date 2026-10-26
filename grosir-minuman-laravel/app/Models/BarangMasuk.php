<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model {
    protected $table = 'barang_masuk';
    protected $fillable = [
        'barang_id', 'jumlah', 'harga_satuan', 'tanggal',
        'tanggal_jatuh_tempo',
        'supplier', 'status_pembayaran', 'jumlah_box'
    ];
    protected $casts = [
        'tanggal' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        ];

    public function barang() {
        return $this->belongsTo(Barang::class);
    }

    public function getTotalAttribute(): int {
        return $this->jumlah * $this->harga_satuan;
    }

    public function getStatusLabelAttribute(): string {
        return $this->status_pembayaran === 'lunas' ? 'Lunas' : 'Belum Lunas';
    }
}