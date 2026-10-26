@extends('layouts.app')
@section('title', 'Stok Barang')

@section('content')

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
        <p class="text-xs text-gray-500 uppercase">Total Jenis Barang</p>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalJenis) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
        <p class="text-xs text-gray-500 uppercase">Total Stok</p>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalStok) }} <span class="text-sm font-normal">pcs</span></p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-500">
        <p class="text-xs text-gray-500 uppercase">Tidak Aman</p>
        <p class="text-2xl font-bold text-amber-600">{{ number_format($jumlahTidakAman) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
        <p class="text-xs text-gray-500 uppercase">Habis</p>
        <p class="text-2xl font-bold text-red-600">{{ number_format($jumlahHabis) }}</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow mb-4 p-4">
    <form method="GET" action="{{ route('stok.index') }}" class="flex flex-col md:flex-row gap-3 md:items-center">
        <div class="flex-1">
            <input type="text" name="q" value="{{ $search }}"
                placeholder="Cari kode / nama barang..."
                class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <select name="filter" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
            <option value="semua"      @selected($filter === 'semua')>Semua</option>
            <option value="aman"       @selected($filter === 'aman')>Aman</option>
            <option value="tidak_aman" @selected($filter === 'tidak_aman')>Tidak Aman</option>
            <option value="habis"      @selected($filter === 'habis')>Habis</option>
        </select>
        <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm font-medium">Cari</button>
        @if ($search || $filter !== 'semua')
            <a href="{{ route('stok.index') }}" class="text-sm text-gray-500 hover:underline px-2">Reset</a>
        @endif
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="font-semibold text-gray-700">Daftar Stok Barang</h3>
        <span class="text-xs text-gray-500">{{ $barang->total() }} barang</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama Barang</th>
                    <th class="px-4 py-3 text-right">Harga Modal</th>
                    <th class="px-4 py-3 text-right">Qty</th>
                    <th class="px-4 py-3 text-center">Pcs</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($barang as $row)
                    @php
                        $status = $row->status_stok; // aman | tidak_aman | habis
                        $badge = match($status) {
                            'aman'       => 'bg-emerald-100 text-emerald-700',
                            'tidak_aman' => 'bg-amber-100 text-amber-700',
                            'habis'      => 'bg-red-100 text-red-700',
                        };
                        $label = match($status) {
                            'aman'       => 'Aman',
                            'tidak_aman' => 'Tidak Aman',
                            'habis'      => 'Habis',
                        };
                        $rowBg = $status === 'habis' ? 'bg-red-50' : ($status === 'tidak_aman' ? 'bg-amber-50' : '');
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $rowBg }}">
                        <td class="px-4 py-3 font-mono text-xs">{{ $row->kode_barang }}</td>
                        <td class="px-4 py-3 font-medium">{{ $row->nama }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($row->harga, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ number_format($row->stok) }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $row->satuan }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 text-xs rounded {{ $badge }}">{{ $label }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($barang->hasPages())
        <div class="px-6 py-4 border-t">{{ $barang->links() }}</div>
    @endif
</div>

<div class="mt-4 text-xs text-gray-500">
    ℹ️ Status: <b>Aman</b> jika stok > 5, <b>Tidak Aman</b> jika 1–5, <b>Habis</b> jika 0.
</div>

@endsection