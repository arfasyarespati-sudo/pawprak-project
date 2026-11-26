<div x-data="{
        open: false,
        maxPlayers: 8,
        roundDuration: 80,
        gameMode: 'classic',
        wordPacks: ['Umum & Benda', 'Buah & Makanan'],
        isPublic: true,
        selectedAvatar: '{{ Auth::user()->avatar ?? 'kucing' }}',

        togglePack(pack) {
            if (this.wordPacks.includes(pack)) {
                if (this.wordPacks.length > 1) {
                    this.wordPacks = this.wordPacks.filter(p => p !== pack);
                }
            } else {
                this.wordPacks.push(pack);
            }
        }
    }"
    @open-create-lobby-modal.window="open = true"
    @keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true">

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-[#0B0C14]/80 backdrop-blur-md"></div>

    {{-- Modal Dialog --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             class="relative w-full max-w-2xl transform overflow-hidden rounded-2xl bg-[#141625] border border-[#23263A] p-6 sm:p-8 shadow-2xl text-left transition-all">

            {{-- Close Button --}}
            <button @click="open = false" type="button" class="absolute top-6 right-6 text-gray-400 hover:text-white transition-colors focus:outline-none">
                <span class="sr-only">Tutup</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Header --}}
            <div class="flex items-start gap-4 mb-6">
                <div class="w-12 h-12 rounded-xl bg-[#23263A] flex items-center justify-center text-xl shrink-0">
                    ⚙️
                </div>
                <div>
                    <h2 class="font-display text-2xl font-black text-white tracking-wide" id="modal-title">
                        PENGATURAN LOBBY BARU
                    </h2>
                    <p class="text-xs text-gray-400 mt-1 leading-relaxed">
                        Tentukan aturan main dan undang kawan tongkronganmu sebelum pertarungan coretan dimulai!
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('lobby.store') }}" class="space-y-6">
                @csrf

                {{-- Hidden Inputs for Alpine values --}}
                <input type="hidden" name="max_players" :value="maxPlayers">
                <input type="hidden" name="round_duration" :value="roundDuration">
                <input type="hidden" name="game_mode" :value="gameMode">
                <input type="hidden" name="is_public" :value="isPublic ? 1 : 0">
                <input type="hidden" name="avatar" :value="selectedAvatar">
                <template x-for="pack in wordPacks" :key="pack">
                    <input type="hidden" name="word_packs[]" :value="pack">
                </template>

                {{-- Section 1: Kapasitas Pemain --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-gray-200 uppercase tracking-wider flex items-center gap-1.5">
                            <span>👥</span> 1. KAPASITAS PEMAIN
                        </label>
                        <span class="text-[10px] text-gray-400">Slot Ruangan</span>
                    </div>

                    <div class="grid grid-cols-4 gap-2.5">
                        {{-- 4 Pemain --}}
                        <button type="button" @click="maxPlayers = 4"
                                :class="maxPlayers === 4 ? 'bg-[#C3F53C] text-[#0B0C14] font-black border-[#C3F53C]' : 'bg-[#1B1D30] text-gray-300 hover:bg-[#23263A] border-[#23263A]'"
                                class="py-2.5 px-2 rounded-xl border text-center transition flex flex-col items-center justify-center cursor-pointer">
                            <span class="text-base font-extrabold leading-none">4</span>
                            <span class="text-[10px] mt-1 opacity-80">Pemain</span>
                        </button>

                        {{-- 6 Pemain --}}
                        <button type="button" @click="maxPlayers = 6"
                                :class="maxPlayers === 6 ? 'bg-[#C3F53C] text-[#0B0C14] font-black border-[#C3F53C]' : 'bg-[#1B1D30] text-gray-300 hover:bg-[#23263A] border-[#23263A]'"
                                class="py-2.5 px-2 rounded-xl border text-center transition flex flex-col items-center justify-center cursor-pointer">
                            <span class="text-base font-extrabold leading-none">6</span>
                            <span class="text-[10px] mt-1 opacity-80">Pemain</span>
                        </button>

                        {{-- 8 Pemain (Rekomendasi) --}}
                        <button type="button" @click="maxPlayers = 8"
                                :class="maxPlayers === 8 ? 'bg-[#C3F53C] text-[#0B0C14] font-black border-[#C3F53C]' : 'bg-[#1B1D30] text-gray-300 hover:bg-[#23263A] border-[#23263A]'"
                                class="py-2.5 px-2 rounded-xl border text-center transition flex flex-col items-center justify-center cursor-pointer">
                            <span class="text-base font-extrabold leading-none flex items-center gap-1">
                                8 <span class="text-xs">✪</span>
                            </span>
                            <span class="text-[9px] mt-1 uppercase font-bold tracking-tight">REKOMENDASI</span>
                        </button>

                        {{-- 12 Pemain --}}
                        <button type="button" @click="maxPlayers = 12"
                                :class="maxPlayers === 12 ? 'bg-[#C3F53C] text-[#0B0C14] font-black border-[#C3F53C]' : 'bg-[#1B1D30] text-gray-300 hover:bg-[#23263A] border-[#23263A]'"
                                class="py-2.5 px-2 rounded-xl border text-center transition flex flex-col items-center justify-center cursor-pointer">
                            <span class="text-base font-extrabold leading-none">12</span>
                            <span class="text-[9px] mt-1 uppercase tracking-tight text-purple-400 font-bold" :class="maxPlayers === 12 ? 'text-[#0B0C14]' : 'text-purple-400'">CHAOS TOTAL</span>
                        </button>
                    </div>

                    <p class="text-[11px] text-gray-400 mt-2 flex items-center gap-1">
                        <span>ⓘ</span> Minimal 2 pemain untuk memulai pertandingan.
                    </p>
                </div>

                {{-- Section 2: Durasi Ronde Menggambar --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-gray-200 uppercase tracking-wider flex items-center gap-1.5">
                            <span>⏱</span> 2. DURASI RONDE MENGGAMBAR
                        </label>
                        <span class="text-[10px] text-gray-400">Waktu Per Giliran</span>
                    </div>

                    <div class="grid grid-cols-4 gap-2.5">
                        <button type="button" @click="roundDuration = 60"
                                :class="roundDuration === 60 ? 'bg-[#C3F53C] text-[#0B0C14] font-black border-[#C3F53C]' : 'bg-[#1B1D30] text-gray-300 hover:bg-[#23263A] border-[#23263A]'"
                                class="py-2.5 px-2 rounded-xl border text-center text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer">
                            <span>⚡</span> 60 Detik
                        </button>
                        <button type="button" @click="roundDuration = 80"
                                :class="roundDuration === 80 ? 'bg-[#C3F53C] text-[#0B0C14] font-black border-[#C3F53C]' : 'bg-[#1B1D30] text-gray-300 hover:bg-[#23263A] border-[#23263A]'"
                                class="py-2.5 px-2 rounded-xl border text-center text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer">
                            <span>✓</span> 80 Detik
                        </button>
                        <button type="button" @click="roundDuration = 100"
                                :class="roundDuration === 100 ? 'bg-[#C3F53C] text-[#0B0C14] font-black border-[#C3F53C]' : 'bg-[#1B1D30] text-gray-300 hover:bg-[#23263A] border-[#23263A]'"
                                class="py-2.5 px-2 rounded-xl border text-center text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer">
                            <span>⏳</span> 100 Detik
                        </button>
                        <button type="button" @click="roundDuration = 120"
                                :class="roundDuration === 120 ? 'bg-[#C3F53C] text-[#0B0C14] font-black border-[#C3F53C]' : 'bg-[#1B1D30] text-gray-300 hover:bg-[#23263A] border-[#23263A]'"
                                class="py-2.5 px-2 rounded-xl border text-center text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer">
                            <span>☕</span> 120s Santai
                        </button>
                    </div>
                </div>

                {{-- Section 3: Mode Permainan --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-gray-200 uppercase tracking-wider flex items-center gap-1.5">
                            <span>🎮</span> 3. MODE PERMAINAN
                        </label>
                        <span class="text-[10px] text-gray-400">RULESET AKTIF</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        {{-- Mode 1: Chaos Klasik --}}
                        <div @click="gameMode = 'classic'"
                             :class="gameMode === 'classic' ? 'border-[#C3F53C] bg-[#1B1D30]' : 'border-[#23263A] bg-[#141625] hover:border-gray-600'"
                             class="relative p-3.5 rounded-xl border cursor-pointer transition flex flex-col justify-between">
                            <div class="flex items-start justify-between">
                                <div class="w-7 h-7 rounded-lg bg-[#C3F53C] text-[#0B0C14] flex items-center justify-center text-sm font-black">
                                    ⚡
                                </div>
                                <div x-show="gameMode === 'classic'" class="w-4 h-4 rounded-full bg-[#C3F53C] text-[#0B0C14] flex items-center justify-center text-[10px] font-bold">
                                    ✓
                                </div>
                            </div>
                            <h4 class="text-xs font-black text-white mt-2">Chaos Klasik</h4>
                            <p class="text-[10px] text-gray-400 mt-1 leading-normal">
                                Semua kutukan aktif, sabotase tinta dan inversi layar tanpa ampun!
                            </p>
                            <span class="inline-block mt-3 px-2 py-0.5 rounded text-[9px] font-bold bg-[#C3F53C]/10 text-[#C3F53C] border border-[#C3F53C]/30 w-fit">
                                Sabotase: 100%
                            </span>
                        </div>

                        {{-- Mode 2: Mode Santai --}}
                        <div @click="gameMode = 'relaxed'"
                             :class="gameMode === 'relaxed' ? 'border-[#C3F53C] bg-[#1B1D30]' : 'border-[#23263A] bg-[#141625] hover:border-gray-600'"
                             class="relative p-3.5 rounded-xl border cursor-pointer transition flex flex-col justify-between">
                            <div class="flex items-start justify-between">
                                <div class="w-7 h-7 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center text-sm">
                                    ☕
                                </div>
                                <div x-show="gameMode === 'relaxed'" class="w-4 h-4 rounded-full bg-[#C3F53C] text-[#0B0C14] flex items-center justify-center text-[10px] font-bold">
                                    ✓
                                </div>
                            </div>
                            <h4 class="text-xs font-black text-white mt-2">Mode Santai</h4>
                            <p class="text-[10px] text-gray-400 mt-1 leading-normal">
                                Murni tebak gambar santai tanpa gangguan kutukan atau sabotase kotor.
                            </p>
                            <span class="inline-block mt-3 px-2 py-0.5 rounded text-[9px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/30 w-fit">
                                No Sabotase
                            </span>
                        </div>

                        {{-- Mode 3: Kutukan Ekstrem --}}
                        <div @click="gameMode = 'extreme'"
                             :class="gameMode === 'extreme' ? 'border-[#C3F53C] bg-[#1B1D30]' : 'border-[#23263A] bg-[#141625] hover:border-gray-600'"
                             class="relative p-3.5 rounded-xl border cursor-pointer transition flex flex-col justify-between">
                            <div class="flex items-start justify-between">
                                <div class="w-7 h-7 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center text-sm">
                                    💀
                                </div>
                                <div x-show="gameMode === 'extreme'" class="w-4 h-4 rounded-full bg-[#C3F53C] text-[#0B0C14] flex items-center justify-center text-[10px] font-bold">
                                    ✓
                                </div>
                            </div>
                            <h4 class="text-xs font-black text-white mt-2">Kutukan Ekstrem</h4>
                            <p class="text-[10px] text-gray-400 mt-1 leading-normal">
                                Semua efek kutukan berlipat ganda! Gempa layar, kuas buta, dan timer turbo!
                            </p>
                            <span class="inline-block mt-3 px-2 py-0.5 rounded text-[9px] font-bold bg-purple-500/10 text-purple-400 border border-purple-500/30 w-fit">
                                Hardcore 2x Speed
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Section 4: Paket Kata Kunci & Tema --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-gray-200 uppercase tracking-wider flex items-center gap-1.5">
                            <span>🔤</span> 4. PAKET KATA KUNCI & TEMA
                        </label>
                        <span class="text-[10px] text-gray-400">Multi-pilihan</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="togglePack('Umum & Benda')"
                                :class="wordPacks.includes('Umum & Benda') ? 'bg-[#C3F53C] text-[#0B0C14] font-bold border-[#C3F53C]' : 'bg-[#1B1D30] text-gray-300 border-[#23263A] hover:bg-[#23263A]'"
                                class="px-3.5 py-1.5 rounded-full border text-xs transition cursor-pointer flex items-center gap-1.5">
                            <span>🏠 Umum &amp; Benda</span>
                            <span x-show="wordPacks.includes('Umum & Benda')">✓</span>
                        </button>

                        <button type="button" @click="togglePack('Buah & Makanan')"
                                :class="wordPacks.includes('Buah & Makanan') ? 'bg-[#C3F53C] text-[#0B0C14] font-bold border-[#C3F53C]' : 'bg-[#1B1D30] text-gray-300 border-[#23263A] hover:bg-[#23263A]'"
                                class="px-3.5 py-1.5 rounded-full border text-xs transition cursor-pointer flex items-center gap-1.5">
                            <span>🍎 Buah &amp; Makanan</span>
                            <span x-show="wordPacks.includes('Buah & Makanan')">✓</span>
                        </button>

                        <button type="button" @click="togglePack('Hewan')"
                                :class="wordPacks.includes('Hewan') ? 'bg-[#C3F53C] text-[#0B0C14] font-bold border-[#C3F53C]' : 'bg-[#1B1D30] text-gray-300 border-[#23263A] hover:bg-[#23263A]'"
                                class="px-3.5 py-1.5 rounded-full border text-xs transition cursor-pointer flex items-center gap-1.5">
                            <span>🐱 Hewan</span>
                            <span x-show="wordPacks.includes('Hewan')">✓</span>
                        </button>

                        <button type="button" @click="togglePack('Alam & Kendaraan')"
                                :class="wordPacks.includes('Alam & Kendaraan') ? 'bg-[#C3F53C] text-[#0B0C14] font-bold border-[#C3F53C]' : 'bg-[#1B1D30] text-gray-300 border-[#23263A] hover:bg-[#23263A]'"
                                class="px-3.5 py-1.5 rounded-full border text-xs transition cursor-pointer flex items-center gap-1.5">
                            <span>🚗 Alam &amp; Kendaraan</span>
                            <span x-show="wordPacks.includes('Alam & Kendaraan')">✓</span>
                        </button>
                    </div>
                </div>

                {{-- Section 5: Room Publik Toggle --}}
                <div class="flex items-center justify-between p-3.5 rounded-xl bg-[#1B1D30] border border-[#23263A]">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-[#23263A] flex items-center justify-center text-lg">
                            🌐
                        </div>
                        <div>
                            <span class="text-xs font-bold text-white block">Room Publik</span>
                            <span class="text-[11px] text-gray-400">Bisa ditemukan dan langsung dimasuki oleh pemain lain di Lobby Hub.</span>
                        </div>
                    </div>
                    {{-- Toggle Button --}}
                    <button type="button" @click="isPublic = !isPublic"
                            :class="isPublic ? 'bg-[#C3F53C]' : 'bg-[#23263A]'"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                        <span :class="isPublic ? 'translate-x-5 bg-[#0B0C14]' : 'translate-x-0 bg-white'"
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                </div>

                {{-- Submit CTA Button --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3.5 px-6 rounded-xl bg-[#C3F53C] hover:brightness-110 text-[#0B0C14] font-extrabold text-sm tracking-wide shadow-[0_0_20px_rgba(195,245,60,0.3)] transition transform active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                        <span>BUAT LOBBY SEKARANG</span>
                        <span>➔</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
