<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CORET! CHAOS — Real-Time Multiplayer Drawing & Sabotage</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700;9..144,900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="/socket.io/socket.io.js" onerror="this.onerror=null;"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0D0B14] text-white min-h-screen flex flex-col font-sans antialiased selection:bg-[#B8FF3B] selection:text-[#0D0B14] overflow-x-hidden relative">

    <!-- Ambient Radial Glows -->
    <div class="pointer-events-none fixed -top-36 -left-36 w-[520px] h-[520px] rounded-full bg-[#3B1D7A]/25 blur-[130px] z-0"></div>
    <div class="pointer-events-none fixed -bottom-40 -right-36 w-[540px] h-[540px] rounded-full bg-[#4E6E18]/20 blur-[140px] z-0"></div>

    <!-- ===================================================================== -->
    <!-- TOP GLOBAL NAVBAR                                                     -->
    <!-- ===================================================================== -->
    <header class="relative z-30 w-full px-6 py-4 flex items-center justify-between border-b border-white/5 bg-[#0D0B14]/80 backdrop-blur-md">
        <!-- Brand Logo -->
        <button type="button" data-nav-view="home" class="flex items-center gap-2.5 text-left group cursor-pointer">
            <span class="text-lg opacity-85 group-hover:scale-110 transition">🐾</span>
            <span class="font-serif font-extrabold tracking-wider text-base sm:text-lg text-white">CORET! CHAOS</span>
        </button>

        <!-- Center Pill Switcher -->
        <nav class="hidden md:flex items-center gap-1 bg-[#161421] border border-[#28243B] rounded-full p-1 text-xs font-medium">
            <button type="button" data-nav-view="home" class="px-3.5 py-1.5 rounded-full bg-[#28243D] text-white shadow-sm transition cursor-pointer">Home</button>
            <button type="button" data-nav-view="lobby" class="px-3.5 py-1.5 rounded-full text-[#948FB1] hover:text-white transition cursor-pointer">Lobby</button>
            <button type="button" data-nav-view="game" class="px-3.5 py-1.5 rounded-full text-[#948FB1] hover:text-white transition cursor-pointer">Game</button>
            <button type="button" data-nav-view="results" class="px-3.5 py-1.5 rounded-full text-[#948FB1] hover:text-white transition cursor-pointer">Results</button>
            <button type="button" data-nav-view="museum" class="px-3.5 py-1.5 rounded-full text-[#948FB1] hover:text-white transition cursor-pointer">Museum</button>
        </nav>

        <!-- Right Status & Real Ping Controls -->
        <div class="flex items-center gap-2.5">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#181624] border border-[#2B2740] text-[11px] font-bold tracking-wider uppercase text-white">
                <span id="nav-connection-dot" class="w-2 h-2 rounded-full bg-[#B8FF3B] animate-pulse"></span>
                <span id="nav-ping-badge">-- ms</span>
            </div>
            <button id="btn-toggle-sound" type="button" class="w-8 h-8 rounded-full bg-[#181624] border border-[#2B2740] flex items-center justify-center text-xs text-[#A39EC0] hover:text-white transition cursor-pointer" title="Toggle Sound FX">
                🔊
            </button>
            <button id="btn-toggle-fullscreen" type="button" class="w-8 h-8 rounded-full bg-[#181624] border border-[#2B2740] flex items-center justify-center text-xs text-[#A39EC0] hover:text-white transition cursor-pointer" title="Fullscreen">
                ⛶
            </button>
        </div>
    </header>

    <!-- Mobile View Switcher Bar -->
    <div class="flex md:hidden items-center justify-center gap-1 px-4 py-2 bg-[#13111C] border-b border-white/5 overflow-x-auto z-20">
        <button type="button" data-nav-view="home" class="px-3 py-1 rounded-full text-xs bg-[#28243D] text-white">Home</button>
        <button type="button" data-nav-view="lobby" class="px-3 py-1 rounded-full text-xs text-[#948FB1]">Lobby</button>
        <button type="button" data-nav-view="game" class="px-3 py-1 rounded-full text-xs text-[#948FB1]">Game</button>
        <button type="button" data-nav-view="results" class="px-3 py-1 rounded-full text-xs text-[#948FB1]">Results</button>
        <button type="button" data-nav-view="museum" class="px-3 py-1 rounded-full text-xs text-[#948FB1]">Museum</button>
    </div>

    <!-- ===================================================================== -->
    <!-- SCREEN 1: LANDING PAGE (REAL ONLINE COUNT & QUICK PLAY)               -->
    <!-- ===================================================================== -->
    <section data-screen-section="home" class="relative z-10 flex-1 flex flex-col justify-between px-6 py-8 max-w-6xl mx-auto w-full">
        <!-- Real-Time Active Connected Players Counter Pill -->
        <div class="flex justify-center mt-2">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#171524] border border-[#29253E] text-[11px] font-bold tracking-widest uppercase text-[#D5D1EA]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#B8FF3B] animate-pulse"></span>
                <span id="online-players-counter">{{ $onlineCount ?? 1 }} SENIMAN SEDANG ONLINE</span>
            </div>
        </div>

        <!-- Center Hero Block -->
        <div class="relative my-auto py-10 flex flex-col items-center text-center max-w-2xl mx-auto">

            <!-- Floating Tilted SABOTEUR Card -->
            <div class="hidden sm:flex flex-col items-center justify-between w-24 h-28 rounded-2xl bg-[#1C192B] border border-[#342F4E] p-3 shadow-2xl animate-float-card absolute -top-6 right-4 md:right-10">
                <div class="text-3xl leading-none mt-1 drop-shadow-[0_0_10px_rgba(184,255,59,0.7)]">😼✨</div>
                <span class="px-2 py-0.5 rounded-md bg-[#E11D48] text-[9px] font-extrabold tracking-widest uppercase text-white shadow">SABOTEUR</span>
            </div>

            <!-- Party Drawing Mayhem Tag -->
            <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-[#1B192B] border border-[#2E2A45] text-[11px] font-extrabold tracking-widest uppercase text-[#B8FF3B] mb-5">
                <span>⚡</span>
                <span>PARTY DRAWING MAYHEM</span>
            </div>

            <!-- Main Display Serif Headline -->
            <h1 class="font-serif font-black text-5xl sm:text-6xl md:text-7xl tracking-tight leading-[1.06] text-white">
                GAMBAR. TEBAK.
                <span class="block mt-1 italic text-[#B8FF3B] relative inline-block">
                    RUSUH.
                    <svg class="w-56 sm:w-72 h-3.5 -mt-1 mx-auto text-white" viewBox="0 0 240 14" fill="none">
                        <path d="M4 9.5C68 4.5 154 4.5 236 8.5" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
                    </svg>
                </span>
            </h1>

            <!-- Hero Subtitle -->
            <p class="mt-6 text-sm sm:text-base text-[#B6B1CE] max-w-lg leading-relaxed font-serif">
                Game gambar multiplayer real-time bersama teman, lengkap dengan sabotase kartu kutukan dan tebakan kata bebas.
            </p>

            <!-- Zero-Auth Guest Nickname Input Bar -->
            <div class="mt-6 w-full max-w-md bg-[#181625] border border-[#2C2842] rounded-2xl px-3.5 py-2 flex items-center justify-between gap-2 shadow-inner">
                <div class="flex items-center gap-2.5 flex-1">
                    <span class="text-sm" title="Avatar Pemain">🎭</span>
                    <div class="flex flex-col text-left flex-1">
                        <label for="input-landing-nickname" class="text-[9px] font-extrabold uppercase tracking-widest text-[#8E88A8]">NICKNAME PEMAIN</label>
                        <input
                            id="input-landing-nickname"
                            type="text"
                            maxlength="50"
                            value="{{ $guestPlayer['nickname'] ?? 'SenimanRusuh_89' }}"
                            placeholder="Ketik nickname kamu..."
                            class="w-full bg-transparent text-xs sm:text-sm font-bold text-[#B8FF3B] placeholder-[#676182] focus:outline-none"
                        >
                    </div>
                </div>
                <button id="btn-randomize-nickname" type="button" class="px-2.5 py-1.5 rounded-xl bg-[#27233A] hover:bg-[#352F4D] text-[11px] font-bold text-[#D5D1EA] transition cursor-pointer" title="Acak Nickname">
                    🎲 Acak
                </button>
            </div>

            <!-- Primary CTA Buttons: QUICK PLAY, BUAT ROOM & GABUNG -->
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3 w-full">
                <button id="btn-landing-quick-play" type="button" class="w-44 py-4 px-5 rounded-2xl bg-[#B8FF3B] hover:bg-[#c6ff61] text-[#0E0D15] font-extrabold text-xs sm:text-sm tracking-wider uppercase shadow-[0_10px_30px_rgba(184,255,59,0.25)] flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5 cursor-pointer">
                    <span class="text-base">⚡</span>
                    <span>QUICK PLAY</span>
                </button>
                <button id="btn-landing-create-room" type="button" class="w-44 py-4 px-5 rounded-2xl bg-[#242038] hover:bg-[#302B4A] border border-[#3E375E] text-white font-extrabold text-xs sm:text-sm tracking-wider uppercase flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5 cursor-pointer">
                    <span class="text-base text-[#B8FF3B]">⊕</span>
                    <span>BUAT ROOM</span>
                </button>
                <button id="btn-landing-join-focus" type="button" class="w-40 py-4 px-5 rounded-2xl bg-[#C8B6FF] hover:bg-[#d6c8ff] text-[#0E0D15] font-extrabold text-xs sm:text-sm tracking-wider uppercase shadow-[0_10px_30px_rgba(200,182,255,0.2)] flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5 cursor-pointer">
                    <span>➔]</span>
                    <span>GABUNG</span>
                </button>
            </div>

            <!-- 4-Letter Room Code Form Bar -->
            <form id="form-landing-join-room" class="mt-5 w-full max-w-md bg-[#181625] border border-[#2C2842] rounded-2xl p-2 flex items-center justify-between gap-2 shadow-inner">
                <div class="flex items-center gap-2.5 pl-3 flex-1">
                    <span class="text-xs text-[#8E88A8]">🔑</span>
                    <input
                        id="input-landing-room-code"
                        type="text"
                        maxlength="4"
                        placeholder="MASUKKAN KODE ROOM (4 HURUF)"
                        class="w-full bg-transparent text-xs sm:text-sm font-bold tracking-[0.22em] uppercase text-white placeholder-[#676182] focus:outline-none"
                    >
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#27233A] hover:bg-[#B8FF3B] hover:text-[#0E0D15] text-white text-xs font-extrabold tracking-wider uppercase transition cursor-pointer">
                    MASUK →
                </button>
            </form>
        </div>

        <!-- Bottom Footer Metadata (Real Dynamic Host & Measured RTT Ping) -->
        <div class="flex flex-wrap items-center justify-between gap-4 text-[11px] text-[#6E6888] pt-4 border-t border-white/5">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#A855F7]"></span>
                <span>CORET! CHAOS • Real-Time Multiplayer</span>
            </div>
            <div class="flex items-center gap-3">
                <span id="footer-server-host">🌐 Server Aktif</span>
                <span>•</span>
                <span id="footer-ping-display">📶 Mengukur Ping...</span>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- SCREEN 2: MULTIPLAYER LOBBY (REDESIGNED + WITH KELUAR FEATURE)        -->
    <!-- ===================================================================== -->
    <section data-screen-section="lobby" class="hidden relative z-10 flex-1 flex flex-col justify-between w-full">
        <style>
            section[data-screen-section="lobby"] {
                --bg:#0f1020;
                --sf:#1d1e33;
                --sf2:#26273f;
                --ln:#2e3050;
                --lime:#c2f04e;
                --lav:#c9b8ff;
                --tx:#e8e9fb;
                --mu:#8b8ead;
                --ser:'Fraunces',Georgia,serif;
                font-family: var(--ser);
                color: var(--tx);
                min-height: calc(100vh - 76px);
                box-sizing: border-box;
            }
            section[data-screen-section="lobby"] button {
                font: inherit;
                color: inherit;
                cursor: pointer;
                border: 0;
                background: none;
            }
            section[data-screen-section="lobby"] button:focus-visible {
                outline: 2px solid var(--lime);
                outline-offset: 3px;
            }
            section[data-screen-section="lobby"] svg {
                width: 1em;
                height: 1em;
                stroke: currentColor;
                fill: none;
                stroke-width: 2;
                stroke-linecap: round;
                stroke-linejoin: round;
                vertical-align: -.12em;
            }
            section[data-screen-section="lobby"] .top {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
                padding: 24px 32px 0;
            }
            section[data-screen-section="lobby"] .pill {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                background: var(--sf);
                border-radius: 99px;
                padding: 9px 18px;
                font-size: 12px;
                font-weight: 800;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: var(--tx);
            }
            section[data-screen-section="lobby"] .pill small {
                font-size: 11px;
                color: var(--mu);
                letter-spacing: .1em;
            }
            section[data-screen-section="lobby"] .pill .k {
                font-size: 17px;
                color: var(--lime);
                letter-spacing: .14em;
            }
            section[data-screen-section="lobby"] .cp {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                background: var(--sf);
                border-radius: 99px;
                padding: 9px 16px;
                font-size: 12px;
                font-weight: 800;
                letter-spacing: .06em;
                transition: background .15s, transform .15s;
            }
            section[data-screen-section="lobby"] .cp:hover {
                background: var(--sf2);
                transform: translateY(-1px);
            }
            section[data-screen-section="lobby"] .cp-leave {
                background: rgba(255, 59, 92, 0.16);
                color: #ff9db0;
                border: 1px solid rgba(255, 59, 92, 0.35);
            }
            section[data-screen-section="lobby"] .cp-leave:hover {
                background: rgba(255, 59, 92, 0.28);
                color: #ffffff;
            }
            section[data-screen-section="lobby"] .l {
                display: flex;
                gap: 10px;
                align-items: center;
                flex-wrap: wrap;
            }
            section[data-screen-section="lobby"] .dot {
                width: 9px;
                height: 9px;
                border-radius: 50%;
                background: var(--lime);
            }
            section[data-screen-section="lobby"] .cnt {
                font-size: 16px;
                text-transform: none;
                letter-spacing: 0;
            }
            section[data-screen-section="lobby"] .cnt small {
                font-size: 14px;
                letter-spacing: 0;
                text-transform: none;
                color: var(--mu);
            }
            section[data-screen-section="lobby"] .cfg {
                width: 42px;
                height: 42px;
                border-radius: 13px;
                background: var(--sf);
                display: grid;
                place-items: center;
                font-size: 20px;
                transition: background .15s, transform .15s;
            }
            section[data-screen-section="lobby"] .cfg:hover {
                background: var(--sf2);
                transform: rotate(45deg);
            }
            section[data-screen-section="lobby"] .lobby-main {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
                gap: 22px;
                padding: 24px 32px;
                max-width: 1280px;
                width: 100%;
                margin: 0 auto;
            }
            section[data-screen-section="lobby"] .ttl h1 {
                margin: 0;
                font: 800 30px var(--ser);
            }
            section[data-screen-section="lobby"] .ttl p {
                margin: 4px 0 0;
                color: var(--mu);
                font-size: 14px;
            }
            section[data-screen-section="lobby"] .grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
            }
            section[data-screen-section="lobby"] .pl {
                display: grid;
                grid-template-columns: 68px 1fr;
                gap: 16px;
                align-items: center;
                background: var(--sf);
                border-radius: 18px;
                padding: 18px;
                text-align: left;
                border: 1.5px solid transparent;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4);
                transition: transform .15s, border-color .15s;
            }
            section[data-screen-section="lobby"] .pl.me {
                border-color: #4d6a1f;
                cursor: pointer;
            }
            section[data-screen-section="lobby"] .pl.me:hover {
                transform: translateY(-2px);
            }
            section[data-screen-section="lobby"] .av {
                position: relative;
                width: 68px;
                height: 68px;
                border-radius: 16px;
                display: grid;
                place-items: center;
                font-size: 36px;
                background: #0b0c18;
                border: 2px solid var(--c, #35d0e6);
                flex-shrink: 0;
            }
            section[data-screen-section="lobby"] .cr {
                position: absolute;
                top: -8px;
                right: -8px;
                width: 24px;
                height: 24px;
                border-radius: 8px;
                background: var(--lime);
                color: #14200a;
                display: grid;
                place-items: center;
                font-size: 13px;
                font-weight: 900;
            }
            section[data-screen-section="lobby"] .nm {
                font-size: 19px;
                font-weight: 800;
                line-height: 1.15;
                overflow-wrap: anywhere;
            }
            section[data-screen-section="lobby"] .lv {
                font-size: 13px;
                color: var(--mu);
                margin: 3px 0 8px;
            }
            section[data-screen-section="lobby"] .lv.h {
                color: var(--lime);
                font-weight: 800;
                letter-spacing: .08em;
                font-size: 12px;
            }
            section[data-screen-section="lobby"] .st {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 7px;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: .08em;
                background: var(--ln);
                color: var(--mu);
            }
            section[data-screen-section="lobby"] .st.on {
                background: var(--lime);
                color: #14200a;
            }
            section[data-screen-section="lobby"] .em {
                display: grid;
                grid-template-columns: 68px 1fr;
                gap: 16px;
                align-items: center;
                border-radius: 18px;
                padding: 18px;
                background: #0c0d1b;
                border: 1.5px dashed var(--ln);
                color: var(--mu);
                text-align: left;
                transition: border-color .15s, color .15s;
            }
            section[data-screen-section="lobby"] .em:hover {
                border-color: var(--lav);
                color: var(--lav);
            }
            section[data-screen-section="lobby"] .em .ic {
                width: 68px;
                height: 68px;
                border-radius: 16px;
                background: var(--sf);
                display: grid;
                place-items: center;
                font-size: 26px;
                flex-shrink: 0;
            }
            section[data-screen-section="lobby"] .em b {
                display: block;
                font-style: italic;
                font-size: 19px;
            }
            section[data-screen-section="lobby"] .em small {
                font-size: 12px;
                font-weight: 700;
            }
            section[data-screen-section="lobby"] .foot {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;
                padding: 8px 20px 36px;
            }
            section[data-screen-section="lobby"] .row {
                display: flex;
                gap: 12px;
                width: min(640px, 100%);
            }
            section[data-screen-section="lobby"] .rd {
                flex: 0 0 auto;
                padding: 0 24px;
                border-radius: 18px;
                background: var(--sf);
                font-size: 14px;
                font-weight: 800;
                letter-spacing: .04em;
                transition: background .15s, color .15s, transform .15s;
            }
            section[data-screen-section="lobby"] .rd:hover {
                transform: translateY(-1px);
            }
            section[data-screen-section="lobby"] .rd.on {
                background: var(--sf2);
                color: var(--lime);
            }
            section[data-screen-section="lobby"] .go {
                flex: 1;
                padding: 20px;
                border-radius: 18px;
                background: var(--lime);
                color: #14200a;
                font-size: 22px;
                font-weight: 800;
                letter-spacing: .04em;
                box-shadow: 0 10px 28px rgba(194, 240, 78, 0.2);
                transition: transform .15s, background .15s, opacity .15s;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
            section[data-screen-section="lobby"] .go:hover:not(:disabled) {
                transform: translateY(-2px);
            }
            section[data-screen-section="lobby"] .go:disabled {
                background: var(--ln);
                color: var(--mu);
                box-shadow: none;
                cursor: not-allowed;
            }
            section[data-screen-section="lobby"] .hint {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 12px;
                font-weight: 700;
                color: var(--mu);
            }
            section[data-screen-section="lobby"] .hint i {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: var(--lav);
            }
            @media(max-width: 900px){
                section[data-screen-section="lobby"] .grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
            @media(max-width: 599px){
                section[data-screen-section="lobby"] .grid {
                    grid-template-columns: 1fr;
                }
                section[data-screen-section="lobby"] .top, section[data-screen-section="lobby"] .lobby-main {
                    padding-inline: 16px;
                }
                section[data-screen-section="lobby"] .row {
                    flex-direction: column;
                }
                section[data-screen-section="lobby"] .rd {
                    padding: 14px;
                }
            }
        </style>

        <header class="top">
            <div class="l">
                <span class="pill">
                    <small>Lobby</small>
                    <span class="k" id="lobby-room-code-display">KODE: C H A O</span>
                </span>
                <button class="cp" id="btn-lobby-copy-link" type="button" aria-label="Salin link room">
                    <svg viewBox="0 0 24 24"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h8"/></svg>
                    <span>SALIN LINK</span>
                </button>
                <!-- FITUR KELUAR ROOM (LEAVE ROOM BUTTON) -->
                <button class="cp cp-leave" id="btn-lobby-leave-room" type="button" aria-label="Keluar dari room">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    <span>KELUAR</span>
                </button>
            </div>

            <!-- Aturan Room Real-Time -->
            <span class="pill" id="lobby-room-rules-pill">
                <span class="dot"></span>
                <span id="lobby-room-rules-text">Mode rusuh • 60 detik • 5 ronde • Campuran</span>
            </span>

            <div class="l">
                <span class="pill cnt">
                    <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 3-6 6.5-6s6.5 2.4 6.5 6"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5c2 .6 3.5 2.4 3.5 5.5"/></svg>
                    <b id="lobby-player-counter">0/8</b> <small>Pemain</small>
                </span>
                <!-- Tombol Edit Pengaturan (Host Only) -->
                <button class="cfg" id="btn-lobby-edit-room" type="button" aria-label="Pengaturan room" title="Edit Pengaturan Room">
                    <svg viewBox="0 0 24 24"><path d="M4 7h10M18 7h2M4 17h2M10 17h10M4 12h16"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></svg>
                </button>
            </div>
        </header>

        <main class="lobby-main">
            <div class="ttl">
                <h1>Ruang tunggu</h1>
                <p id="lobby-status-banner">Klik kartumu untuk mengubah status siap. Pelukis pertama dipilih acak oleh server.</p>
            </div>
            <!-- Grid Pemain & Slot Kosong Dinamis -->
            <div class="grid" id="lobby-players-grid"></div>
        </main>

        <footer class="foot">
            <div class="row">
                <button class="rd" id="btn-lobby-toggle-ready" type="button">TANDAI SIAP</button>
                <button class="go" id="btn-lobby-start-game" type="button">🚀 MULAI GAME</button>
            </div>
            <div class="hint">
                <i></i>
                <span id="lobby-hint-text">Menunggu pemain lain bergabung (min. 2 pemain siap)...</span>
            </div>
        </footer>
    </section>

    <!-- ===================================================================== -->
    <!-- SCREEN 3 & 4: GAMEPLAY (SABOTEUR VIEW & ARTIST VIEW)                  -->
    <!-- ===================================================================== -->
    <!-- ===================================================================== -->
    <!-- SCREEN 3 & 4: GAMEPLAY (REDESIGNED + INTERACTIVE CHAT FOR ALL PLAYERS)-->
    <!-- ===================================================================== -->
    <section data-screen-section="gameplay" class="hidden relative z-10 flex-1 flex flex-col w-full">
        <style>
            section[data-screen-section="gameplay"] {
                --bg:#0f1020;
                --sf:#1a1b30;
                --sf2:#26273f;
                --ln:#2e3050;
                --lime:#c2f04e;
                --lav:#c9b8ff;
                --red:#ff4d6d;
                --amb:#ffc83b;
                --tx:#eceefc;
                --mu:#8b8ead;
                --ser:'Fraunces',Georgia,serif;
                --sans:'DM Sans',system-ui,sans-serif;
                font:15px/1.4 var(--sans);
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
            section[data-screen-section="gameplay"] button {
                cursor:pointer;
                border:0;
                background:none;
            }
            section[data-screen-section="gameplay"] button:focus-visible,
            section[data-screen-section="gameplay"] input:focus-visible {
                outline:2px solid var(--lime);
                outline-offset:2px;
            }
            section[data-screen-section="gameplay"] .wrap {
                max-width:1280px;
                margin:0 auto;
                padding:22px 28px 36px;
                display:grid;
                gap:16px;
                width: 100%;
            }
            section[data-screen-section="gameplay"] .card {
                background:var(--sf);
                border:1px solid var(--ln);
                border-radius:20px;
            }
            section[data-screen-section="gameplay"] .hd {
                display:grid;
                grid-template-columns:auto 1fr auto;
                gap:20px;
                align-items:center;
                padding:14px 20px;
            }
            section[data-screen-section="gameplay"] .rd small {
                display:block;
                color:var(--mu);
                font-size:12px;
            }
            section[data-screen-section="gameplay"] .rd b {
                font:800 18px var(--ser);
            }
            section[data-screen-section="gameplay"] .wd {
                justify-self:center;
                text-align:center;
                background:var(--bg);
                border:1.5px dashed var(--ln);
                border-radius:16px;
                padding:8px 26px;
                min-width:340px;
            }
            section[data-screen-section="gameplay"] .wd small {
                display:block;
                color:var(--mu);
                font-size:11px;
                font-weight:700;
                letter-spacing:.08em;
            }
            section[data-screen-section="gameplay"] .wd b {
                font:800 24px var(--ser);
                color:var(--lime);
                letter-spacing:.03em;
            }
            section[data-screen-section="gameplay"] .wd b.no {
                color:var(--mu);
                font-size:18px;
            }
            section[data-screen-section="gameplay"] .tmr {
                display:flex;
                align-items:center;
                gap:12px;
            }
            section[data-screen-section="gameplay"] .ring {
                position:relative;
                width:62px;
                height:62px;
            }
            section[data-screen-section="gameplay"] .ring svg {
                width:62px;
                height:62px;
                transform:rotate(-90deg);
            }
            section[data-screen-section="gameplay"] .ring circle {
                fill:none;
                stroke-width:6;
                stroke-linecap:round;
            }
            section[data-screen-section="gameplay"] .ring .bgc {
                stroke:var(--ln);
            }
            section[data-screen-section="gameplay"] .ring .fg {
                stroke:var(--lime);
                stroke-dasharray:163.4;
                stroke-dashoffset:0;
                transition:stroke-dashoffset 1s linear, stroke .3s;
            }
            section[data-screen-section="gameplay"] .ring.lo .fg {
                stroke:var(--red);
            }
            section[data-screen-section="gameplay"] .ring b {
                position:absolute;
                inset:0;
                display:grid;
                place-items:center;
                font:800 18px var(--ser);
            }
            section[data-screen-section="gameplay"] .tmr small {
                color:var(--mu);
                font-size:12px;
                max-width:120px;
                line-height:1.25;
            }
            section[data-screen-section="gameplay"] .wp {
                padding:20px 24px;
                border-color:#4d6a1f;
                background:linear-gradient(135deg, #1f2a1a, var(--sf));
            }
            section[data-screen-section="gameplay"] .wp h2 {
                margin:0;
                font:800 22px var(--ser);
            }
            section[data-screen-section="gameplay"] .wp p {
                margin:2px 0 14px;
                color:var(--mu);
                font-size:14px;
            }
            section[data-screen-section="gameplay"] .wr {
                display:flex;
                gap:10px;
            }
            section[data-screen-section="gameplay"] .wr input {
                flex:1;
                background:var(--bg);
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
                color:#14200a;
                font:800 16px var(--ser);
                transition:transform .15s;
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
                background:var(--bg);
                font-weight:700;
                font-size:13px;
                cursor: pointer;
                transition: background .15s, color .15s;
            }
            section[data-screen-section="gameplay"] .sz.on {
                background:var(--lime);
                color:#14200a;
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
                background:#fbf4e4;
                background-image:linear-gradient(#0000000a 1px, transparent 1px), linear-gradient(90deg, #0000000a 1px, transparent 1px);
                background-size:32px 32px;
            }
            section[data-screen-section="gameplay"] .ov {
                position:absolute;
                inset:0;
                display:grid;
                place-items:center;
                background:#0f1020cc;
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
                font:800 16px var(--ser);
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
                background:var(--bg);
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
                display:grid;
                gap:2px;
                justify-items:center;
                padding:10px 4px;
                border-radius:14px;
                background:var(--bg);
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
                background:var(--bg);
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
                border: 1px solid #c2f04e;
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
                background:var(--bg);
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
                color:#14200a;
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
                background:var(--bg);
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

            <!-- Artist Secret Word Form (Only visible to Artist before word is set) -->
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
                    <!-- Tools (Palette & Sizes & Actions) -->
                    <div class="tools" id="canvas-artist-tools">
                        <div class="tg" id="cols" aria-label="Warna kuas">
                            <button type="button" class="sw on" data-brush-color="#222222" style="background: #222222;" aria-label="Warna Hitam"></button>
                            <button type="button" class="sw" data-brush-color="#ff4d6d" style="background: #ff4d6d;" aria-label="Warna Merah"></button>
                            <button type="button" class="sw" data-brush-color="#7ed321" style="background: #7ed321;" aria-label="Warna Hijau"></button>
                            <button type="button" class="sw" data-brush-color="#17a2ff" style="background: #17a2ff;" aria-label="Warna Biru"></button>
                            <button type="button" class="sw" data-brush-color="#a855f7" style="background: #a855f7;" aria-label="Warna Ungu"></button>
                            <button type="button" class="sw" data-brush-color="#ff8a1f" style="background: #ff8a1f;" aria-label="Warna Oranye"></button>
                            <button type="button" class="sw" data-brush-color="#fbf4e4" style="background: #fbf4e4;" aria-label="Penghapus"></button>
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
                        <!-- Canvas Overlay (Before word is set) -->
                        <div class="ov" id="canvas-overlay">
                            <div>Tetapkan kata rahasia dulu<span>Kanvas aktif setelah kata ditetapkan</span></div>
                        </div>
                        <!-- Sticker Bomb Overlay Layer -->
                        <div id="curse-sticker-bomb-layer" class="hidden pointer-events-none absolute inset-0 flex items-center justify-center z-20 p-4"></div>
                    </div>
                </section>

                <!-- Right: Sidebar (Sabotage, Live Guessing/Chat, Scoreboard) -->
                <aside class="st">
                    <!-- 1. Chaos Energy & Sabotage -->
                    <section class="card pd">
                        <h3>Chaos energy <span id="chaos-energy-val">100 / 100</span></h3>
                        <div class="en"><i id="chaos-energy-bar" style="width: 100%;"></i></div>
                        <div class="cds">
                            <button type="button" data-curse-trigger="earthquake" data-cost="30"><i>🌋</i>Gempa<small>⚡30</small></button>
                            <button type="button" data-curse-trigger="invert" data-cost="45"><i>🌀</i>Mabuk<small>⚡45</small></button>
                            <button type="button" data-curse-trigger="blindfold" data-cost="60"><i>💡</i>Gelap<small>⚡60</small></button>
                            <button type="button" data-curse-trigger="sticker" data-cost="25"><i>💣</i>Stiker<small>⚡25</small></button>
                        </div>
                        <p class="note">Kirim kartu kutukan untuk mengacaukan kanvas Pelukis!</p>
                    </section>

                    <!-- 2. Tebakan Langsung (Live Chat for Everyone!) -->
                    <section class="card pd">
                        <h3>Tebakan langsung <span id="chat-players-count">Pemain di room</span></h3>
                        <div id="chat">
                            <div class="emp" id="chat-empty-placeholder">Belum ada tebakan.<br>Tebakan & chat pemain muncul di sini.</div>
                        </div>
                        <!-- Live Guess / Chat Form: ALWAYS ENABLED FOR EVERYONE -->
                        <form id="form-live-guess" class="cr">
                            <input id="input-live-guess" placeholder="Ketik tebakan atau chat..." aria-label="Tebakan">
                            <button type="submit" id="btn-submit-live-guess">Kirim</button>
                        </form>
                    </section>

                    <!-- 3. Papan Skor -->
                    <section class="card pd">
                        <h3>Papan skor <span id="quick-rank-you">Skormu: 0</span></h3>
                        <div class="sb" id="game-scoreboard"></div>
                    </section>
                </aside>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- SCREEN 5: ROUND RESULTS (REAL TOP SCORERS & 1-5 STAR RATING)          -->
    <!-- ===================================================================== -->
    <section data-screen-section="results" class="hidden relative z-10 flex-1 flex flex-col justify-between w-full">
        <style>
            section[data-screen-section="results"] {
                --bg:#0f1020;
                --sf:#1a1b30;
                --sf2:#26273f;
                --ln:#2e3050;
                --lime:#c2f04e;
                --lav:#c9b8ff;
                --red:#ff4d6d;
                --amb:#ffc83b;
                --tx:#eceefc;
                --mu:#8b8ead;
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
            section[data-screen-section="results"] button:focus-visible,
            section[data-screen-section="results"] input:focus-visible {
                outline:2px solid var(--lime);
                outline-offset:2px;
            }
            section[data-screen-section="results"] .wrap {
                max-width:1100px;
                margin:0 auto;
                padding:34px 28px 150px;
                width: 100%;
            }
            section[data-screen-section="results"] .hero {
                text-align:center;
            }
            section[data-screen-section="results"] .chip {
                display:inline-flex;
                gap:8px;
                align-items:center;
                background:#2a2250;
                color:var(--lav);
                padding:6px 16px;
                border-radius:99px;
                font-size:13px;
                font-weight:700;
            }
            section[data-screen-section="results"] .hero h1 {
                font:900 clamp(40px, 6vw, 68px)/1 var(--ser);
                margin:14px 0 18px;
            }
            section[data-screen-section="results"] .word {
                display:inline-flex;
                align-items:center;
                gap:14px;
                background:var(--sf);
                border:1.5px solid #4d6a1f;
                border-radius:20px;
                padding:12px 26px;
            }
            section[data-screen-section="results"] .word small {
                color:var(--mu);
                font-size:13px;
            }
            section[data-screen-section="results"] .word b {
                font:800 30px var(--ser);
                color:var(--lime);
            }
            section[data-screen-section="results"] .word b span {
                display:inline-block;
                animation:pop .45s both;
            }
            section[data-screen-section="results"] .word b span.sp {
                width:.3em;
            }
            @keyframes pop {
                from{opacity:0;transform:translateY(16px) scale(.6)}
                to{opacity:1;transform:none}
            }
            section[data-screen-section="results"] .stats {
                display:flex;
                gap:10px;
                justify-content:center;
                flex-wrap:wrap;
                margin-top:16px;
            }
            section[data-screen-section="results"] .stats span {
                background:var(--sf);
                border:1px solid var(--ln);
                border-radius:14px;
                padding:8px 16px;
                font-size:14px;
            }
            section[data-screen-section="results"] .stats b {
                color:var(--lime);
            }
            section[data-screen-section="results"] .cols {
                display:grid;
                grid-template-columns:430px 1fr;
                gap:44px;
                align-items:start;
                margin-top:36px;
            }
            section[data-screen-section="results"] .pol {
                position:relative;
                background:#fbf4e4;
                padding:14px 14px 56px;
                border-radius:12px;
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
                background:rgba(255, 200, 59, 0.6);
                transform:rotate(3deg);
                border-radius:3px;
            }
            section[data-screen-section="results"] #results-polaroid-canvas {
                display:block;
                width:100%;
                aspect-ratio:8/5;
                border-radius:6px;
                background:#fbf4e4;
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
                color:#3a3350;
                font:700 14px var(--ser);
            }
            section[data-screen-section="results"] .rp {
                background:#14102a;
                color:var(--lime);
                padding:8px 14px;
                border-radius:10px;
                font:800 14px var(--ser);
                transition:transform .15s;
                cursor: pointer;
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
                cursor: pointer;
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
                font-size:30px;
                background:var(--sf2);
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
                color:#14200a;
            }
            section[data-screen-section="results"] .tag.l {
                background:var(--lav);
                color:#14200a;
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
                background:var(--bg);
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
                background:var(--bg);
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
                padding:16px 20px calc(16px + env(safe-area-inset-bottom, 0px));
                background:#0f1020ee;
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
                padding:18px;
                border-radius:18px;
                background:var(--lime);
                color:#14200a;
                font:900 20px var(--ser);
                box-shadow:0 10px 30px rgba(194, 240, 78, 0.2);
                transition:transform .15s;
                cursor: pointer;
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
                width:230px;
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
                cursor: pointer;
                transition: background .15s;
            }
            section[data-screen-section="results"] .sec button:hover {
                background:var(--ln);
            }
            section[data-screen-section="results"] .sec button:disabled {
                background:var(--lav);
                color:#241a4a;
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
                <h1>Ronde selesai!</h1>
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

        <!-- Fixed Bottom Next Round & Museum Bar -->
        <div class="bt">
            <button class="nx" id="btn-results-next-round" type="button">
                ▶ Ronde berikutnya <span style="font-weight:600;font-size:14px">(giliran pelukis berganti)</span>
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
    <!-- SCREEN 6: HALL OF SHAME (REAL SQLITE/DB MUSEUM WITH 3 FILTERS)        -->
    <!-- ===================================================================== -->
    <section data-screen-section="museum" class="hidden relative z-10 flex-1 flex flex-col justify-between px-6 py-6 max-w-7xl mx-auto w-full">

        <!-- Museum Header & Working Filter Tabs -->
        <div class="flex flex-wrap items-end justify-between gap-4 pb-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#1C192B] border border-[#2F2A46] text-[10px] font-extrabold uppercase tracking-widest text-[#B8FF3B] mb-2">
                    <span>GALERI KARYA PEMAIN ASLI</span>
                    <span class="text-[#8E88A8]">• Tersimpan di Database</span>
                </div>
                <h1 class="font-serif font-black text-4xl sm:text-5xl text-white">Hall of Shame</h1>
                <p class="text-sm text-[#948FB1] mt-1">Koleksi lukisan hasil pertandingan nyata lengkap dengan tingkat Chaos % dan rating bintang.</p>
            </div>

            <!-- Filter Tabs: Pilihan Kurator | Terfavorit | Terbaru -->
            <div class="flex items-center gap-2 bg-[#171523] border border-[#29243E] rounded-full p-1 text-xs font-bold">
                <button type="button" data-museum-filter="curator" class="px-4 py-2 rounded-full text-[#948FB1] hover:text-white transition cursor-pointer">
                    Pilihan Kurator
                </button>
                <button type="button" data-museum-filter="favorite" class="px-4 py-2 rounded-full text-[#948FB1] hover:text-white transition cursor-pointer">
                    Terfavorit
                </button>
                <button type="button" data-museum-filter="newest" class="px-4 py-2 rounded-full bg-[#B8FF3B] text-[#0E0D15] shadow transition cursor-pointer">
                    Terbaru
                </button>
            </div>
        </div>

        <!-- Dynamic Real Museum Artworks Grid -->
        <div id="museum-gallery-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 my-auto"></div>

        <!-- Bottom Bar -->
        <div class="mt-8 pt-4 border-t border-white/5 flex flex-wrap items-center justify-between gap-4 text-xs text-[#8E88A8]">
            <span id="museum-count-label">Menampilkan 0 karya asli dari database</span>
            <button type="button" data-nav-view="home" class="px-5 py-2.5 rounded-xl bg-[#1A1829] hover:bg-[#252238] border border-[#2E2A45] text-white font-bold transition cursor-pointer">
                Mulai Pertandingan Baru →
            </button>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- MODAL PENGATURAN & BUAT ROOM KUSTOM (PRESETS + REAL SETTINGS)          -->
    <!-- ===================================================================== -->
    <div id="modal-room-settings" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <style>
            #modal-room-settings {
                --bg:#0e0a1a;--sf:#1a1530;--sf2:#241d45;--ln:#2d2650;--lime:#b6ff3b;--lav:#c9b8ff;--red:#ff3b5c;--amb:#ffc83b;--tx:#f4f0ff;--mu:#9a92bf;--ser:'Fraunces',Georgia,serif;--sans:'DM Sans',system-ui,sans-serif;
                box-sizing:border-box;
            }
            #modal-room-settings *, #modal-room-settings *::before, #modal-room-settings *::after { box-sizing:inherit; }
            #modal-room-settings button { font:inherit; color:inherit; cursor:pointer; border:0; background:none; }
            #modal-room-settings button:focus-visible { outline:2px solid var(--lime); outline-offset:2px; }
            #modal-room-settings .room-modal-container { width:min(720px,100%); background:var(--sf); border:1.5px solid #4d6a1f; border-radius:24px; overflow:hidden; max-height:calc(100vh - 32px); overflow-y:auto; box-shadow:0 25px 70px rgba(0,0,0,0.85); margin:auto; }
            #modal-room-settings .hd { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; padding:24px 26px 18px; }
            #modal-room-settings h1 { font:900 30px/1.1 var(--ser); margin:0 0 6px; color:var(--tx); }
            #modal-room-settings .hd p { margin:0; color:var(--mu); font-size:14px; }
            #modal-room-settings .hr { display:flex; gap:8px; align-items:center; }
            #modal-room-settings .code { background:var(--bg); border:1px dashed var(--lime); border-radius:12px; padding:8px 12px; font:700 15px var(--ser); letter-spacing:.18em; color:var(--lime); display:inline-flex; align-items:center; }
            #modal-room-settings .code span { font:500 11px var(--sans); letter-spacing:0; color:var(--mu); margin-left:8px; }
            #modal-room-settings .x { width:38px; height:38px; border-radius:12px; background:var(--bg); font-size:20px; color:var(--mu); display:grid; place-items:center; transition:color .15s, background .15s; }
            #modal-room-settings .x:hover { color:var(--tx); background:var(--sf2); }
            #modal-room-settings .sum { display:flex; flex-wrap:wrap; gap:8px; align-items:center; padding:12px 26px; background:var(--bg); border-block:1px solid var(--ln); }
            #modal-room-settings .sum b { font-weight:500; font-size:13px; padding:5px 11px; border-radius:99px; background:var(--sf2); color:var(--tx); }
            #modal-room-settings .sum em { margin-left:auto; font-style:normal; color:var(--lime); font-size:13px; font-weight:700; }
            #modal-room-settings .modal-body { padding:20px 26px 6px; display:grid; gap:22px; }
            #modal-room-settings .sec h2 { font:700 13px var(--sans); color:var(--mu); margin:0 0 10px; display:flex; align-items:center; gap:8px; }
            #modal-room-settings .pre { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
            #modal-room-settings .p { text-align:left; padding:14px; border-radius:16px; background:var(--sf2); border:1.5px solid var(--ln); display:grid; gap:4px; transition:transform .15s,border-color .15s; }
            #modal-room-settings .p:hover { transform:translateY(-2px); }
            #modal-room-settings .p i { font-style:normal; font-size:24px; }
            #modal-room-settings .p b { font:700 16px var(--ser); color:var(--tx); }
            #modal-room-settings .p small { color:var(--mu); font-size:12px; line-height:1.35; }
            #modal-room-settings .p[aria-pressed=true] { border-color:var(--lime); background:#21301a; }
            #modal-room-settings .p[data-p=rush][aria-pressed=true] { border-color:var(--red); background:#35142a; }
            #modal-room-settings .p[data-p=chill][aria-pressed=true] { border-color:var(--lav); background:#2a2250; }
            #modal-room-settings .row { display:grid; grid-template-columns:150px 1fr; gap:16px; align-items:start; padding-top:20px; border-top:1px solid var(--ln); }
            #modal-room-settings .lb b { display:block; font:700 17px var(--ser); color:var(--tx); }
            #modal-room-settings .lb small { color:var(--mu); font-size:13px; }
            #modal-room-settings .grp { display:grid; grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); gap:8px; }
            #modal-room-settings .grp.two { grid-template-columns:1fr 1fr; }
            #modal-room-settings .o { position:relative; text-align:left; padding:12px 14px; border-radius:14px; background:var(--bg); border:1.5px solid var(--ln); display:grid; gap:2px; transition:border-color .15s,background .15s; }
            #modal-room-settings .o:hover { border-color:#5a4f94; }
            #modal-room-settings .o b { font-weight:700; font-size:14px; color:var(--tx); }
            #modal-room-settings .o small { color:var(--mu); font-size:12px; line-height:1.3; }
            #modal-room-settings .o[aria-pressed=true] { border-color:var(--lime); background:#1f2a1a; }
            #modal-room-settings .o[aria-pressed=true]::after { content:"✓"; position:absolute; top:8px; right:10px; width:18px; height:18px; border-radius:50%; background:var(--lime); color:#14200a; font-size:11px; font-weight:900; display:grid; place-items:center; }
            #modal-room-settings .o .n { font:900 28px/1 var(--ser); color:var(--tx); }
            #modal-room-settings .o .n sub { font:500 12px var(--sans); color:var(--mu); margin-left:3px; }
            #modal-room-settings .seats { display:flex; gap:6px; margin-top:12px; }
            #modal-room-settings .seats span { flex:1; height:8px; border-radius:9px; background:var(--ln); transition:background .2s; }
            #modal-room-settings .seats span.on { background:var(--lime); }
            #modal-room-settings .cards { display:flex; flex-wrap:wrap; gap:8px; margin-top:12px; }
            #modal-room-settings .cards span { background:#35142a; color:#ff9db0; border-radius:99px; padding:5px 12px; font-size:12px; font-weight:700; }
            #modal-room-settings .cards.off span { background:var(--sf2); color:var(--mu); }
            #modal-room-settings .packs { grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); }
            #modal-room-settings .packs .o i { font-style:normal; font-size:26px; margin-bottom:2px; }
            #modal-room-settings .ft { position:sticky; bottom:0; display:flex; justify-content:space-between; align-items:center; gap:12px; padding:16px 26px; background:var(--sf); border-top:1px solid var(--ln); margin-top:20px; z-index:10; }
            #modal-room-settings .ft p { margin:0; color:var(--mu); font-size:12px; }
            #modal-room-settings .ft div { display:flex; gap:10px; }
            #modal-room-settings .cancel { padding:12px 18px; border-radius:14px; background:var(--bg); font-weight:700; color:var(--tx); transition:background .15s; }
            #modal-room-settings .cancel:hover { background:var(--sf2); }
            #modal-room-settings .save { padding:12px 24px; border-radius:14px; background:var(--lime); color:#14200a; font:900 15px var(--ser); transition:transform .15s, background .15s; }
            #modal-room-settings .save:hover { transform:translateY(-1px); }
            #modal-room-settings .save.ok { background:var(--lav); color:#141220; }
            @media(max-width:640px){
                #modal-room-settings .pre { grid-template-columns:1fr; }
                #modal-room-settings .row { grid-template-columns:1fr; gap:10px; }
                #modal-room-settings .hd, #modal-room-settings .modal-body, #modal-room-settings .sum, #modal-room-settings .ft { padding-inline:18px; }
                #modal-room-settings .ft p { display:none; }
                #modal-room-settings .ft div { width:100%; }
                #modal-room-settings .ft button { flex:1; }
                #modal-room-settings h1 { font-size:25px; }
            }
        </style>
        <main class="room-modal-container" aria-labelledby="modal-room-settings-title">
            <header class="hd">
                <div>
                    <h1 id="modal-room-settings-title">Pengaturan room</h1>
                    <p id="modal-room-settings-subtitle">Perubahan langsung tersinkron ke semua pemain.</p>
                </div>
                <div class="hr">
                    <button class="code" id="btn-modal-copy-code" type="button" aria-label="Salin kode room">
                        <span id="modal-room-code-text">U93C</span><span id="modal-room-code-sub">salin</span>
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
                            <i>⚡</i><b>Standar</b>
                            <small>Publik · 60 detik · 5 ronde · Chaos aktif</small>
                        </button>
                        <button class="p" type="button" data-p="rush">
                            <i>🔥</i><b>Mode rusuh</b>
                            <small>Publik · 45 detik · 8 ronde · Chaos aktif</small>
                        </button>
                        <button class="p" type="button" data-p="chill">
                            <i>☁️</i><b>Santai</b>
                            <small>Privat · 80 detik · 3 ronde · Tanpa chaos</small>
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
                            placeholder="Contoh: MABR / ASIK (Kosong = Acak)"
                            class="flex-1 bg-[#0e0a1a] border border-[#2d2650] focus:border-[#b6ff3b] rounded-xl px-3.5 py-2 text-sm font-bold uppercase tracking-widest text-[#b6ff3b] placeholder-[#6e6888] focus:outline-none"
                        >
                        <button id="btn-randomize-custom-code" type="button" class="px-3.5 py-2 rounded-xl bg-[#241d45] hover:bg-[#2d2650] border border-[#2d2650] text-xs font-bold text-white transition cursor-pointer">
                            🎲 Acak
                        </button>
                    </div>
                </section>

                <section class="row">
                    <div class="lb">
                        <b>Privasi</b>
                        <small>Siapa yang boleh masuk</small>
                    </div>
                    <div class="grp two">
                        <button class="o" type="button" data-k="priv" data-v="pub">
                            <b>🌐 Publik</b>
                            <small>Bisa ditemukan lewat Main Sekarang</small>
                        </button>
                        <button class="o" type="button" data-k="priv" data-v="priv">
                            <b>🔒 Privat</b>
                            <small>Hanya lewat kode atau link undangan</small>
                        </button>
                    </div>
                </section>

                <section class="row">
                    <div class="lb">
                        <b>Waktu menggambar</b>
                        <small>Per ronde</small>
                    </div>
                    <div class="grp">
                        <button class="o" type="button" data-k="time" data-v="45"><span class="n">45<sub>dtk</sub></span><small>Panik</small></button>
                        <button class="o" type="button" data-k="time" data-v="60"><span class="n">60<sub>dtk</sub></span><small>Standar</small></button>
                        <button class="o" type="button" data-k="time" data-v="80"><span class="n">80<sub>dtk</sub></span><small>Santai</small></button>
                        <button class="o" type="button" data-k="time" data-v="100"><span class="n">100<sub>dtk</sub></span><small>Master</small></button>
                    </div>
                </section>

                <section class="row">
                    <div class="lb">
                        <b>Total ronde</b>
                        <small>Tiap pemain bergiliran melukis</small>
                    </div>
                    <div class="grp">
                        <button class="o" type="button" data-k="rounds" data-v="3"><span class="n">3<sub>ronde</sub></span><small>Singkat</small></button>
                        <button class="o" type="button" data-k="rounds" data-v="5"><span class="n">5<sub>ronde</sub></span><small>Disarankan</small></button>
                        <button class="o" type="button" data-k="rounds" data-v="8"><span class="n">8<sub>ronde</sub></span><small>Maraton</small></button>
                    </div>
                </section>

                <section class="row">
                    <div class="lb">
                        <b>Maksimal pemain</b>
                        <small>Kursi yang tersedia</small>
                    </div>
                    <div>
                        <div class="grp">
                            <button class="o" type="button" data-k="max" data-v="4"><span class="n">4</span><small>Kecil</small></button>
                            <button class="o" type="button" data-k="max" data-v="6"><span class="n">6</span><small>Seru</small></button>
                            <button class="o" type="button" data-k="max" data-v="8"><span class="n">8</span><small>Disarankan</small></button>
                            <button class="o" type="button" data-k="max" data-v="12"><span class="n">12</span><small>Ramai</small></button>
                        </div>
                        <div class="seats" id="seats" aria-hidden="true"></div>
                    </div>
                </section>

                <section class="row">
                    <div class="lb">
                        <b>Sabotase</b>
                        <small>Kartu kutukan untuk penebak</small>
                    </div>
                    <div>
                        <div class="grp two">
                            <button class="o" type="button" data-k="chaos" data-v="on">
                                <b>💥 Chaos aktif</b>
                                <small>Penebak bisa mengacaukan kanvas</small>
                            </button>
                            <button class="o" type="button" data-k="chaos" data-v="off">
                                <b>🕊️ Tanpa sabotase</b>
                                <small>Tebak gambar klasik murni</small>
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
                        <b>Paket kata rahasia</b>
                        <small>Tema kata yang muncul</small>
                    </div>
                    <div class="grp packs">
                        <button class="o" type="button" data-k="pack" data-v="mix"><i>🌟</i><b>Campuran</b></button>
                        <button class="o" type="button" data-k="pack" data-v="meme"><i>😹</i><b>Meme viral</b></button>
                        <button class="o" type="button" data-k="pack" data-v="kuliner"><i>🍜</i><b>Kuliner Indo</b></button>
                        <button class="o" type="button" data-k="pack" data-v="anime"><i>🎮</i><b>Anime dan game</b></button>
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
    <!-- INTERACTIVE PANIC TIME-LAPSE REPLAY MODAL                             -->
    <!-- ===================================================================== -->
    <div id="panic-replay-modal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-[#181526] border-2 border-[#D4AF37] rounded-3xl max-w-lg w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#B8FF3B]">🎬 TIME-LAPSE REPLAY KEPANIKAN</span>
                    <h3 id="modal-replay-title" class="font-serif font-bold text-xl text-white">Replay Lukisan</h3>
                </div>
                <button id="btn-close-replay-modal" type="button" class="w-8 h-8 rounded-full bg-[#26223B] text-white font-bold cursor-pointer">✕</button>
            </div>
            <div class="rounded-2xl overflow-hidden border border-white/10 bg-[#FAF4E8]">
                <canvas id="modal-replay-canvas" width="440" height="300" class="w-full h-auto block"></canvas>
            </div>
            <p id="modal-replay-plaque" class="mt-3 text-center text-xs font-serif italic text-[#D4AF37]"></p>
        </div>
    </div>

    <!-- Floating Toast Notifications -->
    <div id="chaos-toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm"></div>

    <!-- Hydrate Initial Backend State to JS Engine -->
    <script>
        window.CORET_CHAOS_BOOT = {
            deckCatalog: @json($deckCatalog),
            hallOfShame: @json($hallOfShame),
            initialRoom: @json($initialRoom),
            onlineCount: @json($onlineCount ?? 1),
            guestPlayer: @json($guestPlayer ?? null),
            reverbConfig: @json($reverbConfig ?? []),
        };
    </script>
</body>
</html>
