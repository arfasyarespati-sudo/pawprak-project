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
