    <!-- ===================================================================== -->
    <!-- TOP GLOBAL NAVBAR                                                     -->
    <!-- ===================================================================== -->
    <header class="sticky top-0 z-30 w-full px-4 sm:px-6 py-3 flex items-center justify-between gap-3 border-b border-[#333347]/80 bg-[#111124]/90 backdrop-blur-md">
        <!-- Brand Logo -->
        <a href="{{ auth()->check() ? route('lobby.hub') : route('home') }}" class="flex items-center gap-2.5 text-left group cursor-pointer shrink-0">
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
        </a>

        <!-- Right Controls: Live Ping, Sound, Fullscreen & Profile Pill -->
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

            <!-- Active Player / Profile Pill -->
            <button id="btn-open-profile-modal" type="button" class="flex items-center gap-2 pl-1.5 pr-3 py-1 rounded-full bg-[#1A1A2D] hover:bg-[#28283C] border border-[#333347] hover:border-[#BDF454]/60 transition cursor-pointer" title="Profil Seniman & Akun">
                <span id="nav-user-avatar-slot" class="w-6 h-6 rounded-full bg-[#0C0C1F] border border-[#333347] flex items-center justify-center overflow-hidden"></span>
                <span id="nav-user-nickname" class="text-xs font-bold text-[#E2E0FB] max-w-[110px] truncate">{{ $guestPlayer['nickname'] ?? 'SenimanRusuh_89' }}</span>
                <span id="nav-user-status-dot" class="w-1.5 h-1.5 rounded-full bg-[#BDF454]"></span>
            </button>
        </div>
    </header>
