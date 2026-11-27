    <!-- ===================================================================== -->
    <!-- SCREEN 6: GAMEPLAY (60FPS CANVAS, SABOTEUR DECK & LIVE GUESSING)      -->
    <!-- ===================================================================== -->
    <section data-screen-section="gameplay" class="hidden relative z-10 flex-1 flex flex-col justify-between w-full">
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
                    <input id="input-artist-secret-word" maxlength="40" placeholder="Contoh: Apel, Rumah, Kucing" aria-label="Kata rahasia">
                    <button class="set" id="btn-submit-secret-word" type="submit">Tetapkan kata</button>
                </div>
                <div class="er" id="artist-word-error" role="alert"></div>
                <div class="chips" id="artist-word-suggestions">
                    <span>Rekomendasi:</span>
                    <button type="button" data-suggested-word="Apel">Apel</button>
                    <button type="button" data-suggested-word="Rumah">Rumah</button>
                    <button type="button" data-suggested-word="Kucing">Kucing</button>
                    <button type="button" data-suggested-word="Matahari">Matahari</button>
                    <button type="button" data-suggested-word="Mobil">Mobil</button>
                    <button type="button" data-suggested-word="Payung">Payung</button>
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
