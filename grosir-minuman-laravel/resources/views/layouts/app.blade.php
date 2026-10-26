<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'POS') — Grosir Minuman Sinar Utama</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-64 bg-slate-800 text-slate-100 flex flex-col">
        <div class="p-4 border-b border-slate-700">
            <h1 class="font-bold text-base leading-tight">
                Grosir Minuman<br><span class="text-emerald-400">Sinar Utama</span>
            </h1>
        </div>
        <nav class="flex-1 p-2 space-y-1 text-sm">
            @php
                $menu = [
                    ['label' => 'Dashboard',     'route' => route('dashboard'),          'active' => request()->routeIs('dashboard')],
                    ['label' => 'Master Barang', 'route' => route('master-barang.index'), 'active' => request()->routeIs('master-barang.*')],
                    ['label' => 'Barang Masuk',  'route' => route('barang-masuk.index'),  'active' => request()->routeIs('barang-masuk.*')],
                    ['label' => 'Barang Keluar', 'route' => route('barang-keluar.index'), 'active' => request()->routeIs('barang-keluar.*')],
                    ['label' => 'Stok Barang',   'route' => route('stok.index'),          'active' => request()->routeIs('stok.*')],
                    ['label' => 'Kas Masuk',     'route' => route('kas-masuk.index'),     'active' => request()->routeIs('kas-masuk.*')],
                    ['label' => 'Buku Tagihan',  'route' => route('buku-tagihan.index'),  'active' => request()->routeIs('buku-tagihan.*')],
                ];
            @endphp
            @foreach ($menu as $item)
                <a href="{{ $item['route'] }}"
                   class="block px-4 py-2 rounded transition
                          {{ $item['active'] ? 'bg-slate-700 text-emerald-400 font-semibold' : 'hover:bg-slate-700' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="p-4 text-xs text-slate-400 border-t border-slate-700">
            &copy; {{ date('Y') }} Sinar Utama
        </div>
    </aside>

    {{-- Main --}}
    <main class="flex-1 overflow-auto">
        <header class="bg-white shadow px-6 py-4 flex items-center justify-between">
            <h2 class="text-xl font-semibold">@yield('title')</h2>
            <span class="text-sm text-gray-500">{{ now()->translatedFormat('d F Y') }}</span>
        </header>

        <div class="p-6">
            @if (session('success'))
                <div class="bg-emerald-100 text-emerald-800 px-4 py-2 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>