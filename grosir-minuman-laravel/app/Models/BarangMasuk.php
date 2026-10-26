<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model {
    protected $table = 'barang_masuk';
    protected $fillable = [
        'barang_id', 'jumlah', 'harga_satuan', 'tanggal',
        'supplier', 'status_pembayaran',
    ];
    protected $casts = ['tanggal' => 'date'];

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