<div x-data="{
        open: false,
        chars: ['', '', '', ''],
        selectedAvatar: '{{ Auth::user()->avatar ?? 'kucing' }}',
        avatars: [
            { id: 'kucing', name: 'Cyber Cat' },
            { id: 'dino', name: 'Pixel Dino' },
            { id: 'raccoon', name: 'Raccoon Hacker' },
            { id: 'ghost', name: 'Ghost Spidol' },
            { id: 'blob', name: 'Blobby Slime' },
            { id: 'robot', name: 'Pixel Rudi' }
        ],

        handleInput(index, event) {
            const input = event.target;
            const val = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            this.chars[index] = val ? val.slice(-1) : '';
            input.value = this.chars[index];

            if (this.chars[index] && index < 3) {
                const nextInput = document.getElementById('code-input-' + (index + 1));
                if (nextInput) {
                    nextInput.focus();
                    nextInput.select();
                }
            }
        },

        handleKeyDown(index, event) {
            if (event.key === 'Backspace' && !this.chars[index] && index > 0) {
                const prevInput = document.getElementById('code-input-' + (index - 1));
                if (prevInput) {
                    prevInput.focus();
                }
            }
        },

        handlePaste(event) {
            event.preventDefault();
            const pasteData = (event.clipboardData || window.clipboardData).getData('text');
            const clean = pasteData.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 4);

            for (let i = 0; i < 4; i++) {
                this.chars[i] = clean[i] || '';
                const input = document.getElementById('code-input-' + i);
                if (input) input.value = this.chars[i];
            }

            const lastIndex = Math.min(clean.length, 3);
            const focusInput = document.getElementById('code-input-' + lastIndex);
            if (focusInput) focusInput.focus();
        },

        fullCode() {
            return this.chars.join('').toUpperCase();
        }
    }"
    @open-join-lobby-modal.window="open = true; $nextTick(() => { document.getElementById('code-input-0')?.focus(); })"
    @keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="join-modal-title"
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
             @click.outside="open = false"
             class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-[#141625] border border-[#23263A] p-6 sm:p-8 shadow-2xl text-left transition-all">

            {{-- Close Button --}}
            <button @click="open = false" type="button" class="absolute top-6 right-6 text-gray-400 hover:text-white transition-colors focus:outline-none">
                <span class="sr-only">Tutup</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Header --}}
            <div class="flex items-start gap-4 mb-6">
                <div class="w-12 h-12 rounded-xl bg-[#7C3AED]/20 border border-[#7C3AED]/40 flex items-center justify-center text-xl shrink-0 text-[#7C3AED]">
                    🎮
                </div>
                <div>
                    <h2 class="font-display text-2xl font-black text-white tracking-wide" id="join-modal-title">
                        MASUKKAN KODE LOBBY
                    </h2>
                    <p class="text-xs text-gray-400 mt-1 leading-relaxed">
                        Ketik 4 digit kode unik atau tempel tautan dari teman tongkrongan untuk langsung meluncur ke kanvas.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('lobby.join') }}" class="space-y-6">
                @csrf

                {{-- Hidden full code input --}}
                <input type="hidden" name="code" :value="fullCode()">
                <input type="hidden" name="avatar" :value="selectedAvatar">

                {{-- 4 Digit Character Tiles --}}
                <div>
                    <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-3 text-center">
                        4 HURUF KODE ROOM
                    </label>

                    <div class="flex items-center justify-center gap-3" @paste="handlePaste($event)">
                        <template x-for="(char, index) in chars" :key="index">
                            <input :id="'code-input-' + index"
                                   type="text"
                                   maxlength="1"
                                   :value="char"
                                   @input="handleInput(index, $event)"
                                   @keydown="handleKeyDown(index, $event)"
                                   class="w-14 h-16 sm:w-16 sm:h-20 text-center font-display text-3xl sm:text-4xl font-black uppercase rounded-2xl bg-[#0E101B] border-2 border-[#282C44] text-[#C3F53C] focus:border-[#C3F53C] focus:outline-none focus:ring-4 focus:ring-[#C3F53C]/20 shadow-inner transition-all tracking-wide selection:bg-transparent"
                                   placeholder="•"
                                   autocomplete="off">
                        </template>
                    </div>

                    <p class="text-[11px] text-gray-400 text-center mt-3">
                        Contoh: <span class="font-mono text-[#C3F53C] font-bold">CHAO</span>, <span class="font-mono text-[#C3F53C] font-bold">GREM</span>, atau <span class="font-mono text-[#C3F53C] font-bold">MAUT</span>
                    </p>
                </div>

                {{-- Avatar Selector Strip --}}
                <div class="p-4 rounded-xl bg-[#1B1D30] border border-[#23263A]">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                            <span>🎭</span> PILIH AVATAR KAMU
                        </label>
                        <span class="text-[10px] text-gray-400 capitalize" x-text="selectedAvatar"></span>
                    </div>

                    <div class="grid grid-cols-6 gap-2">
                        <template x-for="item in avatars" :key="item.id">
                            <button type="button" @click="selectedAvatar = item.id"
                                    :class="selectedAvatar === item.id ? 'ring-2 ring-[#C3F53C] scale-105 bg-[#23263A]' : 'opacity-70 hover:opacity-100 bg-[#141625]'"
                                    class="p-1 rounded-xl transition flex flex-col items-center justify-center cursor-pointer">
                                <x-player-avatar ::avatar="item.id" size="sm" glow="none" />
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div>
                    <button type="submit"
                            :disabled="fullCode().length < 4"
                            :class="fullCode().length === 4 ? 'bg-[#7C3AED] hover:bg-[#6D28D9] text-white shadow-[0_0_20px_rgba(124,58,237,0.4)] cursor-pointer' : 'bg-[#23263A] text-gray-500 cursor-not-allowed'"
                            class="w-full py-3.5 px-6 rounded-xl font-extrabold text-sm tracking-wide transition transform active:scale-[0.98] flex items-center justify-center gap-2">
                        <span>GABUNG KE LOBBY</span>
                        <span>➔</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
