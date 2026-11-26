<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#0B0C14]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CORET! CHAOS — Multiplayer Drawing & Sabotage')</title>

    {{-- Google Fonts: Playfair Display + Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;0,900;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Vite CSS & JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-ui { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col bg-[#0B0C14] text-[#F8FAFC] font-ui antialiased selection:bg-[#C3F53C] selection:text-[#0B0C14]">

    {{-- Top Navbar --}}
    <header class="sticky top-0 z-40 bg-[#0B0C14]/90 backdrop-blur-md border-b border-[#23263A] px-4 lg:px-8 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            {{-- Brand Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 group shrink-0">
                <span class="text-xl group-hover:scale-110 transition-transform">🎨</span>
                <span class="text-xl tracking-wider font-extrabold text-white">
                    CORET! <span class="text-[#C3F53C]">CHAOS</span>
                </span>
            </a>

            {{-- Center Navigation Tabs --}}
            <nav class="hidden md:flex items-center gap-1.5 bg-[#141625] p-1 rounded-xl border border-[#23263A]">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'bg-[#23263A] text-white font-semibold shadow-sm' : 'text-gray-400 hover:text-white' }} px-4 py-1.5 rounded-lg text-xs tracking-wide transition-colors">
                    Beranda
                </a>
                <a href="{{ route('lobby.hub') }}" class="{{ request()->routeIs('lobby.hub') || request()->routeIs('lobby.room') ? 'bg-[#23263A] text-white font-semibold shadow-sm' : 'text-gray-400 hover:text-white' }} px-4 py-1.5 rounded-lg text-xs tracking-wide transition-colors">
                    Lobby Hub
                </a>
            </nav>

            {{-- Right Section: Latency & User Profile --}}
            <div class="flex items-center gap-3">
                {{-- Latency Indicator --}}
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#141625] border border-[#23263A] text-[11px] font-semibold text-gray-300">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#C3F53C] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#C3F53C]"></span>
                    </span>
                    <span>24ms ASIA-SEA</span>
                </div>

                {{-- User Profile Pill & Dropdown --}}
                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button" class="flex items-center gap-2.5 bg-[#141625] hover:bg-[#1B1D30] border border-[#23263A] rounded-full pl-3 pr-1.5 py-1 transition-all focus:outline-none focus:ring-2 focus:ring-[#C3F53C]/50">
                            <div class="flex flex-col text-right leading-tight">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="text-xs font-bold text-white">{{ Auth::user()->nickname }}</span>
                                    <span class="text-[10px] font-extrabold bg-[#7C3AED] text-white px-1.5 py-0.2 rounded">LV.{{ Auth::user()->level ?? 1 }}</span>
                                </div>
                                <span class="text-[9px] uppercase tracking-wider text-gray-400 font-medium">{{ Auth::user()->player_title ?? 'Pemula Spidol' }}</span>
                            </div>
                            <x-player-avatar :avatar="Auth::user()->avatar" size="sm" glow="none" class="rounded-full ring-1 ring-[#C3F53C]/50" />
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open" @click.outside="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 rounded-xl bg-[#141625] border border-[#23263A] shadow-2xl py-1 z-50">
                            <div class="px-3 py-2 border-b border-[#23263A]">
                                <p class="text-xs font-bold text-white truncate">{{ Auth::user()->nickname }}</p>
                                <p class="text-[11px] text-gray-400 truncate">
                                    {{ str_ends_with(Auth::user()->email, '@guest.coretchaos.local') ? 'Akun Tamu' : Auth::user()->email }}
                                </p>
                            </div>
                            <a href="{{ route('lobby.hub') }}" class="block px-3 py-2 text-xs text-gray-300 hover:bg-[#23263A] hover:text-white transition-colors">
                                🏠 Lobby Hub
                            </a>
                            <button type="button" @click="open = false; $dispatch('open-profile-modal')" class="w-full text-left px-3 py-2 text-xs text-gray-300 hover:bg-[#23263A] hover:text-white transition-colors cursor-pointer">
                                ✏️ Edit Profil
                            </button>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 text-xs text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors">
                                    🚪 Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-1.5 text-xs font-bold bg-[#C3F53C] text-[#0B0C14] rounded-lg hover:brightness-110 transition">
                        LOGIN
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Flash Notifications / Toasts --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-cloak
             class="fixed top-18 right-6 z-50 flex items-center gap-3 bg-[#141625] border-l-4 border-[#C3F53C] text-white px-4 py-3 rounded-lg shadow-2xl transition">
            <span class="text-[#C3F53C] text-lg">✓</span>
            <span class="text-xs font-semibold">{{ session('success') }}</span>
            <button @click="show = false" class="text-gray-400 hover:text-white text-sm ml-2">&times;</button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-cloak
             class="fixed top-18 right-6 z-50 flex items-center gap-3 bg-[#141625] border-l-4 border-red-500 text-white px-4 py-3 rounded-lg shadow-2xl transition">
            <span class="text-red-500 text-lg">✕</span>
            <div class="text-xs font-semibold">
                {{ session('error') ?? $errors->first() }}
            </div>
            <button @click="show = false" class="text-gray-400 hover:text-white text-sm ml-2">&times;</button>
        </div>
    @endif

    @if(session('info'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-cloak
             class="fixed top-18 right-6 z-50 flex items-center gap-3 bg-[#141625] border-l-4 border-blue-500 text-white px-4 py-3 rounded-lg shadow-2xl transition">
            <span class="text-blue-400 text-lg">ℹ</span>
            <span class="text-xs font-semibold">{{ session('info') }}</span>
            <button @click="show = false" class="text-gray-400 hover:text-white text-sm ml-2">&times;</button>
        </div>
    @endif

    {{-- Main Page Content --}}
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 lg:px-8 py-6">
        @yield('content')
    </main>

    {{-- Dark Arcade Status Footer --}}
    <footer class="bg-[#08090E] border-t border-[#1C1F2E] px-4 lg:px-8 py-4 text-[11px] text-gray-500 font-mono mt-auto">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="font-bold text-gray-300">CORET! CHAOS</span>
                <span class="text-gray-600">v1.4.0-PROD</span>
                <span class="px-2 py-0.5 rounded border border-[#C3F53C]/40 text-[#C3F53C] text-[10px] font-bold bg-[#C3F53C]/5">
                    ARCADE ENGINE READY
                </span>
            </div>
            <div class="flex items-center gap-4 text-gray-500">
                <span>SERVER STATUS: <strong class="text-emerald-400">OPTIMAL</strong></span>
                <span class="hidden md:inline">•</span>
                <span class="hidden md:inline">CHAOS SEED: <strong class="text-purple-400">#88219</strong></span>
                <span class="hidden md:inline">•</span>
                <span>&copy; {{ date('Y') }} INDIE MAYHEM LABS</span>
            </div>
        </div>
    </footer>

    <x-profile-modal />

    @stack('scripts')
</body>
</html>
