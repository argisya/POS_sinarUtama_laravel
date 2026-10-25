<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model {
    protected $table = 'barang';
    protected $fillable = ['nama', 'satuan', 'harga', 'stok'];

    public function barangMasuk() {
        return $this->hasMany(BarangMasuk::class);
    }
    public function barangKeluar() {
    return $this->hasMany(BarangKeluar::class);
}
}