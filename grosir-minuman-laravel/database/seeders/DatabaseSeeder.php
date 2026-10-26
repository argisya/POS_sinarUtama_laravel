<?php
namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        \App\Models\Barang::insert([
            ['kode_barang' => 'BRG-0001', 'nama' => 'Aqua 600ml',          'satuan' => 'pcs', 'harga' => 3000, 'stok' => 0, 'stok_minimum' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['kode_barang' => 'BRG-0002', 'nama' => 'Teh Pucuk 350ml',     'satuan' => 'pcs', 'harga' => 4000, 'stok' => 0, 'stok_minimum' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['kode_barang' => 'BRG-0003', 'nama' => 'Coca Cola 390ml',     'satuan' => 'pcs', 'harga' => 5500, 'stok' => 0, 'stok_minimum' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['kode_barang' => 'BRG-0004', 'nama' => 'Golda Coffee 200ml',  'satuan' => 'pcs', 'harga' => 4500, 'stok' => 0, 'stok_minimum' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}