@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- Sapaan --}}
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Selamat datang 👋</h2>
    <p class="text-sm text-gray-500 mt-1">
        Ringkasan toko per {{ now()->translatedFormat('l, d F Y') }}
    </p>
</div>

{{-- ===== 4 Kartu Metrik Utama ===== --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
    {{-- Penjualan Hari Ini --}}
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-emerald-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Penjualan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">
                    Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}
                </p>
            </div>
            <div class="text-3xl">💰</div>
        </div>
    </div>

    {{-- Saldo Kas Bulan Ini --}}
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Saldo Kas Bulan Ini</p>
                <p class="text-2xl font-bold mt-1 {{ ($kasBulanIni ?? 0) < 0 ? 'text-red-600' : 'text-blue-600' }}">
                    Rp {{ number_format($kasBulanIni ?? 0, 0, ',', '.') }}
                </p>
                <p class="text-[10px] text-gray-400 mt-1">(Kas Masuk - Total Pengeluaran)</p>
            </div>
            <div class="text-3xl">💵</div>
        </div>
    </div>

     {{-- Total Piutang (Barang Masuk Belum Lunas) --}}
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Piutang</p>
                <p class="text-xl font-bold text-red-600 mt-1">
                    Rp {{ number_format($totalPiutang ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <div class="text-3xl">📒</div>
        </div>
    </div>

    {{-- Total Utang (Barang Keluar Belum Lunas) --}}
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-orange-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Utang</p>
                <p class="text-xl font-bold text-orange-600 mt-1">
                    Rp {{ number_format($totalUtang ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <div class="text-3xl">💳</div>
        </div>
    </div>

    {{-- Card Keuntungan (Lunas & Setelah Kas Keluar) --}}
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500 flex flex-col justify-between">
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wide font-bold mb-3">Keuntungan</p>
            
            <div class="space-y-1 text-xs">
                {{-- Card Keuntungan --}}
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500 flex flex-col justify-between">
                    <div>
                        <!-- {{-- 1. Total Keuntungan (Lunas) - Sama dengan Buku Tagihan --}}
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Total Keuntungan (Lunas)</p>
                        <p class="text-xl font-bold text-emerald-600 mt-1">
                            Rp {{ number_format($totalKeuntunganKotor ?? 0, 0, ',', '.') }}
                        </p> -->
                        
                        <!-- {{-- Garis Pembatas --}}
                        <div class="border-t border-gray-200 my-3"></div> -->

                        {{-- 2. Sisa Saldo Setelah Kas Keluar --}}
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Sisa Saldo (Setelah Kas Keluar)</p>
                        <p class="text-xl font-bold mt-1 {{ ($totalKeuntungan ?? 0) < 0 ? 'text-red-600' : 'text-blue-600' }}">
                            Rp {{ number_format($totalKeuntungan ?? 0, 0, ',', '.') }}
                        </p>
                    </div>

                    {{-- 3. Info Total Modal --}}
                    <div class="mt-3 pt-2 border-t border-gray-100">
                        <p class="text-[10px] text-gray-400 text-center">
                            Total Modal: Rp {{ number_format($totalModal ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
    {{-- Total Modal Pembelian (Barang Masuk) --}}
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-indigo-500 cursor-pointer hover:shadow-lg hover:-translate-y-0.5 transition-transform"
        onclick="document.getElementById('modalBarangMasuk').showModal()"
        title="Klik untuk lihat detail barang masuk">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Modal Pembelian</p>
                <p class="text-2xl font-bold text-indigo-700 mt-1">
                    Rp {{ number_format($totalModal, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    Klik untuk detail →
                </p>
            </div>
            <div class="text-3xl">🛒</div>
        </div>
    </div>
</div>

{{-- ===== Baris Grafik  ===== --}}
<div class="grid grid-cols-1 rounded-lg p-6 mb-6">

    {{-- Grafik 7 Hari --}}
    <div class="bg-white rounded-lg shadow p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4 pb-3 border-b">
            <div>
                <h3 class="font-semibold text-gray-700">Penjualan 7 Hari Terakhir</h3>
                <p class="text-xs text-gray-500 mt-1">
                    Total: <b class="text-emerald-600">Rp {{ number_format($totalMingguIni, 0, ',', '.') }}</b>
                </p>
            </div>
            <span class="text-xs text-gray-500">dalam rupiah</span>
        </div>

        {{-- ⬇️ WAJIB: wrapper dengan tinggi tetap --}}
        <div class="relative w-full" style="height: 260px;">
            <canvas id="chartPenjualan"></canvas>
        </div>

        @if ($totalMingguIni === 0)
            <p class="text-center text-xs text-gray-400 mt-2">
                Belum ada penjualan dalam 7 hari terakhir.
            </p>
        @endif
    </div>
</div>
{{-- ===== Baris 2: Pengingat Jatuh Tempo (Full Width) ===== --}}
<div class="grid grid-cols-1 rounded-lg p-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b">
            <h3 class="font-semibold text-gray-700 text-lg">Pengingat Jatuh Tempo</h3>
            <a href="{{ route('barang-masuk.index') }}" class="text-sm text-red-600 hover:underline">
                Lihat Semua di Barang Masuk →
            </a>
        </div>
        
        {{-- Menggunakan grid 2 kolom agar jika datanya banyak, tampilannya rapi menyamping --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
            @forelse ($reminders as $rem)
                @php
                    $diff = now()->startOfDay()->diffInDays($rem->tanggal_jatuh_tempo->startOfDay(), false);
                    $isOverdue = $diff < 0;
                    $isToday = $diff === 0;
                @endphp
                <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 px-2 -mx-2 rounded transition">
                    <div class="min-w-0 flex-1 pr-4">
                        <p class="text-sm font-bold text-gray-800 truncate" title="{{ $rem->barang->nama ?? '-' }}">
                            {{ $rem->barang->nama ?? '-' }}
                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <p class="text-xs text-gray-500 whitespace-nowrap">
                                Jatuh Tempo: <b>{{ $rem->tanggal_jatuh_tempo->format('d/m/Y') }}</b>
                            </p>
                            <span class="text-gray-300 text-xs">•</span>
                            <p class="text-xs text-gray-500 truncate">
                                Supplier: {{ $rem->supplier ?? '-' }}
                            </p>
                        </div>
                    </div>
                    
                    {{-- Badge Waktu --}}
                    <div class="text-right whitespace-nowrap">
                        @if ($isOverdue)
                            <span class="px-3 py-1 text-xs rounded-md bg-red-100 text-red-700 font-bold border border-red-200">
                                Telat {{ abs($diff) }} hr
                            </span>
                        @elseif ($isToday)
                            <span class="px-3 py-1 text-xs rounded-md bg-orange-100 text-orange-700 font-bold border border-orange-200">
                                Hari Ini!
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs rounded-md bg-blue-50 text-blue-700 font-medium border border-blue-100">
                                {{ $diff }} hr lagi
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-1 md:col-span-2 py-8 text-center">
                    <span class="text-4xl mb-2 block">✓</span>
                    <p class="text-sm text-gray-400">Tidak ada tagihan yang mendekati jatuh tempo.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ===== Baris 3: Transaksi Terbaru + Shortcut ===== --}}
<div class="grid grid-cols-1 rounded-lg p-6 mb-6">

    {{-- Transaksi Terbaru --}}
    <div class="bg-white rounded-lg shadow p-5 lg:col-span-2">
        <h3 class="font-semibold text-gray-700 mb-4">Transaksi Terbaru</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-500 uppercase text-xs border-b">
                    <tr>
                        <th class="py-2 text-left">Tanggal</th>
                        <th class="py-2 text-left">Tipe</th>
                        <th class="py-2 text-left">Barang</th>
                        <th class="py-2 text-right">Jumlah</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($recentTransaksi as $t)
                        <tr>
                            <td class="py-2 text-gray-600 text-xs">
                                {{ \Carbon\Carbon::parse($t['tanggal'])->format('d/m/Y') }}
                            </td>
                            <td class="py-2">
                                @if ($t['tipe'] === 'masuk')
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">Masuk</span>
                                @elseif ($t['pembayaran'] === 'belum_lunas')
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Belum Lunas</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-700">Lunas</span>
                                @endif
                            </td>
                            <td class="py-2 text-gray-800">{{ $t['barang'] }}</td>
                            <td class="py-2 text-right text-gray-600">
                                {{ $t['jumlah'] }} {{ $t['satuan'] }}
                            </td>
                            <td class="py-2 text-right">
                                @if ($t['total'] > 0)
                                    Rp {{ number_format($t['total'], 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-400">
                                Belum ada transaksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===== Modal Detail Barang Masuk ===== --}}
<dialog id="modalBarangMasuk"
    class="rounded-lg shadow-2xl backdrop:bg-black/50 p-0 w-full max-w-3xl">
    <div class="p-6">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Detail Barang Masuk — Modal Pembelian</h3>
                <div class="text-sm text-gray-600 mt-2 space-y-0.5">
                    <p>
                        Total seluruh:
                        <b class="text-indigo-600">Rp {{ number_format($totalModal, 0, ',', '.') }}</b>
                    </p>
                    <p class="text-xs">
                        — Lunas:
                        <b class="text-emerald-600">Rp {{ number_format($totalModalLunas, 0, ',', '.') }}</b>
                        · Belum Lunas:
                        <b class="text-red-600">Rp {{ number_format($totalModalBelumLunas, 0, ',', '.') }}</b>
                    </p>
                    <p>
                        Bulan ini ({{ now()->translatedFormat('F Y') }}):
                        <b class="text-emerald-600">Rp {{ number_format($totalModalBulanIni, 0, ',', '.') }}</b>
                    </p>
                </div>
            </div>
            <button type="button"
                onclick="document.getElementById('modalBarangMasuk').close()"
                class="text-gray-400 hover:text-gray-700 text-2xl leading-none -mt-1">×</button>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto max-h-96 overflow-y-auto border rounded">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-2 text-left">Tanggal</th>
                        <th class="px-3 py-2 text-left">Kode</th>
                        <th class="px-3 py-2 text-left">Barang</th>
                        <th class="px-3 py-2 text-right">Jumlah Box</th> 
                        <th class="px-3 py-2 text-right">Harga / Box</th> 
                        <th class="px-3 py-2 text-right">Total</th>
                        <th class="px-3 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($detailBarangMasuk as $m)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-xs whitespace-nowrap">
                                {{ $m->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="px-3 py-2 font-mono text-xs">
                                {{ $m->barang->kode_barang ?? '-' }}
                            </td>
                            <td class="px-3 py-2">{{ $m->barang->nama ?? '-' }}</td>
                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                {{ number_format($m->jumlah_box) }} Box {{-- ⬅️ Ubah ke jumlah_box --}}
                            </td>
                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                Rp {{ number_format($m->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 text-right font-semibold whitespace-nowrap">
                                {{-- ⬅️ Ubah perkalian ke jumlah_box * harga_satuan --}}
                                Rp {{ number_format($m->jumlah_box * $m->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if ($m->status_pembayaran === 'lunas')
                                    <span class="px-1.5 py-0.5 text-xs rounded bg-emerald-100 text-emerald-700">Lunas</span>
                                @else
                                    <span class="px-1.5 py-0.5 text-xs rounded bg-red-100 text-red-700">Belum</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-gray-400">
                                Belum ada transaksi barang masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-semibold text-sm border-t-2 border-gray-300">
                    <tr>
                        <td colspan="5" class="px-3 py-2 text-right text-gray-700">Total {{ $detailBarangMasuk->count() }} transaksi teratas:</td>
                        <td class="px-3 py-2 text-right text-indigo-700">
                            {{-- ⬅️ Ubah perkalian ke jumlah_box * harga_satuan --}}
                            Rp {{ number_format($detailBarangMasuk->sum(fn($m) => $m->jumlah_box * $m->harga_satuan), 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Footer --}}
        <div class="mt-4 flex items-center justify-between">
            <a href="{{ route('barang-masuk.index') }}"
               class="text-sm text-indigo-600 hover:underline">
                Lihat semua di menu Barang Masuk →
            </a>
            <button type="button"
                onclick="document.getElementById('modalBarangMasuk').close()"
                class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded text-sm">
                Tutup
            </button>
        </div>
    </div>
</dialog>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('chartPenjualan');
    if (!canvas) return;

    const labels = @json($chartLabels);
    const data   = @json($chartPenjualan);
    const maxVal = Math.max(...data, 1);

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Penjualan',
                data: data,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.12)',
                borderWidth: 2.5,
                tension: 0.35,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                    padding: 10,
                    titleFont: { size: 12 },
                    bodyFont: { size: 13, weight: 'bold' },
                    callbacks: {
                        label: (ctx) =>
                            'Rp ' + (ctx.parsed.y || 0).toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: maxVal * 1.2,
                    ticks: {
                        color: '#9ca3af',
                        font: { size: 11 },
                        callback: (v) => {
                            if (v === 0) return '0';
                            if (v >= 1_000_000) return 'Rp ' + (v / 1_000_000).toFixed(1) + 'jt';
                            if (v >= 1_000)     return 'Rp ' + (v / 1_000).toFixed(0) + 'rb';
                            return 'Rp ' + v;
                        }
                    },
                    grid: { color: '#f3f4f6', drawBorder: false }
                },
                x: {
                    ticks: { color: '#9ca3af', font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>

@endsection