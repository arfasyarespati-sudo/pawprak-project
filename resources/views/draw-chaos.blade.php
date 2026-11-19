<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CORET! CHAOS — Real-Time Multiplayer Drawing & Sabotage</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700;9..144,900&family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="/socket.io/socket.io.js" onerror="this.onerror=null;"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#111124] text-[#E2E0FB] min-h-screen flex flex-col font-sans antialiased selection:bg-[#BDF454] selection:text-[#14200A] overflow-x-hidden relative">

    <!-- Ambient Radial Glows (Figma Arena Backdrop) -->
    <div class="pointer-events-none fixed -top-40 -left-40 w-[560px] h-[560px] rounded-full bg-[#5717BC]/22 blur-[140px] z-0"></div>
    <div class="pointer-events-none fixed -bottom-44 -right-40 w-[560px] h-[560px] rounded-full bg-[#BDF454]/12 blur-[150px] z-0"></div>

    <!-- ===================================================================== -->
    <!-- TOP GLOBAL NAVBAR                                                     -->
    <!-- ===================================================================== -->
    <header class="sticky top-0 z-30 w-full px-4 sm:px-6 py-3.5 flex items-center justify-between gap-3 border-b border-[#333347]/80 bg-[#111124]/90 backdrop-blur-md">
        <!-- Brand Logo -->
        <button type="button" data-nav-view="home" class="flex items-center gap-2.5 text-left group cursor-pointer shrink-0">
            <span class="w-9 h-9 rounded-xl bg-[#1A1A2D] border border-[#333347] group-hover:border-[#BDF454] flex items-center justify-center transition shadow-md">
                <svg viewBox="0 0 36 36" class="w-6 h-6" aria-hidden="true">
                    <path d="M8 15L6 6l8 5c2.5-.8 5.5-.8 8 0l8-5-2 9c2 3 2.5 8 0 11.5C25.5 30 21 31 18 31s-7.5-1-10-4.5C5.5 23 6 18 8 15z" fill="#5717BC" stroke="#BDF454" stroke-width="2"/>
                    <circle cx="13.5" cy="19" r="2.2" fill="#BDF454"/>
                    <circle cx="22.5" cy="19" r="2.2" fill="#BDF454"/>
                    <path d="M15 24c1.8 1.5 4.2 1.5 6 0" fill="none" stroke="#E2E0FB" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="font-serif font-black tracking-wider text-base sm:text-lg text-[#E2E0FB]">
                CORET! <span class="text-[#BDF454]">CHAOS</span>
            </span>
        </button>

        <!-- Center Pill Switcher -->
        <nav class="hidden lg:flex items-center gap-1 bg-[#1A1A2D] border border-[#333347] rounded-full p-1 text-xs font-bold">
            <button type="button" data-nav-view="home" class="px-3.5 py-1.5 rounded-full bg-[#28283C] text-[#E2E0FB] shadow-sm transition cursor-pointer">Home</button>
            <button type="button" data-nav-view="hub" class="px-3.5 py-1.5 rounded-full text-[#C3C9B1] hover:text-[#E2E0FB] transition cursor-pointer">Hub Lobby</button>
            <button type="button" data-nav-view="lobby" class="px-3.5 py-1.5 rounded-full text-[#C3C9B1] hover:text-[#E2E0FB] transition cursor-pointer">Lobby</button>
            <button type="button" data-nav-view="game" class="px-3.5 py-1.5 rounded-full text-[#C3C9B1] hover:text-[#E2E0FB] transition cursor-pointer">Game</button>
            <button type="button" data-nav-view="results" class="px-3.5 py-1.5 rounded-full text-[#C3C9B1] hover:text-[#E2E0FB] transition cursor-pointer">Results</button>
            <button type="button" data-nav-view="museum" class="px-3.5 py-1.5 rounded-full text-[#C3C9B1] hover:text-[#E2E0FB] transition cursor-pointer">Museum</button>
        </nav>

        <!-- Right Controls: Live Ping, Sound, Fullscreen & Auth/Profile Pill -->
        <div class="flex items-center gap-2 shrink-0">
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#1A1A2D] border border-[#333347] text-[11px] font-bold tracking-wider uppercase text-[#E2E0FB]">
                <span id="nav-connection-dot" class="w-2 h-2 rounded-full bg-[#BDF454] animate-pulse"></span>
                <span id="nav-ping-badge">-- ms</span>
            </div>
            <button id="btn-toggle-sound" type="button" class="w-8 h-8 rounded-full bg-[#1A1A2D] border border-[#333347] flex items-center justify-center text-xs text-[#C3C9B1] hover:text-[#E2E0FB] hover:border-[#BDF454]/60 transition cursor-pointer" title="Toggle Sound FX">
                🔊
            </button>
            <button id="btn-toggle-fullscreen" type="button" class="hidden sm:flex w-8 h-8 rounded-full bg-[#1A1A2D] border border-[#333347] items-center justify-center text-xs text-[#C3C9B1] hover:text-[#E2E0FB] hover:border-[#BDF454]/60 transition cursor-pointer" title="Fullscreen">
                ⛶
            </button>

            <!-- Quick Auth Trigger (Visible for Guest; Hidden when Logged In) -->
            <button id="btn-nav-open-login" type="button" data-nav-view="login" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#5717BC]/25 hover:bg-[#5717BC]/45 border border-[#D1BCFF]/35 text-[11px] font-extrabold text-[#D1BCFF] transition cursor-pointer">
                Masuk
            </button>

            <!-- Active Player / Profile Pill -->
            <button id="btn-open-profile-modal" type="button" class="flex items-center gap-2 pl-1.5 pr-3 py-1 rounded-full bg-[#1A1A2D] hover:bg-[#28283C] border border-[#333347] hover:border-[#BDF454]/60 transition cursor-pointer" title="Profil Seniman & Akun">
                <span id="nav-user-avatar-slot" class="w-6 h-6 rounded-full bg-[#0C0C1F] border border-[#333347] flex items-center justify-center overflow-hidden"></span>
                <span id="nav-user-nickname" class="text-xs font-bold text-[#E2E0FB] max-w-[95px] truncate">{{ $guestPlayer['nickname'] ?? 'SenimanRusuh_89' }}</span>
                <span id="nav-user-status-dot" class="w-1.5 h-1.5 rounded-full bg-[#D1BCFF]"></span>
            </button>
        </div>
    </header>

    <!-- Mobile View Switcher Bar -->
    <div class="flex lg:hidden items-center justify-start sm:justify-center gap-1.5 px-4 py-2 bg-[#0C0C1F] border-b border-[#333347]/70 overflow-x-auto z-20 text-xs font-bold">
        <button type="button" data-nav-view="home" class="px-3 py-1 rounded-full bg-[#28283C] text-[#E2E0FB] shrink-0">Home</button>
        <button type="button" data-nav-view="hub" class="px-3 py-1 rounded-full text-[#C3C9B1] shrink-0">Hub Lobby</button>
        <button type="button" data-nav-view="lobby" class="px-3 py-1 rounded-full text-[#C3C9B1] shrink-0">Lobby</button>
        <button type="button" data-nav-view="game" class="px-3 py-1 rounded-full text-[#C3C9B1] shrink-0">Game</button>
        <button type="button" data-nav-view="results" class="px-3 py-1 rounded-full text-[#C3C9B1] shrink-0">Results</button>
        <button type="button" data-nav-view="museum" class="px-3 py-1 rounded-full text-[#C3C9B1] shrink-0">Museum</button>
    </div>

    <!-- ===================================================================== -->
    <!-- SCREEN 1: LANDING PAGE (FIGMA 03_LANDING_PAGE REDESIGN)               -->
    <!-- ===================================================================== -->
    <section data-screen-section="home" class="relative z-10 flex-1 flex flex-col justify-between px-4 sm:px-6 py-6 max-w-6xl mx-auto w-full">
        <!-- Real-Time Online Counter Pill -->
        <div class="flex justify-center mt-1">
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-[#1A1A2D] border border-[#333347] text-[11px] font-extrabold tracking-widest uppercase text-[#C3C9B1] shadow-lg">
                <span class="w-2.5 h-2.5 rounded-full bg-[#BDF454] animate-pulse shadow-[0_0_10px_#BDF454]"></span>
                <span id="online-players-counter">{{ $onlineCount ?? 1 }} SENIMAN SEDANG ONLINE</span>
            </div>
        </div>

        <!-- Center Hero Block -->
        <div class="relative my-auto py-8 sm:py-10 flex flex-col items-center text-center max-w-3xl mx-auto w-full">

            <!-- Floating Left Artist Brush Pill -->
            <div class="hidden md:flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-[#1A1A2D] border border-[#333347] shadow-2xl absolute -top-2 left-2 -rotate-6">
                <span class="w-7 h-7 rounded-xl bg-[#BDF454]/15 text-[#BDF454] flex items-center justify-center text-sm font-black">🎨</span>
                <div class="text-left">
                    <div class="text-[10px] font-extrabold uppercase tracking-widest text-[#BDF454]">60FPS SYNC</div>
                    <div class="text-[11px] font-bold text-[#E2E0FB]">Bezier Kanvas</div>
                </div>
            </div>

            <!-- Floating Right Tilted SABOTEUR Card -->
            <div class="hidden sm:flex flex-col items-center justify-between w-28 rounded-2xl bg-[#1A1A2D] border border-[#333347] p-3 shadow-2xl animate-float-card absolute -top-4 right-2 md:right-6">
                <div class="w-12 h-12 rounded-xl bg-[#0C0C1F] border border-[#333347] flex items-center justify-center overflow-hidden">
                    <svg viewBox="0 0 48 48" class="w-10 h-10" aria-hidden="true">
                        <path d="M12 18l-3-11 10 6c3-.9 7-.9 10 0l10-6-3 11c3 4 3.5 10.5 0 15-3 4-7.5 6-12 6s-9-2-12-6c-3.5-4.5-3-11 0-15z" fill="#5717BC" stroke="#BDF454" stroke-width="2.5"/>
                        <circle cx="18.5" cy="24" r="2.8" fill="#BDF454"/>
                        <circle cx="29.5" cy="24" r="2.8" fill="#BDF454"/>
                        <path d="M20 31c2.5 2 5.5 2 8 0" fill="none" stroke="#E2E0FB" stroke-width="2.4" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="mt-2 px-2.5 py-0.5 rounded-md bg-[#E11D48] text-[9px] font-extrabold tracking-widest uppercase text-white shadow">SABOTEUR</span>
            </div>

            <!-- Party Drawing Mayhem Tag -->
            <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-[#1A1A2D] border border-[#333347] text-[11px] font-extrabold tracking-widest uppercase text-[#BDF454] mb-5">
                <span>⚡</span>
                <span>PARTY DRAWING MAYHEM</span>
            </div>

            <!-- Main Display Serif Headline -->
            <h1 class="font-serif font-black text-5xl sm:text-6xl md:text-7xl tracking-tight leading-[1.05] text-[#E2E0FB]">
                GAMBAR. TEBAK.
                <span class="block mt-1 italic text-[#BDF454] relative inline-block">
                    RUSUH.
                    <svg class="w-52 sm:w-72 h-4 -mt-0.5 mx-auto text-[#D1BCFF]" viewBox="0 0 240 16" fill="none" aria-hidden="true">
                        <path d="M4 11.5C68 4.5 154 4.5 236 10" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
                    </svg>
                </span>
            </h1>

            <p class="mt-4 text-sm sm:text-base text-[#C3C9B1] max-w-xl leading-relaxed">
                Pelukis menggambar kata rahasia secara <span class="text-[#E2E0FB] font-semibold">real-time 60FPS</span>, sementara para penebak berlomba menebak sambil melempar kutukan gempa, kuas mabuk, dan lampu mati!
            </p>

            <!-- Nickname Quick Bar -->
            <div class="mt-7 w-full max-w-md bg-[#1A1A2D] border border-[#333347] focus-within:border-[#BDF454] rounded-2xl p-2 flex items-center gap-2 shadow-xl transition">
                <span id="landing-avatar-preview" class="w-9 h-9 rounded-xl bg-[#0C0C1F] border border-[#333347] flex items-center justify-center shrink-0 overflow-hidden"></span>
                <input
                    id="input-landing-nickname"
                    type="text"
                    maxlength="32"
                    value="{{ $guestPlayer['nickname'] ?? 'SenimanRusuh_89' }}"
                    placeholder="Masukkan Nickname..."
                    aria-label="Nickname pemain"
                    class="flex-1 bg-transparent px-2 py-1.5 text-sm font-bold text-[#E2E0FB] placeholder-[#8D937D] focus:outline-none min-w-0"
                >
                <button id="btn-randomize-nickname" type="button" class="px-3 py-2 rounded-xl bg-[#28283C] hover:bg-[#333347] text-xs font-bold text-[#D1BCFF] transition cursor-pointer shrink-0" title="Acak Nickname">
                    🎲 Acak
                </button>
            </div>

            <!-- Primary CTA Triad -->
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3 w-full max-w-xl">
                <button id="btn-landing-quick-play" type="button" class="py-4 px-5 rounded-2xl bg-[#BDF454] hover:bg-[#ccf970] text-[#14200A] font-extrabold text-xs sm:text-sm tracking-wider uppercase shadow-[0_12px_32px_rgba(189,244,84,0.28)] flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5 cursor-pointer">
                    <span>⚡</span>
                    <span>QUICK PLAY</span>
                </button>
                <button id="btn-landing-create-room" type="button" class="py-4 px-5 rounded-2xl bg-[#28283C] hover:bg-[#333347] border border-[#3E3E58] text-[#E2E0FB] font-extrabold text-xs sm:text-sm tracking-wider uppercase flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5 cursor-pointer">
                    <span>⊕</span>
                    <span>BUAT ROOM</span>
                </button>
                <button id="btn-landing-join-focus" type="button" class="py-4 px-5 rounded-2xl bg-[#5717BC] hover:bg-[#6B25DC] border border-[#D1BCFF]/30 text-white font-extrabold text-xs sm:text-sm tracking-wider uppercase shadow-[0_12px_28px_rgba(87,23,188,0.35)] flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5 cursor-pointer">
                    <span>➔</span>
                    <span>GABUNG</span>
                </button>
            </div>

            <!-- Inline 4-Char Quick Join Room Form -->
            <form id="form-landing-join-room" class="mt-4 w-full max-w-md bg-[#0C0C1F] border border-[#333347] rounded-2xl p-2 flex items-center justify-between gap-2 shadow-inner">
                <input
                    id="input-landing-room-code"
                    type="text"
                    maxlength="4"
                    placeholder="KODE ROOM (CONTOH: CHAO)"
                    aria-label="Kode Room 4 huruf"
                    class="flex-1 bg-transparent px-3 py-2 text-xs sm:text-sm font-extrabold uppercase tracking-[0.22em] text-[#BDF454] placeholder-[#8D937D] focus:outline-none min-w-0"
                >
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#D1BCFF] hover:bg-[#dfd0ff] text-[#14200A] font-extrabold text-xs tracking-wider uppercase transition cursor-pointer shrink-0">
                    Masuk Room
                </button>
            </form>

            <!-- Secondary Discovery Links: Hub Lobby & Akun -->
            <div class="mt-5 flex flex-wrap items-center justify-center gap-2.5 text-xs font-bold">
                <button type="button" data-nav-view="hub" class="px-3.5 py-2 rounded-xl bg-[#1A1A2D] hover:bg-[#28283C] border border-[#333347] text-[#C3C9B1] hover:text-[#BDF454] transition cursor-pointer">
                    🏟️ Jelajahi Hub &amp; Lobby Publik
                </button>
                <button type="button" data-nav-view="login" id="btn-landing-auth-link" class="px-3.5 py-2 rounded-xl bg-[#1A1A2D] hover:bg-[#28283C] border border-[#333347] text-[#D1BCFF] hover:text-white transition cursor-pointer">
                    🔐 Masuk / Daftar Akun Seniman
                </button>
            </div>
        </div>

        <!-- Bottom Status Footer -->
        <footer class="pt-4 border-t border-[#333347]/70 flex flex-wrap items-center justify-between gap-3 text-[11px] font-medium text-[#8D937D]">
            <div class="flex items-center gap-4">
                <span id="footer-server-host">🌐 Server Aktif</span>
                <span id="footer-ping-display">📶 Mengukur Ping...</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-[#1A1A2D] border border-[#333347] text-[#C3C9B1]">Mode Multiplayer Real-Time</span>
            </div>
        </footer>
    </section>

    <!-- ===================================================================== -->
    <!-- SCREEN 2: MAIN ARENA HUB (FIGMA 00_GABUNG_LOBBY REDESIGN)             -->
    <!-- ===================================================================== -->
    <section data-screen-section="hub" class="hidden relative z-10 flex-1 flex flex-col px-4 sm:px-6 py-7 max-w-6xl mx-auto w-full gap-7">
        <!-- Hero Welcome + User Profile Card -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">
            <div class="lg:col-span-2 bg-[#1A1A2D] border border-[#333347] rounded-3xl p-6 sm:p-8 flex flex-col justify-between relative overflow-hidden">
                <div class="pointer-events-none absolute -top-16 -right-16 w-52 h-52 rounded-full bg-[#BDF454]/10 blur-3xl"></div>
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#0C0C1F] border border-[#333347] text-[10px] font-extrabold tracking-widest uppercase text-[#BDF454]">
                        <span class="w-2 h-2 rounded-full bg-[#BDF454]"></span>
                        <span>READY TO DRAW</span>
                    </div>
                    <h1 class="mt-3 font-serif font-black text-3xl sm:text-4xl text-[#E2E0FB] leading-tight">
                        Selamat Datang Kembali, <span id="hub-welcome-nickname" class="text-[#BDF454]">SenimanRusuh_89</span>!
                    </h1>
                    <p class="mt-2 text-sm text-[#C3C9B1] max-w-xl">
                        Pilih arena bermainmu di bawah: buat room dengan aturan sabotase sesukamu, masukkan kode room teman, atau gabung ke lobby publik yang sedang menunggu pemain.
                    </p>
                </div>
                <div class="mt-5 flex flex-wrap items-center gap-2.5">
                    <button id="btn-hub-quick-play" type="button" class="px-5 py-2.5 rounded-xl bg-[#BDF454] hover:bg-[#ccf970] text-[#14200A] font-extrabold text-xs uppercase tracking-wider transition cursor-pointer">
                        ⚡ Quick Match Sekarang
                    </button>
                    <button type="button" data-nav-view="museum" class="px-4 py-2.5 rounded-xl bg-[#28283C] hover:bg-[#333347] text-[#E2E0FB] font-bold text-xs transition cursor-pointer">
                        🖼️ Lihat Hall of Shame
                    </button>
                </div>
            </div>

            <!-- Player Profile & Live Stats Summary Card -->
            <div class="bg-[#1A1A2D] border border-[#333347] rounded-3xl p-6 flex flex-col justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div id="hub-user-avatar" class="w-14 h-14 rounded-2xl bg-[#0C0C1F] border-2 border-[#BDF454]/60 flex items-center justify-center shrink-0 overflow-hidden"></div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span id="hub-user-level" class="px-2 py-0.5 rounded-md bg-[#BDF454] text-[#14200A] text-[10px] font-extrabold uppercase">Lv. 1</span>
                            <span id="hub-auth-badge" class="px-2 py-0.5 rounded-md bg-[#28283C] text-[#D1BCFF] text-[10px] font-bold uppercase">Mode Tamu</span>
                        </div>
                        <h2 id="hub-profile-name" class="mt-1 font-serif font-bold text-lg text-[#E2E0FB] truncate">SenimanRusuh_89</h2>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <div class="bg-[#0C0C1F] border border-[#333347] rounded-2xl p-3">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-[#8D937D]">Total Skor</div>
                        <div id="hub-stat-score" class="mt-0.5 font-serif font-black text-xl text-[#BDF454]">0</div>
                    </div>
                    <div class="bg-[#0C0C1F] border border-[#333347] rounded-2xl p-3">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-[#8D937D]">Pertandingan</div>
                        <div id="hub-stat-games" class="mt-0.5 font-serif font-black text-xl text-[#D1BCFF]">0</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button id="btn-hub-edit-profile" type="button" class="flex-1 py-2 rounded-xl bg-[#28283C] hover:bg-[#333347] text-xs font-bold text-[#E2E0FB] transition cursor-pointer">
                        ✏️ Edit Profil
                    </button>
                    <button id="btn-hub-auth-action" type="button" class="flex-1 py-2 rounded-xl bg-[#5717BC]/30 hover:bg-[#5717BC]/50 border border-[#D1BCFF]/30 text-xs font-bold text-[#D1BCFF] transition cursor-pointer">
                        🔐 Masuk / Daftar
                    </button>
                </div>
            </div>
        </div>

        <!-- Dual Primary Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Create Lobby Card -->
            <div class="bg-[#1A1A2D] border border-[#333347] hover:border-[#BDF454]/70 rounded-3xl p-6 sm:p-7 flex flex-col justify-between gap-5 transition group">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-[#BDF454]/15 text-[#BDF454] text-[10px] font-extrabold tracking-widest uppercase">HOST ROOM</span>
                        <h3 class="mt-2 font-serif font-black text-2xl text-[#E2E0FB]">BUAT LOBBY BARU</h3>
                        <p class="mt-1.5 text-sm text-[#C3C9B1]">
                            Tentukan kapasitas 4–12 pemain, durasi menggambar, paket kata rahasia, dan tingkat mode chaos.
                        </p>
                    </div>
                    <span class="w-12 h-12 rounded-2xl bg-[#BDF454] text-[#14200A] flex items-center justify-center text-2xl font-black shrink-0 shadow-[0_8px_24px_rgba(189,244,84,0.25)]">＋</span>
                </div>
                <button id="btn-hub-create-lobby" type="button" class="w-full py-3.5 rounded-2xl bg-[#BDF454] hover:bg-[#ccf970] text-[#14200A] font-extrabold text-sm uppercase tracking-wider transition cursor-pointer">
                    Buat &amp; Atur Lobby →
                </button>
            </div>

            <!-- Join With Code Card -->
            <div class="bg-[#1A1A2D] border border-[#333347] hover:border-[#D1BCFF]/70 rounded-3xl p-6 sm:p-7 flex flex-col justify-between gap-5 transition group">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-[#5717BC]/30 text-[#D1BCFF] text-[10px] font-extrabold tracking-widest uppercase">INVITE CODE</span>
                        <h3 class="mt-2 font-serif font-black text-2xl text-[#E2E0FB]">GABUNG DENGAN KODE</h3>
                        <p class="mt-1.5 text-sm text-[#C3C9B1]">
                            Sudah punya 4 digit kode room dari teman? Verifikasi kode langsung dan pilih avatar karaktermumu!
                        </p>
                    </div>
                    <span class="w-12 h-12 rounded-2xl bg-[#5717BC] text-white flex items-center justify-center text-xl font-black shrink-0 shadow-[0_8px_24px_rgba(87,23,188,0.35)]">🔑</span>
                </div>
                <button id="btn-hub-join-modal" type="button" class="w-full py-3.5 rounded-2xl bg-[#5717BC] hover:bg-[#6B25DC] text-white font-extrabold text-sm uppercase tracking-wider transition cursor-pointer">
                    Masukkan Kode &amp; Pilih Avatar →
                </button>
            </div>
        </div>

        <!-- Live Public Lobbies Directory -->
        <div class="bg-[#1A1A2D] border border-[#333347] rounded-3xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="font-serif font-black text-xl text-[#E2E0FB] flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#BDF454]"></span>
                        <span>LOBBY PUBLIK AKTIF</span>
                    </h3>
                    <p class="text-xs text-[#C3C9B1] mt-0.5">Room nyata yang sedang terbuka &amp; menunggu pemain dari database server.</p>
                </div>
                <button id="btn-refresh-public-rooms" type="button" class="px-3.5 py-2 rounded-xl bg-[#28283C] hover:bg-[#333347] border border-[#333347] text-xs font-bold text-[#E2E0FB] transition cursor-pointer">
                    ↻ Segarkan Daftar
                </button>
            </div>
            <div id="hub-public-rooms-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5"></div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- SCREEN 3: LOGIN ARENA (FIGMA 04_LOGIN_CORET_CHAOS)                    -->
    <!-- ===================================================================== -->
    <section data-screen-section="login" class="hidden relative z-10 flex-1 flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md bg-[#1A1A2D] border border-[#333347] rounded-3xl p-6 sm:p-8 shadow-2xl">
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-16 h-16 rounded-2xl bg-[#0C0C1F] border-2 border-[#BDF454]/60 flex items-center justify-center mb-3 shadow-lg">
                    <svg viewBox="0 0 48 48" class="w-11 h-11" aria-hidden="true">
                        <path d="M12 18l-3-11 10 6c3-.9 7-.9 10 0l10-6-3 11c3 4 3.5 10.5 0 15-3 4-7.5 6-12 6s-9-2-12-6c-3.5-4.5-3-11 0-15z" fill="#5717BC" stroke="#BDF454" stroke-width="2.5"/>
                        <circle cx="18.5" cy="24" r="2.8" fill="#BDF454"/>
                        <circle cx="29.5" cy="24" r="2.8" fill="#BDF454"/>
                        <path d="M20 31c2.5 2 5.5 2 8 0" fill="none" stroke="#E2E0FB" stroke-width="2.4" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#BDF454]">AUTENTIKASI ARENA</span>
                <h1 class="mt-1 font-serif font-black text-3xl text-[#E2E0FB]">Masuk ke Arena</h1>
                <p class="mt-1 text-xs text-[#C3C9B1]">Masuk untuk menyinkronkan profil, level, dan riwayat skormu.</p>
            </div>

            <form id="form-login" class="space-y-4">
                <div>
                    <label for="input-login-email" class="block text-xs font-bold text-[#C3C9B1] mb-1.5">Email Arena</label>
                    <input
                        id="input-login-email"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="seniman@coretchaos.id"
                        class="w-full rounded-xl bg-[#0C0C1F] border border-[#333347] focus:border-[#BDF454] px-4 py-3 text-sm text-[#E2E0FB] placeholder-[#8D937D] focus:outline-none"
                    >
                </div>

                <div>
                    <label for="input-login-password" class="block text-xs font-bold text-[#C3C9B1] mb-1.5">Password</label>
                    <div class="relative flex items-center">
                        <input
                            id="input-login-password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan password..."
                            class="w-full rounded-xl bg-[#0C0C1F] border border-[#333347] focus:border-[#BDF454] pl-4 pr-11 py-3 text-sm text-[#E2E0FB] placeholder-[#8D937D] focus:outline-none"
                        >
                        <button id="btn-toggle-login-password" type="button" class="absolute right-3 text-xs font-bold text-[#8D937D] hover:text-[#BDF454] cursor-pointer" aria-label="Tampilkan password">
                            👁️
                        </button>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-xs text-[#C3C9B1] cursor-pointer select-none">
                    <input id="input-login-remember" type="checkbox" checked class="rounded border-[#333347] bg-[#0C0C1F] text-[#BDF454]">
                    <span>Ingat sesi masukku di perangkat ini</span>
                </label>

                <div id="login-error-box" class="hidden rounded-xl bg-[#4A1428] border border-[#FF4D6D]/50 px-3.5 py-2.5 text-xs font-bold text-[#FFB3C1]" role="alert"></div>

                <button id="btn-submit-login" type="submit" class="w-full py-3.5 rounded-2xl bg-[#BDF454] hover:bg-[#ccf970] text-[#14200A] font-extrabold text-sm uppercase tracking-wider shadow-[0_10px_24px_rgba(189,244,84,0.22)] transition cursor-pointer">
                    MASUK SEKARANG ⚡
                </button>
            </form>

            <div class="my-5 flex items-center gap-3 text-[11px] font-bold uppercase tracking-widest text-[#8D937D]">
                <span class="h-px flex-1 bg-[#333347]"></span>
                <span>ATAU</span>
                <span class="h-px flex-1 bg-[#333347]"></span>
            </div>

            <button id="btn-login-as-guest" type="button" class="w-full py-3 rounded-2xl bg-[#28283C] hover:bg-[#333347] border border-[#333347] text-xs font-extrabold text-[#E2E0FB] transition cursor-pointer">
                🎲 Masuk Cepat Sebagai Tamu
            </button>

            <p class="mt-6 text-center text-xs text-[#C3C9B1]">
                Belum punya akun?
                <button type="button" data-nav-view="register" class="font-extrabold text-[#BDF454] hover:underline cursor-pointer">
                    Bikin Akun Baru →
                </button>
            </p>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- SCREEN 4: REGISTER ARENA (FIGMA 08_REGISTER_CORET_CHAOS)              -->
    <!-- ===================================================================== -->
    <section data-screen-section="register" class="hidden relative z-10 flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-lg bg-[#1A1A2D] border border-[#333347] rounded-3xl p-6 sm:p-8 shadow-2xl">
            <div class="text-center mb-6">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#D1BCFF]">DAFTAR PEMAIN BARU</span>
                <h1 class="mt-1 font-serif font-black text-3xl text-[#E2E0FB]">Bikin Akun Baru</h1>
                <p class="mt-1 text-xs text-[#C3C9B1]">Buat identitas artistikmu untuk naik level &amp; menguasai leaderboard.</p>
            </div>

            <form id="form-register" class="space-y-4">
                <div>
                    <label for="input-register-nickname" class="block text-xs font-bold text-[#C3C9B1] mb-1.5">Nickname Artis</label>
                    <div class="flex items-center gap-2">
                        <input
                            id="input-register-nickname"
                            type="text"
                            required
                            maxlength="32"
                            placeholder="Contoh: KrayonKilat"
                            class="flex-1 rounded-xl bg-[#0C0C1F] border border-[#333347] focus:border-[#BDF454] px-4 py-2.5 text-sm font-bold text-[#E2E0FB] placeholder-[#8D937D] focus:outline-none min-w-0"
                        >
                        <button id="btn-randomize-register-nickname" type="button" class="px-3.5 py-2.5 rounded-xl bg-[#28283C] hover:bg-[#333347] text-xs font-bold text-[#D1BCFF] transition cursor-pointer shrink-0">
                            🎲 Acak
                        </button>
                    </div>
                </div>

                <div>
                    <span class="block text-xs font-bold text-[#C3C9B1] mb-2">Pilih Avatar Karakter</span>
                    <div id="register-avatar-picker" class="grid grid-cols-3 sm:grid-cols-6 gap-2"></div>
                </div>

                <div>
                    <label for="input-register-email" class="block text-xs font-bold text-[#C3C9B1] mb-1.5">Email Arena</label>
                    <input
                        id="input-register-email"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="nama@email.com"
                        class="w-full rounded-xl bg-[#0C0C1F] border border-[#333347] focus:border-[#BDF454] px-4 py-2.5 text-sm text-[#E2E0FB] placeholder-[#8D937D] focus:outline-none"
                    >
                </div>

                <div>
                    <label for="input-register-password" class="block text-xs font-bold text-[#C3C9B1] mb-1.5">Password (Minimal 8 karakter)</label>
                    <div class="relative flex items-center">
                        <input
                            id="input-register-password"
                            type="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter..."
                            class="w-full rounded-xl bg-[#0C0C1F] border border-[#333347] focus:border-[#BDF454] pl-4 pr-11 py-2.5 text-sm text-[#E2E0FB] placeholder-[#8D937D] focus:outline-none"
                        >
                        <button id="btn-toggle-register-password" type="button" class="absolute right-3 text-xs font-bold text-[#8D937D] hover:text-[#BDF454] cursor-pointer" aria-label="Tampilkan password">
                            👁️
                        </button>
                    </div>
                </div>

                <div id="register-error-box" class="hidden rounded-xl bg-[#4A1428] border border-[#FF4D6D]/50 px-3.5 py-2.5 text-xs font-bold text-[#FFB3C1]" role="alert"></div>

                <p class="text-[11px] text-[#8D937D] leading-relaxed">
                    Dengan mendaftar, profil karakter &amp; poin pertandingan langsung tersimpan di server.
                </p>

                <button id="btn-submit-register" type="submit" class="w-full py-3.5 rounded-2xl bg-[#5717BC] hover:bg-[#6B25DC] text-white font-extrabold text-sm uppercase tracking-wider shadow-[0_10px_26px_rgba(87,23,188,0.35)] transition cursor-pointer">
                    DAFTAR AKUN 🚀
                </button>
            </form>

            <p class="mt-5 text-center text-xs text-[#C3C9B1]">
                Sudah punya akun?
                <button type="button" data-nav-view="login" class="font-extrabold text-[#BDF454] hover:underline cursor-pointer">
                    Masuk ke Arena →
                </button>
            </p>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- SCREEN 5: RUANG TUNGGU LOBBY (FIGMA 10_RUANG_TUNGGU_LOBBY)            -->
    <!-- ===================================================================== -->
    <section data-screen-section="lobby" class="hidden relative z-10 flex-1 flex flex-col px-4 sm:px-6 pt-5 pb-36 max-w-6xl mx-auto w-full gap-5">
        <!-- Top Lobby Banner with 4 Segmented Room Code Boxes -->
        <div class="bg-[#1A1A2D] border border-[#333347] rounded-3xl p-5 sm:p-6 flex flex-wrap items-center justify-between gap-4 shadow-xl">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full bg-[#BDF454]/15 border border-[#BDF454]/40 text-[10px] font-extrabold uppercase tracking-widest text-[#BDF454]">RUANG TUNGGU LOBBY</span>
                    <span id="lobby-player-counter" class="px-2.5 py-0.5 rounded-full bg-[#28283C] border border-[#333347] text-[11px] font-extrabold text-[#E2E0FB]">0/8</span>
                    <span id="lobby-room-code-display" class="text-xs font-extrabold tracking-widest text-[#D1BCFF]">KODE: - - - -</span>
                </div>
                <h1 class="font-serif font-black text-2xl sm:text-3xl text-[#E2E0FB]">Siapkan Kuas &amp; Strategi Sabotase</h1>
                <p id="lobby-status-banner" class="text-xs sm:text-sm text-[#C3C9B1]">
                    Klik kartumu untuk mengubah status siap. Pelukis pertama dipilih acak oleh server.
                </p>
            </div>

            <!-- 4 Segmented Character Boxes + Copy Actions -->
            <div class="flex flex-col sm:items-end gap-2.5">
                <div class="flex items-center gap-2" aria-label="Kode Room 4 Karakter">
                    <span id="lobby-code-box-0" class="w-11 h-12 rounded-xl bg-[#0C0C1F] border-2 border-[#BDF454]/70 flex items-center justify-center font-serif font-black text-2xl text-[#BDF454] shadow-inner">-</span>
                    <span id="lobby-code-box-1" class="w-11 h-12 rounded-xl bg-[#0C0C1F] border-2 border-[#BDF454]/70 flex items-center justify-center font-serif font-black text-2xl text-[#BDF454] shadow-inner">-</span>
                    <span id="lobby-code-box-2" class="w-11 h-12 rounded-xl bg-[#0C0C1F] border-2 border-[#BDF454]/70 flex items-center justify-center font-serif font-black text-2xl text-[#BDF454] shadow-inner">-</span>
                    <span id="lobby-code-box-3" class="w-11 h-12 rounded-xl bg-[#0C0C1F] border-2 border-[#BDF454]/70 flex items-center justify-center font-serif font-black text-2xl text-[#BDF454] shadow-inner">-</span>
                </div>
                <div class="flex items-center gap-2">
                    <button id="btn-lobby-copy-code" type="button" class="px-3.5 py-1.5 rounded-xl bg-[#BDF454] hover:bg-[#ccf970] text-[#14200A] font-extrabold text-[11px] uppercase tracking-wider transition cursor-pointer">
                        📋 SALIN KODE LOBBY
                    </button>
                    <button id="btn-lobby-copy-link" type="button" class="px-3.5 py-1.5 rounded-xl bg-[#28283C] hover:bg-[#333347] border border-[#333347] text-[#E2E0FB] font-bold text-[11px] transition cursor-pointer">
                        🔗 Salin Link
                    </button>
                </div>
            </div>
        </div>

        <!-- 2-Column Main Area: Left Slots & Rules | Right Live Chat & Tips -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
            <!-- Left Column (2/3) -->
            <div class="lg:col-span-2 space-y-5">
                <!-- Players Grid -->
                <div class="bg-[#1A1A2D] border border-[#333347] rounded-3xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-serif font-extrabold text-lg text-[#E2E0FB]">Slot Pemain di Arena</h2>
                        <span class="text-[11px] font-bold text-[#8D937D]">Klik kartumu untuk toggle SIAP</span>
                    </div>
                    <div id="lobby-players-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-3"></div>
                </div>

                <!-- Aturan Permainan Saat Ini -->
                <div id="lobby-room-rules-pill" class="bg-[#1A1A2D] border border-[#333347] rounded-3xl p-5">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <h2 class="font-serif font-extrabold text-base text-[#E2E0FB]">Aturan Permainan Saat Ini</h2>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#28283C] text-[10px] font-bold text-[#D1BCFF] uppercase">Tersinkron Live</span>
                    </div>
                    <p id="lobby-room-rules-text" class="text-xs sm:text-sm font-bold text-[#BDF454]">
                        Chaos Klasik • 60 detik • 5 ronde • Campuran
                    </p>
                </div>
            </div>

            <!-- Right Column (1/3): OBROLAN LOBI + Tips Chaos -->
            <aside class="space-y-5">
                <!-- Live Lobby Chat -->
                <div class="bg-[#1A1A2D] border border-[#333347] rounded-3xl p-5 flex flex-col">
                    <div class="flex items-center justify-between gap-2 pb-3 border-b border-[#333347]">
                        <h2 class="font-serif font-extrabold text-base text-[#E2E0FB] tracking-wide">OBROLAN LOBI</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#0C0C1F] border border-[#333347] text-[10px] font-extrabold text-[#BDF454]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#BDF454] animate-pulse"></span>
                            LIVE SYNC
                        </span>
                    </div>
                    <div id="lobby-chat-messages" class="h-64 overflow-y-auto py-3 space-y-2 pr-1">
                        <div class="text-xs text-center text-[#8D937D] py-12">Belum ada obrolan di lobby. Sapa temanmu dulu!</div>
                    </div>
                    <form id="form-lobby-chat" class="pt-3 border-t border-[#333347] flex items-center gap-2">
                        <input
                            id="input-lobby-chat"
                            type="text"
                            maxlength="240"
                            placeholder="Ketik pesan ke obrolan lobi..."
                            aria-label="Obrolan Lobi"
                            class="flex-1 rounded-xl bg-[#0C0C1F] border border-[#333347] focus:border-[#BDF454] px-3.5 py-2 text-xs text-[#E2E0FB] placeholder-[#8D937D] focus:outline-none min-w-0"
                        >
                        <button id="btn-send-lobby-chat" type="submit" class="px-3.5 py-2 rounded-xl bg-[#BDF454] hover:bg-[#ccf970] text-[#14200A] font-extrabold text-xs transition cursor-pointer shrink-0">
                            Kirim
                        </button>
                    </form>
                </div>

                <!-- Tips Chaos Card -->
                <div class="bg-[#1A1A2D] border border-[#333347] rounded-3xl p-5">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-[#D1BCFF] mb-1.5">💡 Tips Chaos</div>
                    <p class="text-xs text-[#C3C9B1] leading-relaxed">
                        Pelukis menentukan kata rahasia di awal ronde. Penebak mengumpulkan <span class="text-[#BDF454] font-bold">Chaos Energy</span> setiap detik untuk menjatuhkan kutukan Gempa, Kuas Mabuk, Lampu Mati, dan Stiker Bom!
                    </p>
                </div>
            </aside>
        </div>

        <!-- Fixed Non-Overlapping Bottom Control Dock -->
        <div class="fixed bottom-0 left-0 right-0 z-30 bg-[#0C0C1F]/95 backdrop-blur-md border-t border-[#333347] px-4 sm:px-6 py-3">
            <div class="max-w-6xl mx-auto flex flex-col gap-2">
                <p id="lobby-hint-text" class="text-[11px] font-bold text-[#C3C9B1] text-center sm:text-left">
                    Butuh minimal 2 pemain bergabung dan siap untuk memulai.
                </p>
                <div class="flex flex-wrap items-center justify-between gap-2.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <button id="btn-lobby-toggle-ready" type="button" class="px-4 py-2.5 rounded-xl bg-[#28283C] hover:bg-[#333347] border border-[#333347] text-xs font-extrabold text-[#E2E0FB] transition cursor-pointer">
                            ✓ UBAH STATUS SIAP
                        </button>
                        <button id="btn-lobby-edit-room" type="button" class="px-4 py-2.5 rounded-xl bg-[#28283C] hover:bg-[#333347] border border-[#333347] text-xs font-extrabold text-[#D1BCFF] transition cursor-pointer">
                            ⚙ PENGATURAN
                        </button>
                        <button id="btn-lobby-leave-room" type="button" class="px-4 py-2.5 rounded-xl bg-[#3A1628] hover:bg-[#4F1D36] border border-[#FF4D6D]/35 text-xs font-extrabold text-[#FFB3C1] transition cursor-pointer">
                            ➔ KELUAR
                        </button>
                    </div>
                    <button id="btn-lobby-start-game" type="button" disabled class="px-6 py-2.5 rounded-xl bg-[#BDF454] disabled:bg-[#28283C] text-[#14200A] disabled:text-[#8D937D] font-extrabold text-xs sm:text-sm uppercase tracking-wider transition cursor-pointer disabled:cursor-not-allowed shadow-[0_6px_20px_rgba(189,244,84,0.2)] disabled:shadow-none">
                        🎮 MULAI PERMAINAN
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- SCREEN 6: GAMEPLAY (60FPS CANVAS, SABOTEUR DECK & LIVE GUESSING)      -->
    <!-- ===================================================================== -->
    <section data-screen-section="gameplay" class="hidden relative z-10 flex-1 flex flex-col justify-between w-full">
        <style>
            section[data-screen-section="gameplay"] {
                --bg:#111124;
                --sf:#1A1A2D;
                --sf2:#28283C;
                --ln:#333347;
                --lime:#BDF454;
                --lav:#D1BCFF;
                --red:#FF4D6D;
                --amb:#FFC83B;
                --tx:#E2E0FB;
                --mu:#C3C9B1;
                --ser:'Fraunces',Georgia,serif;
                --sans:'DM Sans',system-ui,sans-serif;
                font:15px/1.45 var(--sans);
                color:var(--tx);
                box-sizing:border-box;
            }
            section[data-screen-section="gameplay"] *,
            section[data-screen-section="gameplay"] *::before,
            section[data-screen-section="gameplay"] *::after {
                box-sizing:inherit;
            }
            section[data-screen-section="gameplay"] button,
            section[data-screen-section="gameplay"] input {
                font:inherit;
                color:inherit;
            }
            section[data-screen-section="gameplay"] .wrap {
                max-width:1280px;
                margin:0 auto;
                padding:18px 24px 36px;
                width: 100%;
            }
            section[data-screen-section="gameplay"] .card {
                background:var(--sf);
                border:1px solid var(--ln);
                border-radius:20px;
            }
            section[data-screen-section="gameplay"] .hd {
                display:grid;
                grid-template-columns:1fr auto 1fr;
                align-items:center;
                gap:16px;
                padding:14px 20px;
                margin-bottom:16px;
            }
            section[data-screen-section="gameplay"] .rd {
                display:flex;
                flex-direction:column;
            }
            section[data-screen-section="gameplay"] .rd small,
            section[data-screen-section="gameplay"] .wd small,
            section[data-screen-section="gameplay"] .tmr small {
                font-size:12px;
                color:var(--mu);
            }
            section[data-screen-section="gameplay"] .rd b {
                font:900 22px var(--ser);
            }
            section[data-screen-section="gameplay"] .wd {
                text-align:center;
                padding:8px 22px;
                border-radius:14px;
                background:#0C0C1F;
                border:1.5px solid var(--ln);
                min-width:260px;
            }
            section[data-screen-section="gameplay"] .wd b {
                display:block;
                font:900 22px var(--ser);
                letter-spacing:2px;
                color:var(--lime);
            }
            section[data-screen-section="gameplay"] .wd b.no {
                font-size:14px;
                letter-spacing:0;
                color:var(--lav);
                font-family:var(--sans);
                font-weight:700;
            }
            section[data-screen-section="gameplay"] .tmr {
                display:flex;
                align-items:center;
                gap:12px;
                justify-self:end;
            }
            section[data-screen-section="gameplay"] .ring {
                position:relative;
                width:54px;
                height:54px;
                display:grid;
                place-items:center;
            }
            section[data-screen-section="gameplay"] .ring svg {
                position:absolute;
                inset:0;
                transform:rotate(-90deg);
            }
            section[data-screen-section="gameplay"] .ring circle {
                fill:none;
                stroke-width:5;
            }
            section[data-screen-section="gameplay"] .ring .bgc {
                stroke:var(--ln);
            }
            section[data-screen-section="gameplay"] .ring .fg {
                stroke:var(--lime);
                stroke-linecap:round;
                stroke-dasharray:163.4;
                stroke-dashoffset:0;
                transition:stroke-dashoffset 1s linear, stroke .3s;
            }
            section[data-screen-section="gameplay"] .ring.lo .fg {
                stroke:var(--red);
            }
            section[data-screen-section="gameplay"] .ring b {
                font:900 18px var(--ser);
                position:relative;
            }
            section[data-screen-section="gameplay"] .wp {
                padding:22px 24px;
                margin-bottom:16px;
                border:2px solid var(--lime);
                box-shadow:0 0 0 4px rgba(189, 244, 84, 0.08);
            }
            section[data-screen-section="gameplay"] .wp h2 {
                margin:0;
                font:900 24px var(--ser);
            }
            section[data-screen-section="gameplay"] .wp p {
                margin:4px 0 14px;
                color:var(--mu);
                font-size:14px;
            }
            section[data-screen-section="gameplay"] .wr {
                display:flex;
                gap:10px;
            }
            section[data-screen-section="gameplay"] .wr input {
                flex:1;
                background:#0C0C1F;
                border:1.5px solid var(--ln);
                border-radius:14px;
                padding:14px 18px;
                font-size:16px;
            }
            section[data-screen-section="gameplay"] .wr input:focus {
                border-color:var(--lime);
                outline:0;
            }
            section[data-screen-section="gameplay"] .set {
                padding:0 26px;
                border-radius:14px;
                background:var(--lime);
                color:#14200A;
                font:800 16px var(--ser);
                transition:transform .15s;
                cursor: pointer;
            }
            section[data-screen-section="gameplay"] .set:hover {
                transform:translateY(-1px);
            }
            section[data-screen-section="gameplay"] .chips {
                display:flex;
                flex-wrap:wrap;
                gap:8px;
                margin-top:12px;
                align-items:center;
                color:var(--mu);
                font-size:13px;
            }
            section[data-screen-section="gameplay"] .chips button {
                background:var(--sf2);
                border-radius:99px;
                padding:6px 14px;
                font-size:13px;
                font-weight:500;
                transition:background .15s;
                cursor: pointer;
            }
            section[data-screen-section="gameplay"] .chips button:hover {
                background:var(--ln);
            }
            section[data-screen-section="gameplay"] .er {
                color:#ff9db0;
                font-size:13px;
                margin-top:8px;
                min-height:18px;
            }
            section[data-screen-section="gameplay"] .main {
                display:grid;
                grid-template-columns:1fr 372px;
                gap:16px;
                align-items:start;
            }
            section[data-screen-section="gameplay"] .tools {
                display:flex;
                flex-wrap:wrap;
                gap:12px;
                align-items:center;
                justify-content:space-between;
                padding:12px 16px;
                border-bottom:1px solid var(--ln);
            }
            section[data-screen-section="gameplay"] .tg {
                display:flex;
                gap:8px;
                align-items:center;
            }
            section[data-screen-section="gameplay"] .sw {
                width:30px;
                height:30px;
                border-radius:50%;
                border:3px solid transparent;
                box-shadow:inset 0 0 0 2px rgba(0,0,0,0.25);
                transition:transform .15s;
                cursor: pointer;
            }
            section[data-screen-section="gameplay"] .sw:hover {
                transform:scale(1.12);
            }
            section[data-screen-section="gameplay"] .sw.on {
                border-color:#fff;
            }
            section[data-screen-section="gameplay"] .sz {
                padding:7px 13px;
                border-radius:10px;
                background:#0C0C1F;
                font-weight:700;
                font-size:13px;
                cursor: pointer;
                transition: background .15s, color .15s;
            }
            section[data-screen-section="gameplay"] .sz.on {
                background:var(--lime);
                color:#14200A;
            }
            section[data-screen-section="gameplay"] .tb {
                padding:8px 14px;
                border-radius:11px;
                background:var(--sf2);
                font-weight:700;
                font-size:13px;
                transition: background .15s;
                cursor: pointer;
            }
            section[data-screen-section="gameplay"] .tb:hover {
                background:var(--ln);
            }
            section[data-screen-section="gameplay"] .tb.d {
                background:#35142a;
                color:#ff9db0;
            }
            section[data-screen-section="gameplay"] .tb.d:hover {
                background:#4a1c3b;
            }
            section[data-screen-section="gameplay"] .cw {
                position:relative;
                margin:12px;
                border-radius:14px;
                overflow:hidden;
                transition:filter .3s, transform .2s;
            }
            section[data-screen-section="gameplay"] #chaos-canvas {
                display:block;
                width:100%;
                aspect-ratio:8/5;
                touch-action:none;
                cursor:crosshair;
                background:#FFF8E9;
                background-image:linear-gradient(#0000000a 1px, transparent 1px), linear-gradient(90deg, #0000000a 1px, transparent 1px);
                background-size:32px 32px;
            }
            section[data-screen-section="gameplay"] .ov {
                position:absolute;
                inset:0;
                display:grid;
                place-items:center;
                background:#111124dd;
                font:800 22px var(--ser);
                color:var(--tx);
                text-align:center;
                padding:20px;
                z-index: 10;
            }
            section[data-screen-section="gameplay"] .ov span {
                display:block;
                font:400 14px var(--sans);
                color:var(--mu);
                margin-top:6px;
            }
            section[data-screen-section="gameplay"] .fx-gempa {
                animation:sh .1s infinite;
            }
            @keyframes sh {
                0%{transform:translate(4px,2px)}
                50%{transform:translate(-4px,-2px)}
                100%{transform:translate(2px,-4px)}
            }
            section[data-screen-section="gameplay"] .fx-mabuk {
                filter:blur(3px) hue-rotate(80deg);
                transform:rotate(1.5deg);
            }
            section[data-screen-section="gameplay"] .fx-gelap::after {
                content:"";
                position:absolute;
                inset:0;
                background:radial-gradient(circle at var(--mx,50%) var(--my,50%), transparent 70px, #000 210px);
                pointer-events:none;
                z-index: 5;
            }
            section[data-screen-section="gameplay"] .st {
                display:grid;
                gap:16px;
            }
            section[data-screen-section="gameplay"] .pd {
                padding:16px 18px;
            }
            section[data-screen-section="gameplay"] .pd h3 {
                margin:0 0 10px;
                font:800 15px var(--ser);
                display:flex;
                justify-content:space-between;
                align-items:center;
            }
            section[data-screen-section="gameplay"] .pd h3 span {
                font:700 13px var(--sans);
                color:var(--lime);
            }
            section[data-screen-section="gameplay"] .en {
                height:10px;
                border-radius:9px;
                background:#0C0C1F;
                overflow:hidden;
            }
            section[data-screen-section="gameplay"] .en i {
                display:block;
                height:100%;
                width:100%;
                background:var(--lime);
                border-radius:9px;
                transition:width .4s;
            }
            section[data-screen-section="gameplay"] .cds {
                display:grid;
                grid-template-columns:repeat(4, 1fr);
                gap:8px;
                margin-top:12px;
            }
            section[data-screen-section="gameplay"] .cds button {
                position:relative;
                display:grid;
                gap:2px;
                justify-items:center;
                padding:10px 4px;
                border-radius:14px;
                background:#0C0C1F;
                border:1.5px solid var(--ln);
                font-size:12px;
                font-weight:700;
                transition:transform .15s, border-color .15s;
                cursor: pointer;
            }
            section[data-screen-section="gameplay"] .cds button:hover:not(:disabled) {
                transform:translateY(-2px);
                border-color:var(--lav);
            }
            section[data-screen-section="gameplay"] .cds button:disabled {
                opacity:.4;
                cursor:not-allowed;
            }
            section[data-screen-section="gameplay"] .cds i {
                font-style:normal;
                font-size:24px;
            }
            section[data-screen-section="gameplay"] .cds small {
                color:var(--amb);
            }
            section[data-screen-section="gameplay"] .note {
                margin:8px 0 0;
                color:var(--mu);
                font-size:11px;
            }
            section[data-screen-section="gameplay"] #chat {
                height:210px;
                overflow-y:auto;
                display:flex;
                flex-direction:column;
                gap:6px;
                margin-bottom:10px;
                padding-right: 4px;
            }
            section[data-screen-section="gameplay"] .m {
                background:#0C0C1F;
                border-radius:12px;
                padding:7px 12px;
                font-size:13px;
                overflow-wrap:anywhere;
            }
            section[data-screen-section="gameplay"] .m b {
                color:var(--lime);
            }
            section[data-screen-section="gameplay"] .m.n {
                background:var(--red);
                font-weight:700;
                color:#fff;
            }
            section[data-screen-section="gameplay"] .m.exact {
                background:linear-gradient(135deg, #166534, #15803d);
                font-weight:700;
                color:#fff;
                border: 1px solid #BDF454;
            }
            section[data-screen-section="gameplay"] .m.close {
                background: #854d0e;
                color: #fef08a;
                font-weight: 700;
            }
            section[data-screen-section="gameplay"] .m.s {
                color:var(--lav);
                font-style: italic;
            }
            section[data-screen-section="gameplay"] .emp {
                margin:auto;
                color:var(--mu);
                font-size:13px;
                text-align:center;
            }
            section[data-screen-section="gameplay"] .cr {
                display:flex;
                gap:8px;
            }
            section[data-screen-section="gameplay"] .cr input {
                flex:1;
                background:#0C0C1F;
                border:1.5px solid var(--ln);
                border-radius:12px;
                padding:10px 14px;
                font-size:14px;
                color: var(--tx);
            }
            section[data-screen-section="gameplay"] .cr button {
                padding:0 18px;
                border-radius:12px;
                background:var(--lime);
                color:#14200A;
                font-weight:800;
                transition: transform .15s, background .15s;
                cursor: pointer;
            }
            section[data-screen-section="gameplay"] .cr button:hover:not(:disabled) {
                transform: translateY(-1px);
            }
            section[data-screen-section="gameplay"] .cr button:disabled {
                background:var(--ln);
                color:var(--mu);
                cursor: not-allowed;
            }
            section[data-screen-section="gameplay"] .sb {
                display:grid;
                gap:6px;
                max-height: 200px;
                overflow-y: auto;
            }
            section[data-screen-section="gameplay"] .sb div {
                display:flex;
                justify-content:space-between;
                align-items:center;
                padding:9px 12px;
                border-radius:12px;
                background:#0C0C1F;
                font-size:14px;
            }
            section[data-screen-section="gameplay"] .sb .me {
                border:1.5px solid var(--lime);
            }
            section[data-screen-section="gameplay"] .sb b {
                color:var(--lime);
            }
            @media(max-width:899px){
                section[data-screen-section="gameplay"] .main {
                    grid-template-columns:1fr;
                }
                section[data-screen-section="gameplay"] .hd {
                    grid-template-columns:1fr;
                    text-align: center;
                }
                section[data-screen-section="gameplay"] .tmr {
                    justify-self:center;
                }
                section[data-screen-section="gameplay"] .wd {
                    min-width:0;
                    width:100%;
                }
                section[data-screen-section="gameplay"] .wrap {
                    padding-inline: 16px;
                }
            }
            @media(prefers-reduced-motion:reduce){
                section[data-screen-section="gameplay"] *{transition:none!important;}
                section[data-screen-section="gameplay"] .fx-gempa{animation:none;}
            }
        </style>

        <div class="wrap">
            <!-- Header Status Bar -->
            <header class="card hd">
                <div class="rd">
                    <small>Ronde</small>
                    <b id="hud-round-number">1 / 5</b>
                </div>
                <div class="wd">
                    <small id="hud-word-label">Kata rahasia · hanya kamu yang lihat</small>
                    <b class="no" id="hud-word-display">Belum ditetapkan</b>
                </div>
                <div class="tmr">
                    <small id="hud-timer-status">Tetapkan kata untuk memulai</small>
                    <div class="ring" id="game-timer-ring">
                        <svg viewBox="0 0 62 62">
                            <circle class="bgc" cx="31" cy="31" r="26"/>
                            <circle class="fg" id="game-timer-fg" cx="31" cy="31" r="26"/>
                        </svg>
                        <b id="hud-round-timer">60</b>
                    </div>
                </div>
            </header>

            <!-- Artist Secret Word Form -->
            <form class="card wp" id="form-artist-secret-word">
                <h2>Giliranmu melukis!</h2>
                <p>Ketik kata rahasia bebas (2 sampai 40 karakter) atau pilih rekomendasi. Jangan ditulis di kanvas.</p>
                <div class="wr">
                    <input id="input-artist-secret-word" maxlength="40" placeholder="Contoh: Kucing naik helikopter" aria-label="Kata rahasia">
                    <button class="set" id="btn-submit-secret-word" type="submit">Tetapkan kata</button>
                </div>
                <div class="er" id="artist-word-error" role="alert"></div>
                <div class="chips" id="artist-word-suggestions">
                    <span>Rekomendasi:</span>
                    <button type="button" data-suggested-word="Kucing naik helikopter">Kucing naik helikopter</button>
                    <button type="button" data-suggested-word="Sate ayam Madura">Sate ayam Madura</button>
                    <button type="button" data-suggested-word="Superman terbang miring">Superman terbang miring</button>
                </div>
            </form>

            <div class="main">
                <!-- Left: Canvas with Tools -->
                <section class="card">
                    <!-- Top Canvas Live Sync Strip -->
                    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-2.5 border-b border-[#333347] text-[11px] font-extrabold tracking-wider uppercase">
                        <span class="inline-flex items-center gap-2 text-[#BDF454]">
                            <span class="w-2 h-2 rounded-full bg-[#BDF454] animate-pulse"></span>
                            <span>KANVAS LANGSUNG (SYNCHED 60FPS)</span>
                        </span>
                        <div class="flex items-center gap-3">
                            <span id="canvas-active-curse-banner" class="hidden px-2.5 py-0.5 rounded-full bg-[#FF4D6D]/20 border border-[#FF4D6D]/50 text-[#FFB3C1]">
                                <span id="canvas-active-curse-text">KUTUKAN AKTIF</span>
                                <span id="canvas-active-curse-countdown" class="ml-1 text-[#BDF454]">0s</span>
                            </span>
                            <span id="canvas-ping-display" class="text-[#C3C9B1]">📶 -- ms Ping</span>
                        </div>
                    </div>

                    <!-- Tools (Palette & Sizes & Actions) -->
                    <div class="tools" id="canvas-artist-tools">
                        <div class="tg" id="cols" aria-label="Warna kuas">
                            <button type="button" class="sw on" data-brush-color="#222222" style="background: #222222;" aria-label="Warna Hitam"></button>
                            <button type="button" class="sw" data-brush-color="#ff4d6d" style="background: #ff4d6d;" aria-label="Warna Merah"></button>
                            <button type="button" class="sw" data-brush-color="#7ed321" style="background: #7ed321;" aria-label="Warna Hijau"></button>
                            <button type="button" class="sw" data-brush-color="#17a2ff" style="background: #17a2ff;" aria-label="Warna Biru"></button>
                            <button type="button" class="sw" data-brush-color="#a855f7" style="background: #a855f7;" aria-label="Warna Ungu"></button>
                            <button type="button" class="sw" data-brush-color="#ff8a1f" style="background: #ff8a1f;" aria-label="Warna Oranye"></button>
                            <button type="button" class="sw" data-brush-color="#FFF8E9" style="background: #FFF8E9;" aria-label="Penghapus"></button>
                        </div>
                        <div class="tg" id="szs">
                            <button type="button" class="sz" data-brush-size="3">3px</button>
                            <button type="button" class="sz on" data-brush-size="6">6px</button>
                            <button type="button" class="sz" data-brush-size="12">12px</button>
                        </div>
                        <div class="tg">
                            <button type="button" class="tb" id="btn-canvas-undo">↩ Undo</button>
                            <button type="button" class="tb d" id="btn-canvas-clear">Bersihkan</button>
                        </div>
                    </div>

                    <!-- Canvas Wrapper -->
                    <div class="cw" id="chaos-canvas-wrapper">
                        <canvas id="chaos-canvas" width="800" height="500"></canvas>
                        <div id="inverted-cursor-ghost" class="hidden pointer-events-none absolute w-4 h-4 rounded-full bg-[#FF4D6D] border-2 border-white shadow-lg z-30"></div>
                        <div class="ov" id="canvas-overlay">
                            <div>Tetapkan kata rahasia dulu<span>Kanvas aktif setelah kata ditetapkan</span></div>
                        </div>
                        <div id="curse-sticker-bomb-layer" class="hidden pointer-events-none absolute inset-0 flex items-center justify-center z-20 p-4"></div>
                    </div>
                </section>

                <!-- Right: Sidebar (Sabotage, Live Guessing/Chat, Scoreboard) -->
                <aside class="st">
                    <section class="card pd">
                        <h3>CHAOS ENERGY <span id="chaos-energy-val">100 / 100</span></h3>
                        <div class="en"><i id="chaos-energy-bar" style="width: 100%;"></i></div>
                        <div class="cds">
                            <button type="button" data-curse-trigger="earthquake" data-cost="30"><i>🌋</i>Gempa<small>⚡30</small><span data-cooldown-badge class="hidden absolute top-1 right-1 px-1.5 py-0.5 rounded bg-[#FF4D6D] text-[10px] text-white font-black"></span></button>
                            <button type="button" data-curse-trigger="invert" data-cost="45"><i>🌀</i>Mabuk<small>⚡45</small><span data-cooldown-badge class="hidden absolute top-1 right-1 px-1.5 py-0.5 rounded bg-[#FF4D6D] text-[10px] text-white font-black"></span></button>
                            <button type="button" data-curse-trigger="blindfold" data-cost="60"><i>💡</i>Gelap<small>⚡60</small><span data-cooldown-badge class="hidden absolute top-1 right-1 px-1.5 py-0.5 rounded bg-[#FF4D6D] text-[10px] text-white font-black"></span></button>
                            <button type="button" data-curse-trigger="sticker" data-cost="25"><i>💣</i>Stiker<small>⚡25</small><span data-cooldown-badge class="hidden absolute top-1 right-1 px-1.5 py-0.5 rounded bg-[#FF4D6D] text-[10px] text-white font-black"></span></button>
                        </div>
                        <p class="note">Kirim kartu kutukan untuk mengacaukan kanvas Pelukis!</p>
                    </section>

                    <section class="card pd">
                        <h3>TEBAKAN LANGSUNG <span id="chat-players-count">Pemain di room</span></h3>
                        <div id="chat">
                            <div class="emp" id="chat-empty-placeholder">Belum ada tebakan.<br>Tebakan &amp; chat pemain muncul di sini.</div>
                        </div>
                        <form id="form-live-guess" class="cr">
                            <input id="input-live-guess" placeholder="Ketik tebakan atau chat..." aria-label="Tebakan">
                            <button type="submit" id="btn-submit-live-guess">Kirim</button>
                        </form>
                    </section>

                    <section class="card pd">
                        <h3>Papan skor <span id="quick-rank-you">Skormu: 0</span></h3>
                        <div class="sb" id="game-scoreboard"></div>
                    </section>
                </aside>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- SCREEN 7: ROUND RESULTS (RONDE SELESAI!)                              -->
    <!-- ===================================================================== -->
    <section data-screen-section="results" class="hidden relative z-10 flex-1 flex flex-col justify-between w-full">
        <style>
            section[data-screen-section="results"] {
                --bg:#111124;
                --sf:#1A1A2D;
                --sf2:#28283C;
                --ln:#333347;
                --lime:#BDF454;
                --lav:#D1BCFF;
                --red:#FF4D6D;
                --amb:#FFC83B;
                --tx:#E2E0FB;
                --mu:#C3C9B1;
                --ser:'Fraunces',Georgia,serif;
                --sans:'DM Sans',system-ui,sans-serif;
                font:15px/1.45 var(--sans);
                color:var(--tx);
                box-sizing:border-box;
            }
            section[data-screen-section="results"] *,
            section[data-screen-section="results"] *::before,
            section[data-screen-section="results"] *::after {
                box-sizing:inherit;
            }
            section[data-screen-section="results"] button,
            section[data-screen-section="results"] input {
                font:inherit;
                color:inherit;
            }
            section[data-screen-section="results"] button {
                cursor:pointer;
                border:0;
                background:none;
            }
            section[data-screen-section="results"] .wrap {
                max-width:1120px;
                margin:0 auto;
                padding:32px 24px 185px;
                width:100%;
            }
            section[data-screen-section="results"] .hero {
                text-align:center;
            }
            section[data-screen-section="results"] .chip {
                display:inline-flex;
                align-items:center;
                gap:8px;
                padding:7px 16px;
                border-radius:99px;
                background:var(--sf);
                border:1px solid var(--ln);
                font-size:12px;
                font-weight:800;
                color:var(--lime);
            }
            section[data-screen-section="results"] .hero h1 {
                margin:12px 0 8px;
                font:900 clamp(32px, 5vw, 52px)/1.05 var(--ser);
            }
            section[data-screen-section="results"] .word small {
                display:block;
                font-size:12px;
                color:var(--mu);
                letter-spacing:1.4px;
                text-transform:uppercase;
            }
            section[data-screen-section="results"] .word b {
                display:inline-flex;
                flex-wrap:wrap;
                justify-content:center;
                gap:5px;
                margin-top:6px;
                font:900 clamp(22px, 3.6vw, 34px) var(--ser);
                color:var(--lime);
            }
            section[data-screen-section="results"] .word b span {
                display:inline-block;
                padding:4px 9px;
                border-radius:9px;
                background:var(--sf);
                border:1px solid var(--ln);
            }
            section[data-screen-section="results"] .stats {
                display:flex;
                justify-content:center;
                gap:12px;
                flex-wrap:wrap;
                margin-top:14px;
            }
            section[data-screen-section="results"] .stats span {
                padding:8px 14px;
                border-radius:14px;
                background:var(--sf);
                border:1px solid var(--ln);
                font-size:13px;
            }
            section[data-screen-section="results"] .stats b {
                color:var(--lime);
            }
            section[data-screen-section="results"] .cols {
                display:grid;
                grid-template-columns:430px 1fr;
                gap:44px;
                align-items:start;
                margin-top:34px;
            }
            section[data-screen-section="results"] .pol {
                position:relative;
                background:#FFF8E9;
                padding:14px 14px 56px;
                border-radius:14px;
                transform:rotate(-2.2deg);
                box-shadow:0 22px 50px rgba(0,0,0,0.55);
                transition:transform .3s;
            }
            section[data-screen-section="results"] .pol:hover {
                transform:rotate(0);
            }
            section[data-screen-section="results"] .pol::before {
                content:"";
                position:absolute;
                top:-14px;
                left:50%;
                width:110px;
                height:28px;
                margin-left:-55px;
                background:rgba(255, 200, 59, 0.65);
                transform:rotate(3deg);
                border-radius:3px;
            }
            section[data-screen-section="results"] #results-polaroid-canvas {
                display:block;
                width:100%;
                aspect-ratio:8/5;
                border-radius:6px;
                background:#FFF8E9;
                border:1px solid rgba(0,0,0,0.12);
            }
            section[data-screen-section="results"] .cap {
                position:absolute;
                left:18px;
                right:18px;
                bottom:14px;
                display:flex;
                justify-content:space-between;
                align-items:center;
                color:#241c3c;
                font:700 14px var(--ser);
            }
            section[data-screen-section="results"] .rp {
                background:#111124;
                color:var(--lime);
                padding:8px 14px;
                border-radius:10px;
                font:800 13px var(--ser);
                transition:transform .15s;
            }
            section[data-screen-section="results"] .rp:hover {
                transform:scale(1.06);
            }
            section[data-screen-section="results"] .stamp {
                position:absolute;
                top:-6px;
                right:-22px;
                background:var(--red);
                color:#fff;
                padding:8px 18px;
                border-radius:99px;
                font:900 15px var(--ser);
                transform:rotate(8deg);
                box-shadow:0 8px 20px rgba(255, 77, 109, 0.4);
                z-index:2;
            }
            section[data-screen-section="results"] .rate {
                margin-top:26px;
                background:var(--sf);
                border:1px solid var(--ln);
                border-radius:20px;
                padding:16px;
                text-align:center;
            }
            section[data-screen-section="results"] .rate>small {
                color:var(--mu);
                font-size:13px;
            }
            section[data-screen-section="results"] .stars {
                display:flex;
                justify-content:center;
                gap:4px;
                margin:6px 0 2px;
            }
            section[data-screen-section="results"] .stars button {
                font-size:38px;
                line-height:1;
                color:var(--ln);
                transition:transform .15s, color .15s;
            }
            section[data-screen-section="results"] .stars button.on {
                color:var(--amb);
            }
            section[data-screen-section="results"] .stars button:hover {
                transform:scale(1.2);
            }
            section[data-screen-section="results"] .stars+small {
                color:var(--lime);
                font-weight:700;
                font-size:13px;
            }
            section[data-screen-section="results"] .hd {
                display:flex;
                justify-content:space-between;
                align-items:baseline;
                margin-bottom:14px;
            }
            section[data-screen-section="results"] .hd h2 {
                margin:0;
                font:800 24px var(--ser);
            }
            section[data-screen-section="results"] .hd small {
                color:var(--mu);
            }
            section[data-screen-section="results"] .lb {
                display:grid;
                gap:12px;
            }
            section[data-screen-section="results"] .rw {
                position:relative;
                display:grid;
                grid-template-columns:44px 56px 1fr auto;
                gap:16px;
                align-items:center;
                padding:16px 20px;
                border-radius:20px;
                background:var(--sf);
                border:1.5px solid var(--ln);
                overflow:hidden;
            }
            section[data-screen-section="results"] .rw.t1 {
                border-color:var(--amb);
                background:linear-gradient(120deg, #33290f, var(--sf) 60%);
            }
            section[data-screen-section="results"] .rk {
                font:900 28px var(--ser);
                color:var(--mu);
                text-align:center;
            }
            section[data-screen-section="results"] .t1 .rk {
                color:var(--amb);
            }
            section[data-screen-section="results"] .av {
                width:56px;
                height:56px;
                border-radius:16px;
                display:grid;
                place-items:center;
                font-size:28px;
                background:var(--sf2);
                overflow:hidden;
            }
            section[data-screen-section="results"] .t1 .av {
                background:#4a3a10;
            }
            section[data-screen-section="results"] .nm {
                font:800 20px var(--ser);
                display:flex;
                gap:10px;
                align-items:center;
                flex-wrap:wrap;
            }
            section[data-screen-section="results"] .tag {
                font:700 11px var(--sans);
                padding:3px 10px;
                border-radius:7px;
                background:var(--lime);
                color:#14200A;
            }
            section[data-screen-section="results"] .tag.l {
                background:var(--lav);
                color:#14200A;
            }
            section[data-screen-section="results"] .tag.r {
                background:var(--red);
                color:#fff;
            }
            section[data-screen-section="results"] .sub {
                color:var(--mu);
                font-size:13px;
                margin:2px 0 8px;
            }
            section[data-screen-section="results"] .bar {
                height:6px;
                border-radius:99px;
                background:#0C0C1F;
                overflow:hidden;
            }
            section[data-screen-section="results"] .bar i {
                display:block;
                height:100%;
                width:0;
                background:var(--lime);
                border-radius:99px;
                transition:width 1s cubic-bezier(.2,.8,.2,1);
            }
            section[data-screen-section="results"] .pt {
                text-align:right;
            }
            section[data-screen-section="results"] .pt b {
                display:block;
                font:900 32px var(--ser);
                color:var(--lime);
            }
            section[data-screen-section="results"] .pt small {
                color:var(--mu);
                font-size:12px;
            }
            section[data-screen-section="results"] .aw {
                display:flex;
                gap:10px;
                flex-wrap:wrap;
                margin-top:16px;
            }
            section[data-screen-section="results"] .aw span {
                background:#0C0C1F;
                border:1px solid var(--ln);
                border-radius:14px;
                padding:9px 14px;
                font-size:13px;
            }
            section[data-screen-section="results"] .aw b {
                color:var(--amb);
            }
            section[data-screen-section="results"] .bt {
                position:fixed;
                left:0;
                right:0;
                bottom:0;
                padding:14px 20px calc(14px + env(safe-area-inset-bottom, 0px));
                background:#0C0C1Ff2;
                backdrop-filter: blur(8px);
                border-top:1px solid var(--ln);
                display:flex;
                flex-direction:column;
                align-items:center;
                gap:10px;
                z-index:30;
            }
            section[data-screen-section="results"] .nx {
                position:relative;
                overflow:hidden;
                width:min(560px, 100%);
                padding:16px;
                border-radius:16px;
                background:var(--lime);
                color:#14200A;
                font:900 18px var(--ser);
                box-shadow:0 10px 30px rgba(189, 244, 84, 0.22);
                transition:transform .15s;
            }
            section[data-screen-section="results"] .nx:hover {
                transform:translateY(-2px);
            }
            section[data-screen-section="results"] .nx i {
                position:absolute;
                left:0;
                bottom:0;
                height:5px;
                width:100%;
                background:rgba(20, 32, 10, 0.25);
                transform-origin:left;
                transition:transform 1s linear;
            }
            section[data-screen-section="results"] .sec {
                display:flex;
                gap:10px;
                align-items:center;
                flex-wrap:wrap;
                justify-content:center;
            }
            section[data-screen-section="results"] .sec input {
                background:var(--sf);
                border:1.5px solid var(--ln);
                border-radius:12px;
                padding:9px 14px;
                font-size:13px;
                width:220px;
                color: var(--tx);
            }
            section[data-screen-section="results"] .sec input:focus {
                border-color:var(--lime);
                outline:0;
            }
            section[data-screen-section="results"] .sec button {
                padding:9px 16px;
                border-radius:12px;
                background:var(--sf2);
                font-weight:700;
                font-size:13px;
                transition: background .15s;
            }
            section[data-screen-section="results"] .sec button:hover {
                background:var(--ln);
            }
            section[data-screen-section="results"] .sec button:disabled {
                background:var(--lav);
                color:#14200A;
                cursor: default;
            }
            @media(max-width:899px){
                section[data-screen-section="results"] .cols {
                    grid-template-columns:1fr;
                    gap:30px;
                }
                section[data-screen-section="results"] .rw {
                    grid-template-columns:34px 46px 1fr;
                }
                section[data-screen-section="results"] .pt {
                    grid-column:1/-1;
                    text-align:left;
                }
                section[data-screen-section="results"] .stamp {
                    right:-8px;
                }
            }
        </style>

        <div class="wrap">
            <section class="hero">
                <span class="chip" id="results-round-badge">● Ronde 1 dari 5 selesai</span>
                <h1>RONDE SELESAI!</h1>
                <div class="word">
                    <small>Kata rahasia</small>
                    <b id="results-secret-word">KUCING NAIK HELIKOPTER</b>
                </div>
                <div class="stats">
                    <span>✅ <b id="results-guessed-count">0 / 0</b> menebak</span>
                    <span>⚡ Tebakan tercepat <b id="results-fastest-guess">-</b></span>
                    <span>💥 Chaos <b id="results-chaos-pct">0%</b></span>
                </div>
            </section>

            <div class="cols">
                <div>
                    <div class="pol">
                        <span class="stamp" id="results-stamp">SABOTAGED!</span>
                        <canvas id="results-polaroid-canvas" width="800" height="500" role="img" aria-label="Gambar hasil ronde"></canvas>
                        <div class="cap">
                            <span id="results-artist-label">Pelukis: -</span>
                            <button class="rp" id="btn-replay-polaroid-timelapse" type="button">▶ Replay panik</button>
                        </div>
                    </div>
                    <div class="rate">
                        <small>Beri bintang untuk gambar ini</small>
                        <div class="stars" id="results-star-buttons">
                            <button type="button" data-rate-star="1" aria-label="1 bintang">★</button>
                            <button type="button" data-rate-star="2" aria-label="2 bintang">★</button>
                            <button type="button" data-rate-star="3" aria-label="3 bintang">★</button>
                            <button type="button" data-rate-star="4" aria-label="4 bintang">★</button>
                            <button type="button" data-rate-star="5" aria-label="5 bintang">★</button>
                        </div>
                        <small id="results-rating-summary">Belum ada rating</small>
                    </div>
                </div>

                <div>
                    <div class="hd">
                        <h2>Top skor ronde ini</h2>
                        <small>Skor real-time server</small>
                    </div>
                    <div class="lb" id="results-top-scorers-list"></div>
                    <div class="aw" id="results-awards-row">
                        <span>🏎️ Penebak tercepat <b id="results-fastest-player">-</b></span>
                        <span>💥 Raja sabotase <b id="results-sabotage-king">-</b></span>
                        <span>🎨 Gambar terlucu <b id="results-artist-award">-</b></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bt">
            <button class="nx" id="btn-results-next-round" type="button">
                ▶ Ronde berikutnya <span style="font-weight:600;font-size:13px">(giliran pelukis berganti)</span>
                <i id="results-countdown-bar"></i>
            </button>
            <div class="sec">
                <input id="input-museum-custom-title" maxlength="40" placeholder="Judul karya..." aria-label="Judul karya">
                <button id="btn-save-hall-of-shame" type="button">🖼️ Simpan ke Museum</button>
                <button id="btn-download-wa-sticker" type="button">⬇ Unduh gambar</button>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- SCREEN 8: HALL OF SHAME MUSEUM                                        -->
    <!-- ===================================================================== -->
    <section data-screen-section="museum" class="hidden relative z-10 flex-1 flex flex-col justify-between px-4 sm:px-6 py-7 max-w-7xl mx-auto w-full">
        <div class="flex flex-wrap items-end justify-between gap-4 pb-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#1A1A2D] border border-[#333347] text-[10px] font-extrabold uppercase tracking-widest text-[#BDF454] mb-2">
                    <span>GALERI KARYA PEMAIN ASLI</span>
                    <span class="text-[#8D937D]">• Tersimpan di Database</span>
                </div>
                <h1 class="font-serif font-black text-4xl sm:text-5xl text-[#E2E0FB]">Hall of Shame</h1>
                <p class="text-sm text-[#C3C9B1] mt-1">Koleksi lukisan hasil pertandingan nyata lengkap dengan tingkat Chaos % dan rating bintang.</p>
            </div>

            <div class="flex items-center gap-1.5 bg-[#1A1A2D] border border-[#333347] rounded-full p-1 text-xs font-bold">
                <button type="button" data-museum-filter="curator" class="px-4 py-2 rounded-full text-[#C3C9B1] hover:text-white transition cursor-pointer">
                    Pilihan Kurator
                </button>
                <button type="button" data-museum-filter="favorite" class="px-4 py-2 rounded-full text-[#C3C9B1] hover:text-white transition cursor-pointer">
                    Terfavorit
                </button>
                <button type="button" data-museum-filter="newest" class="px-4 py-2 rounded-full bg-[#BDF454] text-[#14200A] shadow transition cursor-pointer">
                    Terbaru
                </button>
            </div>
        </div>

        <div id="museum-gallery-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 my-auto"></div>

        <div class="mt-8 pt-4 border-t border-[#333347]/70 flex flex-wrap items-center justify-between gap-4 text-xs text-[#C3C9B1]">
            <span id="museum-count-label">Menampilkan 0 karya asli dari database</span>
            <button type="button" data-nav-view="home" class="px-5 py-2.5 rounded-xl bg-[#1A1A2D] hover:bg-[#28283C] border border-[#333347] text-[#E2E0FB] font-bold transition cursor-pointer">
                Mulai Pertandingan Baru →
            </button>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- MODAL 1: PENGATURAN & BUAT LOBBY BARU V2                              -->
    <!-- ===================================================================== -->
    <div id="modal-room-settings" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <style>
            #modal-room-settings .room-modal-container {
                width: 100%;
                max-width: 780px;
                background: #1A1A2D;
                border: 1px solid #333347;
                border-radius: 26px;
                color: #E2E0FB;
                font-family: 'DM Sans', system-ui, sans-serif;
                overflow: hidden;
                box-shadow: 0 28px 90px rgba(0, 0, 0, .68);
                margin: auto;
            }
            #modal-room-settings .hd {
                padding: 20px 26px 14px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
            }
            #modal-room-settings .hd h1 {
                margin: 0;
                font-family: 'Fraunces', Georgia, serif;
                font-weight: 800;
                font-size: 24px;
                letter-spacing: -.01em;
                color: #E2E0FB;
            }
            #modal-room-settings .hd p {
                margin: 3px 0 0;
                font-size: 13px;
                color: #C3C9B1;
            }
            #modal-room-settings .hr {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            #modal-room-settings .code {
                padding: 7px 14px;
                border-radius: 999px;
                background: #28283C;
                border: 1px solid #333347;
                font: 700 13px 'DM Sans', sans-serif;
                letter-spacing: .16em;
                color: #BDF454;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                cursor: pointer;
            }
            #modal-room-settings .code span:last-child {
                font-size: 11px;
                letter-spacing: 0;
                color: #C3C9B1;
                font-weight: 600;
            }
            #modal-room-settings .x {
                width: 34px;
                height: 34px;
                border-radius: 50%;
                background: #28283C;
                color: #C3C9B1;
                display: grid;
                place-items: center;
                font-size: 20px;
                cursor: pointer;
                transition: .15s;
            }
            #modal-room-settings .x:hover {
                background: #333347;
                color: #fff;
            }
            #modal-room-settings .sum {
                margin: 0 26px;
                padding: 10px 14px;
                border-radius: 14px;
                background: #0C0C1F;
                border: 1px solid #333347;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 7px;
                font-size: 12px;
            }
            #modal-room-settings .sum b {
                background: #28283C;
                padding: 3px 9px;
                border-radius: 999px;
                font-weight: 700;
                color: #E2E0FB;
            }
            #modal-room-settings .sum em {
                margin-left: auto;
                font-style: normal;
                color: #BDF454;
                font-weight: 800;
                font-size: 12px;
            }
            #modal-room-settings .modal-body {
                padding: 18px 26px;
                max-height: 62vh;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 18px;
            }
            #modal-room-settings .sec h2 {
                margin: 0 0 9px;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: .12em;
                text-transform: uppercase;
                color: #C3C9B1;
            }
            #modal-room-settings .pre {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 9px;
            }
            #modal-room-settings .p {
                background: #0C0C1F;
                border: 1.5px solid #333347;
                border-radius: 14px;
                padding: 12px 13px;
                text-align: left;
                cursor: pointer;
                transition: .16s;
            }
            #modal-room-settings .p:hover {
                border-color: #4A4A66;
            }
            #modal-room-settings .p i {
                font-style: normal;
                font-size: 18px;
                display: block;
                margin-bottom: 4px;
            }
            #modal-room-settings .p b {
                display: block;
                font-size: 14px;
                font-weight: 800;
                color: #E2E0FB;
            }
            #modal-room-settings .p small {
                display: block;
                margin-top: 2px;
                font-size: 11px;
                color: #C3C9B1;
            }
            #modal-room-settings .p[aria-pressed="true"] {
                background: #28283C;
                border-color: #BDF454;
            }
            #modal-room-settings .p[aria-pressed="true"] b {
                color: #BDF454;
            }
            #modal-room-settings .row {
                display: grid;
                grid-template-columns: 165px 1fr;
                gap: 14px;
                align-items: start;
                padding-top: 16px;
                border-top: 1px solid #333347;
            }
            #modal-room-settings .lb b {
                display: block;
                font-size: 13.5px;
                font-weight: 800;
                color: #E2E0FB;
            }
            #modal-room-settings .lb small {
                display: block;
                margin-top: 2px;
                font-size: 11.5px;
                color: #C3C9B1;
            }
            #modal-room-settings .grp {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(96px, 1fr));
                gap: 8px;
            }
            #modal-room-settings .grp.two {
                grid-template-columns: 1fr 1fr;
            }
            #modal-room-settings .grp.three {
                grid-template-columns: repeat(3, 1fr);
            }
            #modal-room-settings .grp.packs {
                grid-template-columns: repeat(4, 1fr);
            }
            #modal-room-settings .o {
                background: #0C0C1F;
                border: 1.5px solid #333347;
                border-radius: 14px;
                padding: 10px 11px;
                text-align: left;
                cursor: pointer;
                transition: .15s;
            }
            #modal-room-settings .o:hover {
                border-color: #4A4A66;
            }
            #modal-room-settings .o .n {
                display: block;
                font-family: 'Fraunces', serif;
                font-weight: 800;
                font-size: 20px;
                line-height: 1.05;
                color: #E2E0FB;
            }
            #modal-room-settings .o .n sub {
                font: 700 11px 'DM Sans', sans-serif;
                color: #C3C9B1;
                margin-left: 2px;
                vertical-align: baseline;
            }
            #modal-room-settings .o b {
                display: block;
                font-size: 13px;
                font-weight: 800;
                color: #E2E0FB;
            }
            #modal-room-settings .o small {
                display: block;
                margin-top: 2px;
                font-size: 11px;
                color: #C3C9B1;
            }
            #modal-room-settings .o i {
                font-style: normal;
                font-size: 19px;
                display: block;
                margin-bottom: 3px;
            }
            #modal-room-settings .o[aria-pressed="true"] {
                background: #BDF454;
                border-color: #BDF454;
                color: #14200A;
            }
            #modal-room-settings .o[aria-pressed="true"] .n,
            #modal-room-settings .o[aria-pressed="true"] .n sub,
            #modal-room-settings .o[aria-pressed="true"] b,
            #modal-room-settings .o[aria-pressed="true"] small {
                color: #14200A;
            }
            #modal-room-settings .seats {
                display: flex;
                gap: 5px;
                margin-top: 8px;
                flex-wrap: wrap;
            }
            #modal-room-settings .seats span {
                width: 14px;
                height: 14px;
                border-radius: 50%;
                background: #28283C;
                transition: .2s;
            }
            #modal-room-settings .seats span.on {
                background: #BDF454;
            }
            #modal-room-settings .cards {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                margin-top: 8px;
                transition: .2s;
            }
            #modal-room-settings .cards span {
                padding: 4px 10px;
                border-radius: 999px;
                background: #28283C;
                border: 1px solid #333347;
                font-size: 11.5px;
                color: #E2E0FB;
            }
            #modal-room-settings .cards.off {
                opacity: .3;
                text-decoration: line-through;
            }
            #modal-room-settings .ft {
                padding: 15px 26px;
                background: #0C0C1F;
                border-top: 1px solid #333347;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }
            #modal-room-settings .ft p {
                margin: 0;
                font-size: 12px;
                color: #C3C9B1;
            }
            #modal-room-settings .ft > div {
                display: flex;
                gap: 10px;
            }
            #modal-room-settings .cancel {
                padding: 0 18px;
                height: 44px;
                border-radius: 13px;
                background: #28283C;
                color: #C3C9B1;
                font-weight: 700;
                font-size: 13.5px;
                cursor: pointer;
            }
            #modal-room-settings .save {
                padding: 0 22px;
                height: 44px;
                border-radius: 13px;
                background: #BDF454;
                color: #14200A;
                font-family: 'Fraunces', serif;
                font-weight: 800;
                font-size: 15px;
                cursor: pointer;
                transition: .15s;
            }
            #modal-room-settings .save:hover {
                transform: translateY(-1px);
            }
            #modal-room-settings .save.ok {
                background: #28283C;
                color: #BDF454;
            }
            @media (max-width: 640px) {
                #modal-room-settings .row { grid-template-columns: 1fr; }
                #modal-room-settings .pre,
                #modal-room-settings .grp.three { grid-template-columns: 1fr; }
                #modal-room-settings .grp.packs { grid-template-columns: repeat(2, 1fr); }
                #modal-room-settings .sum em { margin-left: 0; width: 100%; }
            }
        </style>

        <main class="room-modal-container" aria-labelledby="modal-room-settings-title">
            <header class="hd">
                <div>
                    <h1 id="modal-room-settings-title">PENGATURAN LOBBY BARU</h1>
                    <p id="modal-room-settings-subtitle">Atur kapasitas, durasi, &amp; mode chaos sebelum arena dimulai.</p>
                </div>
                <div class="hr">
                    <button class="code" id="btn-modal-copy-code" type="button" aria-label="Salin kode room">
                        <span id="modal-room-code-text">CHAO</span><span id="modal-room-code-sub">salin</span>
                    </button>
                    <button class="x" id="btn-close-room-settings-modal" type="button" aria-label="Tutup">×</button>
                </div>
            </header>
            <div class="sum" id="sum" aria-live="polite"></div>
            <div class="modal-body">
                <section class="sec">
                    <h2>Mulai cepat dengan preset</h2>
                    <div class="pre">
                        <button class="p" type="button" data-p="std">
                            <i>⚡</i><b>Chaos Klasik</b>
                            <small>Publik · 60 dtk · 5 ronde · Energi normal</small>
                        </button>
                        <button class="p" type="button" data-p="extreme">
                            <i>😈</i><b>Kutukan Ekstrem</b>
                            <small>Publik · 45 dtk · 8 ronde · Regen 2x cepat</small>
                        </button>
                        <button class="p" type="button" data-p="chill">
                            <i>☁️</i><b>Mode Santai</b>
                            <small>Privat · 80 dtk · 3 ronde · Tanpa kutukan</small>
                        </button>
                    </div>
                </section>

                <section class="row" id="container-setting-custom-code">
                    <div class="lb">
                        <b>Kode kustom</b>
                        <small>Opsional 4 karakter</small>
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            id="input-setting-custom-code"
                            type="text"
                            maxlength="4"
                            placeholder="Contoh: CHAO (Kosong = Acak)"
                            class="flex-1 bg-[#0C0C1F] border border-[#333347] focus:border-[#BDF454] rounded-xl px-3.5 py-2 text-sm font-bold uppercase tracking-widest text-[#BDF454] placeholder-[#8D937D] focus:outline-none"
                        >
                        <button id="btn-randomize-custom-code" type="button" class="px-3.5 py-2 rounded-xl bg-[#28283C] hover:bg-[#333347] border border-[#333347] text-xs font-bold text-[#D1BCFF] transition cursor-pointer">
                            🎲 Acak
                        </button>
                    </div>
                </section>

                <section class="row">
                    <div class="lb">
                        <b>Privasi Lobby</b>
                        <small>Akses daftar room publik</small>
                    </div>
                    <div class="grp two">
                        <button class="o" type="button" data-k="priv" data-v="pub">
                            <b>🌐 Publik</b>
                            <small>Tampil di Hub &amp; Quick Play</small>
                        </button>
                        <button class="o" type="button" data-k="priv" data-v="priv">
                            <b>🔒 Privat</b>
                            <small>Masuk lewat kode atau link</small>
                        </button>
                    </div>
                </section>

                <section class="row">
                    <div class="lb">
                        <b>Mode Permainan</b>
                        <small>Aturan sabotase kanvas</small>
                    </div>
                    <div>
                        <div class="grp three">
                            <button class="o" type="button" data-k="chaos_mode" data-v="classic">
                                <b>⚡ Chaos Klasik</b>
                                <small>Sabotase normal (+3/dtk)</small>
                            </button>
                            <button class="o" type="button" data-k="chaos_mode" data-v="chill">
                                <b>🕊️ Mode Santai</b>
                                <small>Gambar &amp; tebak murni</small>
                            </button>
                            <button class="o" type="button" data-k="chaos_mode" data-v="extreme">
                                <b>💥 Kutukan Ekstrem</b>
                                <small>Regen Chaos Energy 2x!</small>
                            </button>
                        </div>
                        <div class="cards" id="cards">
                            <span>🌋 Gempa</span>
                            <span>🌀 Kuas mabuk</span>
                            <span>💡 Lampu mati</span>
                            <span>💣 Stiker bom</span>
                        </div>
                    </div>
                </section>

                <section class="row">
                    <div class="lb">
                        <b>Waktu menggambar</b>
                        <small>Detik per ronde</small>
                    </div>
                    <div class="grp">
                        <button class="o" type="button" data-k="time" data-v="45"><span class="n">45<sub>dtk</sub></span><small>Panik</small></button>
                        <button class="o" type="button" data-k="time" data-v="60"><span class="n">60<sub>dtk</sub></span><small>Standar</small></button>
                        <button class="o" type="button" data-k="time" data-v="80"><span class="n">80<sub>dtk</sub></span><small>Santai</small></button>
                        <button class="o" type="button" data-k="time" data-v="100"><span class="n">100<sub>dtk</sub></span><small>Master</small></button>
                        <button class="o" type="button" data-k="time" data-v="120"><span class="n">120<sub>dtk</sub></span><small>Detail</small></button>
                    </div>
                </section>

                <section class="row">
                    <div class="lb">
                        <b>Total ronde</b>
                        <small>Giliran tiap pemain</small>
                    </div>
                    <div class="grp three">
                        <button class="o" type="button" data-k="rounds" data-v="3"><span class="n">3<sub>ronde</sub></span><small>Singkat</small></button>
                        <button class="o" type="button" data-k="rounds" data-v="5"><span class="n">5<sub>ronde</sub></span><small>Disarankan</small></button>
                        <button class="o" type="button" data-k="rounds" data-v="8"><span class="n">8<sub>ronde</sub></span><small>Maraton</small></button>
                    </div>
                </section>

                <section class="row">
                    <div class="lb">
                        <b>Maksimal pemain</b>
                        <small>4 sampai 12 kursi</small>
                    </div>
                    <div>
                        <div class="grp">
                            <button class="o" type="button" data-k="max" data-v="4"><span class="n">4</span><small>Squad</small></button>
                            <button class="o" type="button" data-k="max" data-v="6"><span class="n">6</span><small>Seru</small></button>
                            <button class="o" type="button" data-k="max" data-v="8"><span class="n">8</span><small>Standar</small></button>
                            <button class="o" type="button" data-k="max" data-v="12"><span class="n">12</span><small>Pesta</small></button>
                        </div>
                        <div class="seats" id="seats" aria-hidden="true"></div>
                    </div>
                </section>

                <section class="row">
                    <div class="lb">
                        <b>Paket kata rahasia</b>
                        <small>Tema kosakata</small>
                    </div>
                    <div class="grp packs">
                        <button class="o" type="button" data-k="pack" data-v="mix"><i>🌟</i><b>Campuran</b></button>
                        <button class="o" type="button" data-k="pack" data-v="meme"><i>😹</i><b>Meme viral</b></button>
                        <button class="o" type="button" data-k="pack" data-v="kuliner"><i>🍜</i><b>Kuliner Indo</b></button>
                        <button class="o" type="button" data-k="pack" data-v="anime"><i>🎮</i><b>Anime &amp; game</b></button>
                    </div>
                </section>
            </div>
            <footer class="ft">
                <p id="modal-room-settings-footer-hint">Hanya host yang bisa menyimpan perubahan.</p>
                <div>
                    <button class="cancel" id="btn-cancel-room-settings" type="button">Batal</button>
                    <button class="save" id="btn-submit-room-settings" type="button">Simpan perubahan</button>
                </div>
            </footer>
        </main>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL 2: GABUNG KE LOBBY (FIGMA OTP CODE + VERIFICATION + 4 AVATARS)  -->
    <!-- ===================================================================== -->
    <div id="modal-join-lobby" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="w-full max-w-lg bg-[#1A1A2D] border border-[#333347] rounded-3xl p-6 sm:p-7 shadow-2xl my-auto">
            <div class="flex items-start justify-between gap-3 mb-5">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#D1BCFF]">MASUK ARENA PERTANDINGAN</span>
                    <h2 class="mt-0.5 font-serif font-black text-2xl text-[#E2E0FB]">GABUNG KE LOBBY</h2>
                </div>
                <button id="btn-close-join-modal" type="button" class="w-9 h-9 rounded-full bg-[#28283C] hover:bg-[#333347] text-[#C3C9B1] hover:text-white flex items-center justify-center text-lg cursor-pointer" aria-label="Tutup">×</button>
            </div>

            <form id="form-join-lobby-modal" class="space-y-5">
                <!-- 4 Individual OTP Character Boxes -->
                <div>
                    <span class="block text-xs font-bold text-[#C3C9B1] mb-2">Masukkan 4 Huruf/Angka Kode Room</span>
                    <div class="grid grid-cols-4 gap-3 max-w-xs mx-auto">
                        <input id="join-otp-0" data-join-otp="0" type="text" maxlength="1" aria-label="Digit kode 1" class="h-14 rounded-2xl bg-[#0C0C1F] border-2 border-[#333347] focus:border-[#BDF454] text-center font-serif font-black text-2xl uppercase text-[#BDF454] focus:outline-none">
                        <input id="join-otp-1" data-join-otp="1" type="text" maxlength="1" aria-label="Digit kode 2" class="h-14 rounded-2xl bg-[#0C0C1F] border-2 border-[#333347] focus:border-[#BDF454] text-center font-serif font-black text-2xl uppercase text-[#BDF454] focus:outline-none">
                        <input id="join-otp-2" data-join-otp="2" type="text" maxlength="1" aria-label="Digit kode 3" class="h-14 rounded-2xl bg-[#0C0C1F] border-2 border-[#333347] focus:border-[#BDF454] text-center font-serif font-black text-2xl uppercase text-[#BDF454] focus:outline-none">
                        <input id="join-otp-3" data-join-otp="3" type="text" maxlength="1" aria-label="Digit kode 4" class="h-14 rounded-2xl bg-[#0C0C1F] border-2 border-[#333347] focus:border-[#BDF454] text-center font-serif font-black text-2xl uppercase text-[#BDF454] focus:outline-none">
                    </div>
                    <!-- Live Verification Banner -->
                    <div id="join-room-verify-status" class="mt-3 rounded-xl bg-[#0C0C1F] border border-[#333347] px-3.5 py-2 text-xs text-center text-[#C3C9B1]">
                        Ketik 4 karakter kode room untuk memverifikasi status lobby.
                    </div>
                </div>

                <!-- Nickname -->
                <div>
                    <label for="input-join-modal-nickname" class="block text-xs font-bold text-[#C3C9B1] mb-1.5">Nickname Kamu di Room</label>
                    <div class="flex items-center gap-2">
                        <input
                            id="input-join-modal-nickname"
                            type="text"
                            maxlength="32"
                            placeholder="SenimanRusuh_89"
                            class="flex-1 rounded-xl bg-[#0C0C1F] border border-[#333347] focus:border-[#BDF454] px-4 py-2.5 text-sm font-bold text-[#E2E0FB] focus:outline-none min-w-0"
                        >
                        <button id="btn-join-modal-random-nick" type="button" class="px-3 py-2.5 rounded-xl bg-[#28283C] hover:bg-[#333347] text-xs font-bold text-[#D1BCFF] transition cursor-pointer shrink-0">
                            🎲 Acak
                        </button>
                    </div>
                </div>

                <!-- 4 Vector Character Avatar Cards -->
                <div>
                    <span class="block text-xs font-bold text-[#C3C9B1] mb-2">Pilih Karakter Avatar</span>
                    <div id="join-avatar-picker" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5"></div>
                </div>

                <!-- Footer Live Ping & Arcade Violet Primary CTA -->
                <div class="pt-2 flex flex-col gap-3">
                    <div class="flex items-center justify-between text-[11px] font-bold text-[#8D937D]">
                        <span id="join-modal-ping">📶 Server Ping: -- ms</span>
                        <span>Real-time Sync Ready</span>
                    </div>
                    <button id="btn-submit-join-modal" type="submit" class="w-full py-3.5 rounded-2xl bg-[#5717BC] hover:bg-[#6B25DC] text-white font-extrabold text-sm uppercase tracking-wider shadow-[0_12px_28px_rgba(87,23,188,0.4)] transition cursor-pointer">
                        GABUNG KE LOBBY →
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL 3: PROFIL SENIMAN & AKUN                                        -->
    <!-- ===================================================================== -->
    <div id="modal-player-profile" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="w-full max-w-md bg-[#1A1A2D] border border-[#333347] rounded-3xl p-6 shadow-2xl my-auto">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#BDF454]">IDENTITAS ARENA</span>
                    <h2 class="font-serif font-black text-2xl text-[#E2E0FB]">Profil Seniman</h2>
                </div>
                <button id="btn-close-profile-modal" type="button" class="w-8 h-8 rounded-full bg-[#28283C] hover:bg-[#333347] text-[#C3C9B1] hover:text-white flex items-center justify-center cursor-pointer" aria-label="Tutup">✕</button>
            </div>

            <div class="flex items-center gap-3.5 p-3.5 rounded-2xl bg-[#0C0C1F] border border-[#333347] mb-4">
                <div id="profile-modal-avatar" class="w-12 h-12 rounded-xl bg-[#1A1A2D] border border-[#BDF454]/60 flex items-center justify-center shrink-0 overflow-hidden"></div>
                <div class="min-w-0 flex-1">
                    <div id="profile-modal-auth-label" class="text-[11px] font-extrabold text-[#D1BCFF]">Mode Tamu (Guest Token)</div>
                    <div id="profile-modal-stats" class="text-xs text-[#C3C9B1] mt-0.5">Lv. 1 • 0 Skor • 0 Main</div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="input-profile-nickname" class="block text-xs font-bold text-[#C3C9B1] mb-1.5">Nickname Tampilan</label>
                    <input id="input-profile-nickname" type="text" maxlength="32" class="w-full rounded-xl bg-[#0C0C1F] border border-[#333347] focus:border-[#BDF454] px-3.5 py-2.5 text-sm font-bold text-[#E2E0FB] focus:outline-none">
                </div>

                <div>
                    <span class="block text-xs font-bold text-[#C3C9B1] mb-2">Ganti Karakter Avatar</span>
                    <div id="profile-avatar-picker" class="grid grid-cols-3 sm:grid-cols-6 gap-2"></div>
                </div>

                <div class="p-3 rounded-xl bg-[#0C0C1F] border border-[#333347]">
                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-[#8D937D] mb-1">
                        <span>Session / Guest Token</span>
                        <button id="btn-copy-guest-token" type="button" class="text-[#BDF454] hover:underline cursor-pointer">Salin Token</button>
                    </div>
                    <code id="profile-guest-token-display" class="block text-[11px] text-[#C3C9B1] truncate">...</code>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button id="btn-save-profile" type="button" class="flex-1 py-2.5 rounded-xl bg-[#BDF454] hover:bg-[#ccf970] text-[#14200A] font-extrabold text-xs uppercase tracking-wider transition cursor-pointer">
                        Simpan Profil
                    </button>
                    <button id="btn-profile-open-login" type="button" class="px-4 py-2.5 rounded-xl bg-[#5717BC] hover:bg-[#6B25DC] text-white font-bold text-xs transition cursor-pointer">
                        Masuk / Daftar
                    </button>
                    <button id="btn-profile-logout" type="button" class="hidden px-4 py-2.5 rounded-xl bg-[#3A1628] hover:bg-[#4F1D36] text-[#FFB3C1] font-bold text-xs transition cursor-pointer">
                        Keluar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL 4: PANIC TIME-LAPSE REPLAY                                      -->
    <!-- ===================================================================== -->
    <div id="panic-replay-modal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-[#1A1A2D] border-2 border-[#BDF454] rounded-3xl max-w-lg w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#BDF454]">🎬 TIME-LAPSE REPLAY KEPANIKAN</span>
                    <h3 id="modal-replay-title" class="font-serif font-bold text-xl text-[#E2E0FB]">Replay Lukisan</h3>
                </div>
                <button id="btn-close-replay-modal" type="button" class="w-8 h-8 rounded-full bg-[#28283C] text-[#E2E0FB] font-bold cursor-pointer">✕</button>
            </div>
            <div class="rounded-2xl overflow-hidden border border-[#333347] bg-[#FFF8E9]">
                <canvas id="modal-replay-canvas" width="440" height="300" class="w-full h-auto block"></canvas>
            </div>
            <p id="modal-replay-plaque" class="mt-3 text-center text-xs font-serif italic text-[#D1BCFF]"></p>
        </div>
    </div>

    <!-- Floating Toast Notifications -->
    <div id="chaos-toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm"></div>

    <!-- Hydrate Initial Backend State to JS Engine -->
    <script>
        window.CORET_CHAOS_BOOT = {
            deckCatalog: @json($deckCatalog),
            hallOfShame: @json($hallOfShame),
            publicRooms: @json($publicRooms ?? []),
            initialRoom: @json($initialRoom),
            bootView: @json($bootView ?? null),
            onlineCount: @json($onlineCount ?? 1),
            guestPlayer: @json($guestPlayer ?? null),
            reverbConfig: @json($reverbConfig ?? []),
        };
    </script>
</body>
</html>
