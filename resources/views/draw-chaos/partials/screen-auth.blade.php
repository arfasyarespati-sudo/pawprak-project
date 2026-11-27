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
