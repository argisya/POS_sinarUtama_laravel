<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
    \App\Models\Barang::insert([
        ['nama' => 'Aqua 600ml',    'satuan' => 'dus', 'harga' => 35000, 'stok' => 0],
        ['nama' => 'Teh Pucuk 350ml','satuan' => 'dus', 'harga' => 40000, 'stok' => 0],
        ['nama' => 'Coca Cola 390ml','satuan' => 'dus', 'harga' => 55000, 'stok' => 0],
        ]);
    }
}
