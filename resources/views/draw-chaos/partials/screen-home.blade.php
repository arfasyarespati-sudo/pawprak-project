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
