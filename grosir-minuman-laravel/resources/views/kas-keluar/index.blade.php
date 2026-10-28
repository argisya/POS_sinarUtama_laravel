@extends('layouts.app')
@section('title', 'Kas Keluar')

@section('content')

{{-- Ringkasan Saldo --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
        <p class="text-xs text-gray-500 uppercase">Total Keuntungan (Masuk)</p>
        <p class="text-xl font-bold text-emerald-600">Rp {{ number_format($totalKeuntungan, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
        <p class="text-xs text-gray-500 uppercase">Total Pengeluaran</p>
        <p class="text-xl font-bold text-red-600">Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 {{ $sisaSaldo < 0 ? 'border-red-500' : 'border-blue-500' }}">
        <p class="text-xs text-gray-500 uppercase">Sisa Saldo Keuntungan</p>
            <p class="text-xl font-bold {{ $sisaSaldo < 0 ? 'text-red-600' : 'text-blue-700' }}">
            Rp {{ number_format($sisaSaldo, 0, ',', '.') }}
        </p>
    </div>
</div>

{{-- Form Input --}}
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="font-semibold mb-4 text-gray-700">Catat Pengeluaran (Kas Keluar)</h3>

    <form method="POST" action="{{ route('kas-keluar.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @csrf
        <div>
            <label class="block text-sm mb-1">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Keterangan Pengeluaran <span class="text-red-500">*</span></label>
            <input type="text" name="keterangan" value="{{ old('keterangan') }}" required
                placeholder="cth: Bayar Listrik, Beli ATK, dll"
                class="w-full border rounded px-3 py-2 text-sm @error('keterangan') border-red-500 @enderror">
            @error('keterangan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm mb-1">Jumlah (Rp) <span class="text-red-500">*</span></label>
            <input type="number" name="jumlah" value="{{ old('jumlah') }}" min="1" required
                class="w-full border rounded px-3 py-2 text-sm @error('jumlah') border-red-500 @enderror">
            @error('jumlah') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-4 mt-2">
            <button class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded text-sm font-medium">
                Simpan Pengeluaran
            </button>
            <span class="text-xs text-gray-500 ml-3">
                ℹ️ Kas keluar akan memotong Saldo Keuntungan Anda.
            </span>
        </div>
    </form>
</div>

{{-- Tabel Riwayat --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b">
        <h3 class="font-semibold text-gray-700">Riwayat Kas Keluar</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($kasKeluar as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $row->keterangan }}</td>
                        <td class="px-4 py-3 text-right font-bold text-red-600">
                            - Rp {{ number_format($row->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form method="POST" action="{{ route('kas-keluar.destroy', $row->id) }}"
                                  onsubmit="return confirm('Hapus catatan pengeluaran ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada pengeluaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($kasKeluar->hasPages())
        <div class="px-6 py-4 border-t">{{ $kasKeluar->links() }}</div>
    @endif
</div>

@endsection