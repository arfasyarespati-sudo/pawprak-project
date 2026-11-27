    <!-- ===================================================================== -->
    <!-- MODAL 1: PENGATURAN & BUAT LOBBY BARU V2                              -->
    <!-- ===================================================================== -->
    <div id="modal-room-settings" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
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
                <div>
                    <span class="block text-xs font-bold text-[#C3C9B1] mb-2">Masukkan 4 Huruf/Angka Kode Room</span>
                    <div class="grid grid-cols-4 gap-3 max-w-xs mx-auto">
                        <input id="join-otp-0" data-join-otp="0" type="text" maxlength="1" aria-label="Digit kode 1" class="h-14 rounded-2xl bg-[#0C0C1F] border-2 border-[#333347] focus:border-[#BDF454] text-center font-serif font-black text-2xl uppercase text-[#BDF454] focus:outline-none">
                        <input id="join-otp-1" data-join-otp="1" type="text" maxlength="1" aria-label="Digit kode 2" class="h-14 rounded-2xl bg-[#0C0C1F] border-2 border-[#333347] focus:border-[#BDF454] text-center font-serif font-black text-2xl uppercase text-[#BDF454] focus:outline-none">
                        <input id="join-otp-2" data-join-otp="2" type="text" maxlength="1" aria-label="Digit kode 3" class="h-14 rounded-2xl bg-[#0C0C1F] border-2 border-[#333347] focus:border-[#BDF454] text-center font-serif font-black text-2xl uppercase text-[#BDF454] focus:outline-none">
                        <input id="join-otp-3" data-join-otp="3" type="text" maxlength="1" aria-label="Digit kode 4" class="h-14 rounded-2xl bg-[#0C0C1F] border-2 border-[#333347] focus:border-[#BDF454] text-center font-serif font-black text-2xl uppercase text-[#BDF454] focus:outline-none">
                    </div>
                    <div id="join-room-verify-status" class="mt-3 rounded-xl bg-[#0C0C1F] border border-[#333347] px-3.5 py-2 text-xs text-center text-[#C3C9B1]">
                        Ketik 4 karakter kode room untuk memverifikasi status lobby.
                    </div>
                </div>

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

                <div>
                    <span class="block text-xs font-bold text-[#C3C9B1] mb-2">Pilih Karakter Avatar</span>
                    <div id="join-avatar-picker" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5"></div>
                </div>

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

    <!-- ===================================================================== -->
    <!-- MODAL 5: NEXT ROUND TRANSITION POP-UP                                 -->
    <!-- ===================================================================== -->
    <div id="next-round-transition-modal" class="hidden fixed inset-0 z-50 bg-[#090916]/80 backdrop-blur-md flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="next-round-modal-title">
        <div class="bg-[#1A1A2D] border-2 border-[#BDF454] rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl text-center space-y-5">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#BDF454]/15 border border-[#BDF454]/40 text-[11px] font-extrabold uppercase tracking-widest text-[#BDF454]" id="next-round-modal-badge">
                🎉 SEMUA BERHASIL MENEBAK!
            </div>

            <div>
                <h2 id="next-round-modal-title" class="font-serif font-black text-2xl sm:text-3xl text-[#E2E0FB]">
                    Lanjut ke Ronde 2
                </h2>
                <p class="text-xs text-[#C3C9B1] mt-1.5">
                    Kata ronde sebelumnya: <strong id="next-round-modal-prev-word" class="text-[#BDF454] uppercase tracking-wider">APEL</strong>
                </p>
            </div>

            <div class="rounded-2xl bg-[#0C0C1F] border border-[#333347] p-4 space-y-2">
                <div class="text-[11px] font-bold uppercase tracking-wider text-[#8D937D]">Giliran Pelukis Berikutnya</div>
                <div id="next-round-modal-next-artist" class="font-serif font-extrabold text-xl text-[#E2E0FB]">
                    Pemain
                </div>
                <div id="next-round-modal-your-role" class="inline-block px-3 py-1 rounded-full bg-[#5717BC]/30 border border-[#D1BCFF]/30 text-xs font-bold text-[#D1BCFF]">
                    Peranmu: Penebak &amp; Saboteur
                </div>
            </div>

            <div class="space-y-3">
                <p id="next-round-modal-countdown" class="text-xs font-bold text-[#BDF454]">
                    Mulai dalam 3 detik...
                </p>
                <button id="btn-dismiss-next-round-modal" type="button" class="w-full py-3 rounded-xl bg-[#BDF454] hover:brightness-110 text-[#14200A] font-extrabold text-xs uppercase tracking-wider transition cursor-pointer">
                    Lanjut Sekarang →
                </button>
            </div>
        </div>
    </div>
