<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CORET! CHAOS Gambar. Tebak. Rusuh.</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&family=Fraunces:ital,opsz,wght@0,9..144,700;0,9..144,900;1,9..144,900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-[#111124] text-[#E2E0FB] font-sans antialiased selection:bg-[#BDF454] selection:text-[#111124] overflow-x-hidden relative flex flex-col">

    {{-- Subtle Ambient Radial Glows --}}
    <div class="pointer-events-none fixed -top-44 -left-44 w-[520px] h-[520px] rounded-full bg-[#5717BC]/20 blur-[140px] z-0"></div>
    <div class="pointer-events-none fixed -bottom-44 -right-44 w-[520px] h-[520px] rounded-full bg-[#BDF454]/10 blur-[150px] z-0"></div>

    {{-- Minimal Top Navbar --}}
    <header class="sticky top-0 z-40 w-full border-b border-[#333347]/70 bg-[#111124]/85 backdrop-blur-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-[4.5rem] flex items-center justify-between gap-4">
            {{-- Left: Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group shrink-0">
                <span class="w-9 h-9 rounded-xl bg-[#1A1A2D] border border-[#333347] group-hover:border-[#BDF454] flex items-center justify-center transition">
                    <svg viewBox="0 0 36 36" class="w-5 h-5" aria-hidden="true">
                        <path d="M8 15L6 6l8 5c2.5-.8 5.5-.8 8 0l8-5-2 9c2 3 2.5 8 0 11.5C25.5 30 21 31 18 31s-7.5-1-10-4.5C5.5 23 6 18 8 15z" fill="#5717BC" stroke="#BDF454" stroke-width="2"/>
                        <circle cx="13.5" cy="19" r="2.2" fill="#BDF454"/>
                        <circle cx="22.5" cy="19" r="2.2" fill="#BDF454"/>
                        <path d="M15 24c1.8 1.5 4.2 1.5 6 0" fill="none" stroke="#E2E0FB" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="font-serif font-black tracking-wider text-base sm:text-lg text-[#E2E0FB]">
                    CORET! <span class="text-[#BDF454]">CHAOS</span>
                </span>
            </a>

            {{-- Right: Auth or Profile Controls --}}
            <div class="flex items-center gap-2.5 sm:gap-3">
                @auth
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-profile-modal')"
                        class="flex items-center gap-2.5 rounded-full bg-[#1A1A2D] hover:bg-[#28283C] border border-[#333347] hover:border-[#BDF454]/60 pl-1.5 pr-3 py-1 transition cursor-pointer"
                        title="Ganti Foto & Nama Profil"
                    >
                        <x-player-avatar :avatar="Auth::user()->avatar" size="xs" glow="none" class="rounded-full" />
                        <span class="text-xs font-bold text-[#E2E0FB] max-w-[120px] truncate">{{ Auth::user()->nickname }}</span>
                        <span class="text-[11px] text-[#BDF454]" aria-hidden="true">✏️</span>
                    </button>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="px-3.5 py-2 rounded-xl bg-[#1A1A2D] hover:bg-[#28283C] border border-[#333347] text-xs font-bold text-[#C3C9B1] hover:text-white transition cursor-pointer"
                        >
                            Logout
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-[#C3C9B1] hover:text-[#E2E0FB] transition"
                    >
                        Login
                    </a>
                    <a
                        href="{{ route('register') }}"
                        class="px-4 sm:px-5 py-2 rounded-xl bg-[#BDF454] hover:brightness-110 text-[#111124] text-xs sm:text-sm font-extrabold transition"
                    >
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Flash Toast --}}
    @if (session('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            x-cloak
            class="fixed top-20 right-4 sm:right-6 z-50 flex items-center gap-3 bg-[#1A1A2D] border-l-4 border-[#BDF454] text-[#E2E0FB] px-4 py-3 rounded-xl shadow-2xl"
        >
            <span class="text-[#BDF454] font-bold">✓</span>
            <span class="text-xs font-semibold">{{ session('success') }}</span>
            <button type="button" @click="show = false" class="text-[#C3C9B1] hover:text-white text-sm ml-1 cursor-pointer">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)"
            x-cloak
            class="fixed top-20 right-4 sm:right-6 z-50 flex items-center gap-3 bg-[#1A1A2D] border-l-4 border-rose-500 text-[#E2E0FB] px-4 py-3 rounded-xl shadow-2xl"
        >
            <span class="text-rose-400 font-bold">✕</span>
            <span class="text-xs font-semibold">{{ session('error') }}</span>
            <button type="button" @click="show = false" class="text-[#C3C9B1] hover:text-white text-sm ml-1 cursor-pointer">&times;</button>
        </div>
    @endif

    <main class="relative z-10 flex-1">
        {{-- =============================================================== --}}
        {{-- SECTION 1: SIMPLE HERO                                          --}}
        {{-- =============================================================== --}}
        <section class="min-h-[calc(100vh-4.5rem)] flex flex-col justify-between px-4 sm:px-6 py-10 max-w-5xl mx-auto">
            <div class="my-auto py-8 flex flex-col items-center text-center relative">
                {{-- Floating Tilted Mini SABOTEUR Card --}}
                <div class="hidden sm:flex flex-col items-center justify-between w-24 rounded-2xl bg-[#1A1A2D] border border-[#333347] p-2.5 shadow-2xl animate-float-card absolute -top-6 right-4 md:right-14">
                    <div class="w-11 h-11 rounded-xl bg-[#0C0C1F] border border-[#333347] flex items-center justify-center">
                        <svg viewBox="0 0 48 48" class="w-9 h-9" aria-hidden="true">
                            <path d="M12 18l-3-11 10 6c3-.9 7-.9 10 0l10-6-3 11c3 4 3.5 10.5 0 15-3 4-7.5 6-12 6s-9-2-12-6c-3.5-4.5-3-11 0-15z" fill="#5717BC" stroke="#BDF454" stroke-width="2.5"/>
                            <circle cx="18.5" cy="24" r="2.8" fill="#BDF454"/>
                            <circle cx="29.5" cy="24" r="2.8" fill="#BDF454"/>
                            <path d="M20 31c2.5 2 5.5 2 8 0" fill="none" stroke="#E2E0FB" stroke-width="2.4" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="mt-2 px-2 py-0.5 rounded-md bg-[#E11D48] text-[9px] font-extrabold tracking-widest uppercase text-white">
                        SABOTEUR
                    </span>
                </div>


                {{-- Display Serif Headline --}}
                <h1 class="font-serif font-black text-5xl sm:text-6xl md:text-7xl tracking-tight leading-[1.06] text-[#E2E0FB]">
                    <span class="block">GAMBAR. TEBAK.</span>
                    <span class="mt-2 inline-flex items-center justify-center gap-4 sm:gap-6">
                        <span class="italic text-[#BDF454]">RUSUH.</span>
                    </span>
                </h1>

                {{-- Concise 2-Line Subtitle --}}
                <p class="mt-5 text-sm sm:text-base text-[#C3C9B1] max-w-lg leading-relaxed">
                    Game gambar bareng teman, tapi penuh sabotase kartu kutukan acak yang bikin emosi membara.
                </p>

                {{-- 2 Main CTA Buttons Side-by-Side --}}
                <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3 w-full max-w-md">
                    <a
                        href="{{ auth()->check() ? route('lobby.hub') : route('login') }}"
                        class="flex-1 py-3.5 px-6 rounded-2xl bg-[#BDF454] hover:brightness-110 text-[#111124] font-extrabold text-sm tracking-wider uppercase flex items-center justify-center gap-2 shadow-[0_12px_30px_rgba(189,244,84,0.22)] transition"
                    >
                        <span>⊕</span>
                        <span>BUAT ROOM</span>
                    </a>
                    <button
                        type="button"
                        onclick="const input = document.getElementById('hero-room-code'); input?.focus(); input?.select();"
                        class="flex-1 py-3.5 px-6 rounded-2xl bg-[#D1BCFF] hover:brightness-105 text-[#111124] font-extrabold text-sm tracking-wider uppercase flex items-center justify-center gap-2 transition cursor-pointer"
                    >
                        <span>→</span>
                        <span>GABUNG</span>
                    </button>
                </div>

                {{-- Sleek 4-Char Room Code Input --}}
                @auth
                    <form
                        method="POST"
                        action="{{ route('lobby.join') }}"
                        class="mt-4 w-full max-w-sm rounded-full bg-[#1A1A2D] border border-[#333347] focus-within:border-[#BDF454] p-1.5 pl-4 flex items-center gap-2 transition"
                    >
                        @csrf
                        <span class="text-sm text-[#8D937D]" aria-hidden="true">🔑</span>
                        <input
                            id="hero-room-code"
                            type="text"
                            name="code"
                            maxlength="4"
                            required
                            oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                            placeholder="KODE ROOM"
                            aria-label="Kode Room 4 huruf"
                            class="flex-1 min-w-0 bg-transparent text-xs sm:text-sm font-extrabold uppercase tracking-[0.22em] text-[#E2E0FB] placeholder-[#8D937D] focus:outline-none"
                        >
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-full bg-[#28283C] hover:bg-[#333347] text-xs font-extrabold tracking-wider uppercase text-[#E2E0FB] transition cursor-pointer shrink-0"
                        >
                            MASUK →
                        </button>
                    </form>

                    {{-- Compact Quick Profile & Lobby Link for Authenticated User --}}
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-xs text-[#C3C9B1]">
                        <button
                            type="button"
                            x-data
                            @click="$dispatch('open-profile-modal')"
                            class="hover:text-[#BDF454] underline underline-offset-4 transition cursor-pointer"
                        >
                            Ganti Foto &amp; Nama
                        </button>
                        <span class="text-[#333347]" aria-hidden="true">•</span>
                        <a href="{{ route('lobby.hub') }}" class="hover:text-[#BDF454] underline underline-offset-4 transition">
                            Buka Lobby Hub
                        </a>
                    </div>
                @else
                    <form
                        onsubmit="event.preventDefault(); const code = (document.getElementById('hero-room-code')?.value || '').trim().toUpperCase(); if (code.length >= 4) { window.location.href = '/join/' + encodeURIComponent(code.slice(0, 4)); } else { document.getElementById('hero-room-code')?.focus(); }"
                        class="mt-4 w-full max-w-sm rounded-full bg-[#1A1A2D] border border-[#333347] focus-within:border-[#BDF454] p-1.5 pl-4 flex items-center gap-2 transition"
                    >
                        <span class="text-sm text-[#8D937D]" aria-hidden="true">🔑</span>
                        <input
                            id="hero-room-code"
                            type="text"
                            maxlength="4"
                            required
                            oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                            placeholder="KODE ROOM"
                            aria-label="Kode Room 4 huruf"
                            class="flex-1 min-w-0 bg-transparent text-xs sm:text-sm font-extrabold uppercase tracking-[0.22em] text-[#E2E0FB] placeholder-[#8D937D] focus:outline-none"
                        >
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-full bg-[#28283C] hover:bg-[#333347] text-xs font-extrabold tracking-wider uppercase text-[#E2E0FB] transition cursor-pointer shrink-0"
                        >
                            MASUK →
                        </button>
                    </form>
                @endauth
            </div>

            {{-- Subtle Scroll Cue --}}
            <div class="flex justify-center pt-4">
                <a href="#tentang" class="text-xs font-semibold text-[#8D937D] hover:text-[#BDF454] transition">
                    Pelajari cara main ↓
                </a>
            </div>
        </section>

        {{-- =============================================================== --}}
        {{-- SECTION 2: TENTANG WEBSITE INI                                  --}}
        {{-- =============================================================== --}}
        <section id="tentang" class="py-20 px-4 sm:px-6 max-w-5xl mx-auto border-t border-[#333347]/60">
            <div class="max-w-2xl">
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#BDF454]">Tentang Game</span>
                <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-black text-[#E2E0FB]">
                    Apa itu CORET! CHAOS?
                </h2>
                <p class="mt-3 text-sm sm:text-base text-[#C3C9B1] leading-relaxed">
                    Satu pemain bertugas menggambar kata rahasia secara langsung di kanvas, sementara pemain lain berlomba menebaknya. Bedanya, para penebak bisa menembakkan kutukan real-time untuk mengacaukan kuas dan layar si pelukis.
                </p>
            </div>

            <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="rounded-2xl bg-[#1A1A2D] border border-[#333347] p-6">
                    <div class="w-10 h-10 rounded-xl bg-[#28283C] flex items-center justify-center text-lg mb-4">🎨</div>
                    <h3 class="text-base font-bold text-[#E2E0FB]">Giliran Adil (Pelukis vs Saboteur)</h3>
                    <p class="mt-2 text-xs sm:text-sm text-[#C3C9B1] leading-relaxed">
                        Semua pemain di room bergantian menjadi Pelukis dan Penebak/Penyabotase di setiap ronde.
                    </p>
                </div>

                <div class="rounded-2xl bg-[#1A1A2D] border border-[#333347] p-6">
                    <div class="w-10 h-10 rounded-xl bg-[#28283C] flex items-center justify-center text-lg mb-4">🧠</div>
                    <h3 class="text-base font-bold text-[#E2E0FB]">Tebakan Anti-Typo</h3>
                    <p class="mt-2 text-xs sm:text-sm text-[#C3C9B1] leading-relaxed">
                        Sistem fuzzy matching memberi petunjuk privat <em>"Dikit lagi!"</em> saat tebakan hampir tepat tanpa membocorkan jawaban ke pemain lain.
                    </p>
                </div>

                <div class="rounded-2xl bg-[#1A1A2D] border border-[#333347] p-6">
                    <div class="w-10 h-10 rounded-xl bg-[#28283C] flex items-center justify-center text-lg mb-4">🖼️</div>
                    <h3 class="text-base font-bold text-[#E2E0FB]">Hall of Shame</h3>
                    <p class="mt-2 text-xs sm:text-sm text-[#C3C9B1] leading-relaxed">
                        Hasil lukisan rusuh di akhir ronde otomatis dibingkai dengan judul sarkastik dan dapat diunduh untuk dibagikan.
                    </p>
                </div>
            </div>
        </section>

        {{-- =============================================================== --}}
        {{-- SECTION 3: CARA BERMAIN                                         --}}
        {{-- =============================================================== --}}
        <section id="cara-main" class="py-20 px-4 sm:px-6 max-w-5xl mx-auto border-t border-[#333347]/60">
            <div class="max-w-2xl">
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#D1BCFF]">Panduan Singkat</span>
                <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-black text-[#E2E0FB]">
                    Cara Bermain
                </h2>
                <p class="mt-3 text-sm sm:text-base text-[#C3C9B1]">
                    Cukup 4 langkah cepat untuk mulai menggambar dan mengacau bareng teman.
                </p>
            </div>

            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="rounded-2xl bg-[#1A1A2D] border border-[#333347] p-6">
                    <span class="text-xs font-extrabold tracking-widest text-[#BDF454]">01</span>
                    <h3 class="mt-3 text-base font-bold text-[#E2E0FB]">Login / Daftar &amp; Atur Profil</h3>
                    <p class="mt-2 text-xs sm:text-sm text-[#C3C9B1] leading-relaxed">
                        Daftar instan tanpa ribet verifikasi email, lalu pilih avatar dan nama panggilanmu.
                    </p>
                </div>

                <div class="rounded-2xl bg-[#1A1A2D] border border-[#333347] p-6">
                    <span class="text-xs font-extrabold tracking-widest text-[#BDF454]">02</span>
                    <h3 class="mt-3 text-base font-bold text-[#E2E0FB]">Buat atau Gabung Room</h3>
                    <p class="mt-2 text-xs sm:text-sm text-[#C3C9B1] leading-relaxed">
                        Buat room privat untuk mendapatkan kode 4 huruf, atau masukkan kode milik temanmu.
                    </p>
                </div>

                <div class="rounded-2xl bg-[#1A1A2D] border border-[#333347] p-6">
                    <span class="text-xs font-extrabold tracking-widest text-[#BDF454]">03</span>
                    <h3 class="mt-3 text-base font-bold text-[#E2E0FB]">Gambar, Tebak &amp; Sabotase</h3>
                    <p class="mt-2 text-xs sm:text-sm text-[#C3C9B1] leading-relaxed">
                        Pelukis menggambar kata rahasia. Penebak mengetik tebakan secepat mungkin sambil melempar kutukan.
                    </p>
                </div>

                <div class="rounded-2xl bg-[#1A1A2D] border border-[#333347] p-6">
                    <span class="text-xs font-extrabold tracking-widest text-[#BDF454]">04</span>
                    <h3 class="mt-3 text-base font-bold text-[#E2E0FB]">Poin &amp; Replay Ronde</h3>
                    <p class="mt-2 text-xs sm:text-sm text-[#C3C9B1] leading-relaxed">
                        Penebak tercepat meraih poin tertinggi, dan pelukis tetap mendapat bonus bila gambarnya berhasil ditebak.
                    </p>
                </div>
            </div>
        </section>

        {{-- =============================================================== --}}
        {{-- SECTION 4: SKILL SABOTASE PENEBak                               --}}
        {{-- =============================================================== --}}
        <section id="skill" class="py-20 px-4 sm:px-6 max-w-5xl mx-auto border-t border-[#333347]/60">
            <div class="max-w-2xl">
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#BDF454]">The Saboteur Deck</span>
                <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-black text-[#E2E0FB]">
                    Skill Sabotase Penebak
                </h2>
                <p class="mt-3 text-sm sm:text-base text-[#C3C9B1]">
                    Selama ronde berjalan, penebak mengumpulkan <strong class="text-[#E2E0FB]">Chaos Energy</strong> yang terisi otomatis untuk menembakkan salah satu kutukan di bawah ini ke layar pelukis.
                </p>
            </div>

            <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach ($curses as $curse)
                    <div class="rounded-2xl bg-[#1A1A2D] border border-[#333347] hover:border-[#BDF454]/40 p-6 flex flex-col justify-between gap-4 transition">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-[#0C0C1F] border border-[#333347] flex items-center justify-center text-2xl shrink-0">
                                {{ $curse['icon'] }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-[#E2E0FB]">{{ $curse['name'] }}</h3>
                                <p class="mt-1.5 text-xs sm:text-sm text-[#C3C9B1] leading-relaxed">
                                    {{ $curse['description'] }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-[#333347]/60">
                            <span class="px-3 py-1 rounded-full bg-[#5717BC]/25 border border-[#D1BCFF]/30 text-[11px] font-bold text-[#D1BCFF]">
                                ⚡ {{ $curse['energy_cost'] }} Energy
                            </span>
                            <span class="px-3 py-1 rounded-full bg-[#28283C] border border-[#333347] text-[11px] font-bold text-[#BDF454]">
                                ⏱ {{ $curse['duration_seconds'] }} detik
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- =============================================================== --}}
        {{-- SECTION 5: BOTTOM CTA                                           --}}
        {{-- =============================================================== --}}
        <section class="py-16 px-4 sm:px-6 max-w-5xl mx-auto">
            <div class="rounded-3xl bg-[#1A1A2D] border border-[#333347] p-8 sm:p-12 text-center">
                <h2 class="font-serif text-3xl sm:text-4xl font-black text-[#E2E0FB]">
                    Siap Bikin Teman Panik?
                </h2>
                <p class="mt-3 text-sm sm:text-base text-[#C3C9B1] max-w-md mx-auto">
                    Masuk ke lobby sekarang, atur profilmu, dan mulai ronde gambar paling rusuh.
                </p>

                <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                    @auth
                        <a
                            href="{{ route('lobby.hub') }}"
                            class="px-7 py-3.5 rounded-2xl bg-[#BDF454] hover:brightness-110 text-[#111124] font-extrabold text-sm uppercase tracking-wider transition"
                        >
                            Masuk ke Lobby
                        </a>
                        <button
                            type="button"
                            x-data
                            @click="$dispatch('open-profile-modal')"
                            class="px-6 py-3.5 rounded-2xl bg-[#28283C] hover:bg-[#333347] border border-[#333347] text-[#E2E0FB] font-bold text-sm transition cursor-pointer"
                        >
                            Edit Profil
                        </button>
                    @else
                        <a
                            href="{{ route('register') }}"
                            class="px-7 py-3.5 rounded-2xl bg-[#BDF454] hover:brightness-110 text-[#111124] font-extrabold text-sm uppercase tracking-wider transition"
                        >
                            Daftar &amp; Main Sekarang
                        </a>
                        <a
                            href="{{ route('login') }}"
                            class="px-6 py-3.5 rounded-2xl bg-[#28283C] hover:bg-[#333347] border border-[#333347] text-[#E2E0FB] font-bold text-sm transition"
                        >
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </section>
    </main>

    {{-- Minimal 1-Row Footer --}}
    <footer class="relative z-10 border-t border-[#333347]/60 py-6 px-4 sm:px-6">
        <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[#8D937D]">
            <span class="font-bold text-[#E2E0FB]">CORET! CHAOS</span>
            <span>Browser-ready · Tanpa Download</span>
        </div>
    </footer>

    <x-profile-modal />
</body>
</html>
