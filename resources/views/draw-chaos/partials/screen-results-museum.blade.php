    <!-- ===================================================================== -->
    <!-- SCREEN 7: FINAL RESULTS & REMATCH READY SCREEN (CLEAN NO-SCROLL)      -->
    <!-- ===================================================================== -->
    <section data-screen-section="results" class="hidden relative z-10 flex-1 flex items-center justify-center px-4 py-5 w-full">
        <div class="w-full max-w-2xl rounded-3xl bg-[#1A1A2D] border border-[#333347] p-6 sm:p-8 shadow-2xl space-y-5">
            {{-- Header --}}
            <div class="text-center space-y-2">
                <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#0C0C1F] border border-[#333347] text-[11px] font-extrabold uppercase tracking-widest text-[#BDF454]" id="results-round-badge">
                    🏆 PERTANDINGAN SELESAI
                </span>
                <h1 id="results-main-title" class="font-serif font-black text-2xl sm:text-4xl text-[#E2E0FB] leading-tight">
                    RONDE SELESAI!
                </h1>
                <p class="text-xs text-[#C3C9B1]">
                    Kata terakhir: <strong id="results-secret-word" class="text-[#BDF454] uppercase tracking-wider">-</strong>
                </p>
            </div>

            {{-- Leaderboard List --}}
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <h2 id="results-leaderboard-heading" class="text-xs font-extrabold uppercase tracking-widest text-[#D1BCFF]">
                        Leaderboard Skor Akhir
                    </h2>
                    <span class="text-[11px] text-[#8D937D]">Poin Total Pertandingan</span>
                </div>
                <div id="results-top-scorers-list" class="max-h-[38vh] overflow-y-auto chaos-scrollbar space-y-2.5 pr-1"></div>
            </div>

            {{-- Rematch Ready Status --}}
            <div class="rounded-2xl bg-[#0C0C1F] border border-[#333347] px-4 py-3 text-center">
                <p id="results-ready-status-text" class="text-xs font-bold text-[#C3C9B1]">
                    Siap Main Lagi: 0 / 0 Pemain — Otomatis mulai saat semua pemain siap
                </p>
            </div>

            {{-- Action Buttons (Always visible without scrolling) --}}
            <div id="results-final-actions" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <button id="btn-results-play-again" type="button" class="flex-1 py-3.5 px-5 rounded-2xl bg-[#BDF454] hover:brightness-110 text-[#111124] font-extrabold text-xs sm:text-sm tracking-wider uppercase shadow-[0_10px_24px_rgba(189,244,84,0.22)] transition cursor-pointer">
                    ✋ SIAP MAIN LAGI
                </button>
                <button id="btn-results-return-lobby" type="button" class="flex-1 py-3.5 px-5 rounded-2xl bg-[#5717BC] hover:bg-[#6B25DC] border border-[#D1BCFF]/35 text-white font-extrabold text-xs sm:text-sm tracking-wider uppercase transition cursor-pointer">
                    🏠 Kembali ke Lobby
                </button>
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
