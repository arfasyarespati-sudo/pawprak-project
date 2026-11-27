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
