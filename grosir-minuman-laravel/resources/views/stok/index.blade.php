@extends('layouts.app')
@section('title', 'Stok Barang')

@section('content')

{{-- Kartu Ringkasan --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
        <p class="text-xs text-gray-500 uppercase">Total Jenis Barang</p>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalJenis) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
        <p class="text-xs text-gray-500 uppercase">Total Stok</p>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalStok) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-500">
        <p class="text-xs text-gray-500 uppercase">Stok Menipis</p>
        <p class="text-2xl font-bold text-amber-600">{{ number_format($jumlahMenipis) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
        <p class="text-xs text-gray-500 uppercase">Stok Habis</p>
        <p class="text-2xl font-bold text-red-600">{{ number_format($jumlahHabis) }}</p>
    </div>
</div>

{{-- Filter & Pencarian --}}
<div class="bg-white rounded-lg shadow mb-4 p-4">
    <form method="GET" action="{{ route('stok.index') }}"
          class="flex flex-col md:flex-row gap-3 md:items-center">

        <div class="flex-1">
            <input type="text" name="q" value="{{ $search }}"
                placeholder="Cari nama barang..."
                class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div>
            <select name="filter" onchange="this.form.submit()"
                class="border rounded px-3 py-2 text-sm">
                <option value="semua"   @selected($filter === 'semua')>Semua</option>
                <option value="aman"    @selected($filter === 'aman')>Aman</option>
                <option value="menipis" @selected($filter === 'menipis')>Menipis</option>
                <option value="habis"   @selected($filter === 'habis')>Habis</option>
            </select>
        </div>

        <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm font-medium">
            Cari
        </button>

        @if ($search || $filter !== 'semua')
            <a href="{{ route('stok.index') }}"
               class="text-sm text-gray-500 hover:underline px-2">Reset</a>
        @endif
    </form>
</div>

{{-- Tabel Stok --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="font-semibold text-gray-700">Daftar Stok Barang</h3>
        <span class="text-xs text-gray-500">
            {{ $barang->total() }} barang ditemukan
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Nama Barang</th>
                    <th class="px-4 py-3 text-left">Satuan</th>
                    <th class="px-4 py-3 text-right">Harga</th>
                    <th class="px-4 py-3 text-right">Stok</th>
                    <th class="px-4 py-3 text-right">Min.</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($barang as $row)
                    @php
                        if ($row->stok <= 0) {
                            $status = 'Habis';
                            $badge  = 'bg-red-100 text-red-700';
                            $rowBg  = 'bg-red-50';
                        } elseif ($row->stok <= $row->stok_minimum) {
                            $status = 'Menipis';
                            $badge  = 'bg-amber-100 text-amber-700';
                            $rowBg  = 'bg-amber-50';
                        } else {
                            $status = 'Aman';
                            $badge  = 'bg-emerald-100 text-emerald-700';
                            $rowBg  = '';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $rowBg }}">
                        <td class="px-4 py-3">{{ $barang->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 font-medium">{{ $row->nama }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $row->satuan }}</td>
                        <td class="px-4 py-3 text-right">
                            Rp {{ number_format($row->harga, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">
                            {{ number_format($row->stok) }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">
                            {{ number_format($row->stok_minimum) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 text-xs rounded {{ $badge }}">
                                {{ $status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            Tidak ada barang yang cocok dengan pencarian / filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($barang->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $barang->links() }}
        </div>
    @endif
</div>

<div class="mt-4 text-xs text-gray-500">
    ℹ️ Halaman ini bersifat <b>read-only</b>. Stok diperbarui otomatis dari transaksi
    <a href="{{ route('barang-masuk.index') }}" class="text-emerald-600 hover:underline">Barang Masuk</a> dan
    <a href="{{ route('barang-keluar.index') }}" class="text-emerald-600 hover:underline">Barang Keluar</a>.
</div>

@endsection