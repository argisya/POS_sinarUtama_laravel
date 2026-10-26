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
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

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

    {{-- Kas Hari Ini --}}
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Kas Masuk Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">
                    Rp {{ number_format($kasHariIni, 0, ',', '.') }}
                </p>
            </div>
            <div class="text-3xl">🧾</div>
        </div>
    </div>

    {{-- Piutang --}}
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Piutang</p>
                <p class="text-2xl font-bold text-red-600 mt-1">
                    Rp {{ number_format($totalPiutang, 0, ',', '.') }}
                </p>
            </div>
            <div class="text-3xl">📒</div>
        </div>
    </div>

    {{-- Stok --}}
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-amber-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Stok Perlu Perhatian</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">
                    {{ $stokMenipis + $stokHabis }}
                    <span class="text-sm font-normal text-gray-500">barang</span>
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $stokMenipis }} menipis · {{ $stokHabis }} habis
                </p>
            </div>
            <div class="text-3xl">📦</div>
        </div>
    </div>
</div>

{{-- ===== Baris 2: Grafik + Stok Kritis ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- Grafik 7 Hari --}}
    <div class="bg-white rounded-lg shadow p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
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

    {{-- Stok Kritis --}}
    <div class="bg-white rounded-lg shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-700">Stok Perlu Dipesan</h3>
            <a href="{{ route('stok.index') }}" class="text-xs text-emerald-600 hover:underline">
                Lihat semua →
            </a>
        </div>
        @forelse ($barangKritis as $b)
            @php
                $isHabis = $b->stok <= 0;
            @endphp
            <div class="flex items-center justify-between py-2 border-b last:border-0">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $b->nama }}</p>
                    <p class="text-xs text-gray-500">
                        Stok: <b class="{{ $isHabis ? 'text-red-600' : 'text-amber-600' }}">
                            {{ $b->stok }} {{ $b->satuan }}
                        </b>
                        / min {{ $b->stok_minimum }}
                    </p>
                </div>
                <span class="ml-2 px-2 py-1 text-xs rounded whitespace-nowrap
                    {{ $isHabis ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $isHabis ? 'Habis' : 'Menipis' }}
                </span>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-6">
                ✓ Semua stok aman
            </p>
        @endforelse
    </div>
</div>

{{-- ===== Baris 3: Transaksi Terbaru + Shortcut ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

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
                                @elseif ($t['pembayaran'] === 'kredit')
                                    <span class="px-2 py-1 text-xs rounded bg-amber-100 text-amber-700">Kredit</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-700">Cash</span>
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

    {{-- Shortcut Aksi Cepat --}}
    <div class="bg-white rounded-lg shadow p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Aksi Cepat</h3>
        <div class="space-y-2">
            <a href="{{ route('barang-masuk.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-50 hover:bg-blue-100 transition">
                <span class="text-xl">📥</span>
                <span class="text-sm font-medium text-blue-700">Catat Barang Masuk</span>
            </a>
            <a href="{{ route('barang-keluar.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 hover:bg-emerald-100 transition">
                <span class="text-xl">📤</span>
                <span class="text-sm font-medium text-emerald-700">Catat Barang Keluar</span>
            </a>
            <a href="{{ route('kas-masuk.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition">
                <span class="text-xl">💵</span>
                <span class="text-sm font-medium text-indigo-700">Catat Kas Masuk</span>
            </a>
            <a href="{{ route('buku-tagihan.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 hover:bg-red-100 transition">
                <span class="text-xl">📒</span>
                <span class="text-sm font-medium text-red-700">Lihat Buku Tagihan</span>
            </a>
        </div>
    </div>
</div>

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