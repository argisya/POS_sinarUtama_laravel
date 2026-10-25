<?php
namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        Barang::insert([
            ['nama' => 'Aqua 600ml',     'satuan' => 'dus', 'harga' => 35000, 'stok' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Teh Pucuk 350ml','satuan' => 'dus', 'harga' => 40000, 'stok' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Coca Cola 390ml','satuan' => 'dus', 'harga' => 55000, 'stok' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Golda Coffee 200ml','satuan' => 'dus', 'harga' => 45000, 'stok' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}