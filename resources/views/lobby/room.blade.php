@extends('layouts.app')

@section('title', "Ruang Tunggu Lobby #{$lobby->code} — CORET! CHAOS")

@section('content')
<div x-data="lobbyRoom({
        code: '{{ $lobby->code }}',
        syncUrl: '{{ route('lobby.sync', ['code' => $lobby->code]) }}',
        chatUrl: '{{ route('lobby.chat', ['code' => $lobby->code]) }}',
        readyUrl: '{{ route('lobby.ready', ['code' => $lobby->code]) }}',
        startUrl: '{{ route('lobby.start', ['code' => $lobby->code]) }}',
        leaveUrl: '{{ route('lobby.leave', ['code' => $lobby->code]) }}',
        settingsUrl: '{{ route('lobby.settings', ['code' => $lobby->code]) }}',
        currentUserId: {{ Auth::id() }},
        isHost: {{ $isHost ? 'true' : 'false' }},
        inviteUrl: '{{ url('/join/' . $lobby->code) }}',
        initialSlots: @js($lobby->players->keyBy('slot_number')),
        maxPlayers: {{ $lobby->max_players }},
        roundDuration: {{ $lobby->round_duration }},
        gameMode: '{{ $lobby->game_mode }}',
        wordPacks: @js($lobby->word_packs ?? ['Kultur Pop Indo']),
        isPublic: {{ $lobby->is_public ? 'true' : 'false' }}
    })"
    x-init="init()"
    class="space-y-6 pb-28">

    {{-- Top Header Sub-Bar: Room Meta & Big Code Tile Display --}}
    <div class="relative overflow-hidden rounded-2xl bg-[#141625] border border-[#23263A] p-6 lg:p-8 shadow-2xl">
        {{-- Subtle radial glows --}}
        <div class="absolute -top-16 -left-16 w-60 h-60 bg-[#C3F53C]/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -right-16 w-60 h-60 bg-[#7C3AED]/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

            {{-- Left Side: Meta Text & Big 4-Letter Code --}}
            <div class="space-y-4">
                {{-- Meta Information Badge --}}
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-gray-300">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#1B1D30] border border-[#23263A] text-emerald-400">
                        <span class="w-2 h-2 rounded-full bg-[#C3F53C] animate-pulse"></span>
                        <span x-text="`MENUNGGU PEMAIN (${totalPlayers}/${maxPlayers} TERISI)`">MENUNGGU PEMAIN ({{ $lobby->players->count() }}/{{ $lobby->max_players }} TERISI)</span>
                    </span>
                    <span class="text-gray-500">•</span>
                    <span class="text-gray-300">HOST: <strong class="text-white" x-text="hostName">{{ $lobby->host?->nickname ?? 'Unknown' }}</strong> <span x-show="isHost" class="text-[#C3F53C]">(Kamu)</span></span>
                    <span class="text-gray-500">•</span>
                    <span class="text-gray-400">MODE: <span class="uppercase text-white font-bold" x-text="`${maxPlayers} PEMAIN • ${roundDuration} DETIK SABOTASE`">{{ $lobby->max_players }} PEMAIN • {{ $lobby->round_duration }} DETIK SABOTASE</span></span>
                </div>

                {{-- Big 4-Tile Code Display --}}
                <div class="flex items-center gap-4">
                    <span class="font-display font-black text-xs sm:text-sm uppercase tracking-widest text-gray-400 shrink-0">
                        KODE LOBBY:
                    </span>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <template x-for="(char, idx) in code.split('')" :key="idx">
                            <div class="w-12 h-14 sm:w-16 sm:h-18 rounded-xl sm:rounded-2xl bg-[#0E101B] border-2 border-[#282C44] flex items-center justify-center font-display text-3xl sm:text-4xl font-black text-[#C3F53C] shadow-inner select-all">
                                <span x-text="char"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <p class="text-xs text-gray-400 leading-relaxed max-w-xl">
                    Bagikan kode ini atau tautan instan ke teman mabar discord, whatsapp, atau squad gambar kamu!
                </p>
            </div>

            {{-- Right Side: Copy Buttons & Link Pill --}}
            <div class="flex flex-col items-start lg:items-end gap-3 shrink-0">
                {{-- Neon Lime Copy Code Button --}}
                <button type="button"
                        @click="copyCode()"
                        class="px-6 py-3 rounded-xl bg-[#C3F53C] hover:brightness-110 text-[#0B0C14] font-black text-xs tracking-wider uppercase shadow-[0_0_20px_rgba(195,245,60,0.35)] transition transform active:scale-95 flex items-center gap-2 cursor-pointer">
                    <span x-text="copiedCode ? '✓' : '📋'">📋</span>
                    <span x-text="copiedCode ? 'KODE BERHASIL DISALIN!' : 'SALIN KODE LOBBY'">SALIN KODE LOBBY</span>
                </button>

                {{-- Invite URL Pill --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#0E101B] border border-[#282C44] text-xs font-mono text-gray-300">
                    <span class="text-gray-500">🔗</span>
                    <span class="text-[11px] text-gray-300 max-w-[200px] sm:max-w-xs truncate" x-text="inviteUrl">{{ url('/join/' . $lobby->code) }}</span>
                    <button type="button" @click="copyLink()" class="ml-2 px-2 py-0.5 rounded bg-[#1B1D30] hover:bg-[#23263A] text-[#C3F53C] font-bold text-[10px] transition cursor-pointer">
                        <span x-text="copiedLink ? '✓ SALIN' : 'SALIN'">SALIN</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Grid: 8 Cols Player Slots + 4 Cols Live Chat --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- Left 8 Cols: Slot Pemain Grid & Rules Card --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Player Slots Header --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h2 class="font-display text-xl font-black text-white tracking-wide">
                        SLOT PEMAIN
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#C3F53C]/10 text-[#C3F53C] border border-[#C3F53C]/30"
                          x-text="`${readyCount} DARI ${totalPlayers} SIAP`">
                        {{ $lobby->readyCount() }} DARI {{ $lobby->players->count() }} SIAP
                    </span>
                </div>
                <span class="text-xs text-gray-400 font-mono">
                    MINIMAL 3 UNTUK START
                </span>
            </div>

            {{-- Grid of Player Slot Cards (Dynamic 1..max_players) --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                <template x-for="slot in slots" :key="slot.slot_number">
                    <div>
                        {{-- OCCUPIED SLOT CARD --}}
                        <template x-if="slot.is_occupied">
                            <div class="relative rounded-2xl bg-[#141625] border p-4 flex flex-col justify-between h-48 transition-all duration-300 shadow-lg"
                                 :class="slot.is_current_user ? 'border-[#C3F53C]/70 shadow-[0_0_20px_rgba(195,245,60,0.15)] ring-1 ring-[#C3F53C]/50' : 'border-[#23263A] hover:border-gray-600'">

                                {{-- Top Row: Role Badge & Level --}}
                                <div class="flex items-center justify-between">
                                    <template x-if="slot.is_host">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-[#C3F53C] text-[#0B0C14]">
                                            <span x-text="slot.is_current_user ? 'HOST (KAMU)' : 'HOST'">HOST</span>
                                        </span>
                                    </template>
                                    <template x-if="!slot.is_host">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider"
                                              :class="slot.slot_number % 2 === 0 ? 'bg-[#7C3AED]/20 text-purple-400 border border-[#7C3AED]/40' : 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/40'"
                                              x-text="slot.slot_number % 2 === 0 ? 'PETARUNG' : 'PEMULA'">
                                        </span>
                                    </template>

                                    <span class="text-[10px] font-extrabold text-gray-400" x-text="`LV.${slot.level}`">LV.1</span>
                                </div>

                                {{-- Center: Avatar, Nickname & Title --}}
                                <div class="flex flex-col items-center text-center my-auto">
                                    <div class="relative mb-2">
                                        <div class="w-14 h-14 rounded-2xl overflow-hidden bg-[#1B1D30] flex items-center justify-center p-1"
                                             :class="slot.is_ready ? 'ring-2 ring-[#C3F53C] shadow-[0_0_15px_rgba(195,245,60,0.3)]' : 'ring-1 ring-[#23263A]'">
                                            {{-- Render dynamic avatar icon --}}
                                            <span class="text-3xl select-none" x-text="getAvatarEmoji(slot.avatar)">🐱</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-black text-white truncate max-w-[120px]" x-text="slot.name">Player</span>
                                    <span class="text-[10px] text-gray-400 truncate max-w-[120px]" x-text="slot.title">Pemula Spidol</span>
                                </div>

                                {{-- Bottom Row: Ready Status Badge --}}
                                <div class="pt-2 border-t border-[#23263A] flex items-center justify-center">
                                    <template x-if="slot.is_host">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-[#C3F53C] flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#C3F53C]"></span>
                                            <span>SIAP TEMPUR</span>
                                        </span>
                                    </template>
                                    <template x-if="!slot.is_host && slot.is_ready">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-[#C3F53C] flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#C3F53C]"></span>
                                            <span>SIAP!</span>
                                        </span>
                                    </template>
                                    <template x-if="!slot.is_host && !slot.is_ready">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                            <span>BELUM SIAP</span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- EMPTY SLOT CARD --}}
                        <template x-if="!slot.is_occupied">
                            <div @click="copyLink()"
                                 class="group relative rounded-2xl bg-[#0E101B]/60 hover:bg-[#141625] border-2 border-dashed border-[#23263A] hover:border-[#C3F53C]/50 p-4 flex flex-col justify-between h-48 transition-all cursor-pointer text-center">

                                <div class="flex items-center justify-between text-[10px] font-bold text-gray-500 font-mono">
                                    <span x-text="`SLOT 0${slot.slot_number}`">SLOT</span>
                                    <span>KOSONG</span>
                                </div>

                                <div class="flex flex-col items-center justify-center my-auto space-y-1">
                                    <div class="w-10 h-10 rounded-full bg-[#141625] border border-[#23263A] group-hover:border-[#C3F53C]/40 flex items-center justify-center text-gray-500 group-hover:text-[#C3F53C] transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                        </svg>
                                    </div>
                                    <span class="text-[11px] font-medium text-gray-500 group-hover:text-gray-300">Slot Kosong</span>
                                </div>

                                <div class="pt-2 border-t border-[#23263A]/40">
                                    <span class="text-[10px] font-bold text-gray-400 group-hover:text-[#C3F53C] flex items-center justify-center gap-1">
                                        <span>+</span> INVITE
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Aturan Permainan Saat Ini Card --}}
            <div class="rounded-2xl bg-[#141625] border border-[#23263A] p-5 shadow-lg space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="text-base text-[#C3F53C]">⚙️</span>
                        <h3 class="font-display text-sm font-bold text-white tracking-wide">
                            Aturan Permainan Saat Ini
                        </h3>
                    </div>
                    <span class="text-[11px] font-mono text-gray-400 uppercase" x-text="`MODE: ${gameMode}`">{{ $lobby->game_mode }}</span>
                </div>

                <p class="text-xs text-gray-400">
                    <strong class="text-gray-200">Sabotase Aktif:</strong>
                    <template x-if="gameMode === 'classic'">
                        <span>Layar Terbalik, Gempa Kanvas, Tinta Tumpah • Waktu Tebak: <span x-text="roundDuration">{{ $lobby->round_duration }}</span>s</span>
                    </template>
                    <template x-if="gameMode === 'relaxed'">
                        <span>Mode Santai (Murni Menggambar Tanpa Kutukan Sabotase) • Waktu Tebak: <span x-text="roundDuration">{{ $lobby->round_duration }}</span>s</span>
                    </template>
                    <template x-if="gameMode === 'extreme'">
                        <span>Kutukan Ekstrem (2x Gempa Turbo & Kuas Buta) • Waktu Tebak: <span x-text="roundDuration">{{ $lobby->round_duration }}</span>s</span>
                    </template>
                </p>

                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <template x-for="pack in wordPacks" :key="pack">
                        <span class="px-2.5 py-1 rounded-lg bg-[#1B1D30] border border-[#23263A] text-[10px] font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1">
                            <span>📦</span> PAKET KATA: <span x-text="pack"></span>
                        </span>
                    </template>
                    <span class="px-2.5 py-1 rounded-lg bg-[#7C3AED]/15 border border-[#7C3AED]/30 text-[10px] font-bold text-purple-300 uppercase tracking-wider">
                        ⚡ KARTU CHAOS: x3
                    </span>
                </div>
            </div>

        </div>

        {{-- Right 4 Cols: Obrolan Lobi & Tips Chaos --}}
        <div class="lg:col-span-4 space-y-4">

            {{-- Live Chat Box --}}
            <div class="rounded-2xl bg-[#141625] border border-[#23263A] shadow-xl flex flex-col h-[520px]">
                {{-- Chat Header --}}
                <div class="p-4 border-b border-[#23263A] flex items-center justify-between bg-[#16182C]">
                    <div class="flex items-center gap-2">
                        <h3 class="font-display text-sm font-black text-white tracking-wide">
                            OBROLAN LOBI
                        </h3>
                        <span class="w-2 h-2 rounded-full bg-[#C3F53C] animate-ping"></span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] font-mono font-bold text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>LIVE SYNC</span>
                    </div>
                </div>

                {{-- Messages Scroll Area --}}
                <div id="chat-messages-container" class="flex-1 p-4 overflow-y-auto space-y-3">
                    <template x-for="msg in messages" :key="msg.id">
                        <div>
                            {{-- SYSTEM MESSAGE --}}
                            <template x-if="msg.sender_type === 'system'">
                                <div class="p-2.5 rounded-xl bg-[#0E101B]/80 border border-[#23263A] text-center my-1">
                                    <div class="flex items-center justify-center gap-2 mb-0.5">
                                        <span class="text-[9px] font-black uppercase tracking-wider text-cyan-400">SYSTEM</span>
                                        <span class="text-[9px] font-mono text-gray-500" x-text="msg.time"></span>
                                    </div>
                                    <p class="text-[11px] text-gray-300 italic" x-text="msg.message"></p>
                                </div>
                            </template>

                            {{-- CURRENT USER MESSAGE (Right-aligned, Neon Lime Bubble) --}}
                            <template x-if="msg.sender_type === 'player' && msg.is_current_user">
                                <div class="flex flex-col items-end space-y-1">
                                    <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                                        <span class="font-bold text-[#C3F53C]" x-text="msg.sender_name">Kamu</span>
                                        <span class="font-mono text-[9px]" x-text="msg.time"></span>
                                    </div>
                                    <div class="max-w-[85%] rounded-2xl rounded-tr-none bg-[#C3F53C] text-[#0B0C14] px-3.5 py-2 text-xs font-semibold shadow-md break-words">
                                        <span x-text="msg.message"></span>
                                    </div>
                                </div>
                            </template>

                            {{-- OTHER PLAYER MESSAGE (Left-aligned, Dark Slate Bubble) --}}
                            <template x-if="msg.sender_type === 'player' && !msg.is_current_user">
                                <div class="flex flex-col items-start space-y-1">
                                    <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                                        <span class="font-bold text-white" x-text="msg.sender_name">Player</span>
                                        <span class="font-mono text-[9px]" x-text="msg.time"></span>
                                    </div>
                                    <div class="max-w-[85%] rounded-2xl rounded-tl-none bg-[#1B1D30] border border-[#23263A] text-gray-200 px-3.5 py-2 text-xs break-words">
                                        <span x-text="msg.message"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Chat Input Form --}}
                <div class="p-3 border-t border-[#23263A] bg-[#16182C]">
                    <form @submit.prevent="sendChatMessage()" class="flex items-center gap-2">
                        <input type="text"
                               x-model="chatInput"
                               placeholder="Ketik obrolan atau emoji..."
                               maxlength="250"
                               class="flex-1 bg-[#0E101B] border border-[#282C44] rounded-xl px-3.5 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-[#C3F53C] transition">
                        <button type="submit"
                                :disabled="!chatInput.trim()"
                                :class="chatInput.trim() ? 'bg-[#C3F53C] text-[#0B0C14] hover:brightness-110 cursor-pointer' : 'bg-[#23263A] text-gray-500 cursor-not-allowed'"
                                class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm transition shrink-0">
                            ➤
                        </button>
                    </form>
                </div>
            </div>

            {{-- Tips Chaos Card --}}
            <div class="p-4 rounded-xl bg-[#141625] border border-[#23263A] text-xs text-gray-400 flex items-start gap-2.5">
                <span class="text-base text-yellow-400 shrink-0">💡</span>
                <p>
                    <strong class="text-gray-200">Tips Chaos:</strong> Gunakan sabotase "Layar Miring" di 10 detik terakhir saat pemain lain hampir menebak gambar!
                </p>
            </div>

        </div>
    </div>

    {{-- Floating Bottom Action Bar (Fixed at Bottom) --}}
    <div class="fixed bottom-0 left-0 right-0 z-40 bg-[#0B0C14]/95 backdrop-blur-lg border-t border-[#23263A] px-4 lg:px-8 py-3.5 shadow-2xl">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">

            {{-- Left: Status Lobi Indicator --}}
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="w-10 h-10 rounded-xl bg-[#1B1D30] border border-[#23263A] flex items-center justify-center text-lg shrink-0">
                    <span x-text="canStart ? '🚀' : '⏳'">🚀</span>
                </div>
                <div class="leading-tight">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black uppercase tracking-wider"
                              :class="canStart ? 'text-[#C3F53C]' : 'text-yellow-400'"
                              x-text="canStart ? 'STATUS LOBI: SIAP DIMULAI!' : 'STATUS LOBI: MENUNGGU PEMAIN'">
                            STATUS LOBI: SIAP DIMULAI!
                        </span>
                        <span class="text-[10px] font-mono text-gray-400" x-text="`(${totalPlayers}/${maxPlayers})`"></span>
                    </div>
                    <p class="text-[11px] text-gray-400"
                       x-text="canStart ? 'Syarat minimal 2 pemain terpenuhi. Kamu bisa memulai sekarang atau menunggu slot penuh.' : `Kurang ${Math.max(0, 2 - totalPlayers)} pemain lagi untuk syarat minimal pertandingan.`">
                        Syarat minimal 2 pemain terpenuhi.
                    </p>
                </div>
            </div>

            {{-- Center & Right Controls --}}
            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">

                {{-- Host Settings Button --}}
                <template x-if="isHost">
                    <button type="button"
                            @click="openSettingsModal = true"
                            class="px-4 py-2.5 rounded-xl bg-[#1B1D30] hover:bg-[#23263A] border border-[#23263A] text-xs font-bold text-gray-300 hover:text-white transition flex items-center gap-1.5 cursor-pointer">
                        <span>⚙</span>
                        <span>PENGATURAN</span>
                    </button>
                </template>

                {{-- Leave Button --}}
                <form method="POST" :action="leaveUrl">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Yakin ingin meninggalkan ruang tunggu lobi?')"
                            class="px-4 py-2.5 rounded-xl bg-[#1B1D30] hover:bg-red-500/20 border border-[#23263A] hover:border-red-500/40 text-xs font-bold text-gray-400 hover:text-red-400 transition flex items-center gap-1.5 cursor-pointer">
                        <span>🚪</span>
                        <span>KELUAR</span>
                    </button>
                </form>

                {{-- Host: Mulai Permainan Button --}}
                <template x-if="isHost">
                    <button type="button"
                            @click="startGame()"
                            :disabled="!canStart"
                            :class="canStart ? 'bg-[#C3F53C] hover:brightness-110 text-[#0B0C14] shadow-[0_0_20px_rgba(195,245,60,0.4)] cursor-pointer' : 'bg-[#23263A] text-gray-500 cursor-not-allowed'"
                            class="px-6 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide uppercase transition transform active:scale-95 flex items-center gap-2">
                        <span>🚀</span>
                        <span>MULAI PERMAINAN</span>
                    </button>
                </template>

                {{-- Guest: Siap / Batal Siap Toggle Button --}}
                <template x-if="!isHost">
                    <button type="button"
                            @click="toggleReady()"
                            :class="currentUserReady ? 'bg-[#C3F53C] text-[#0B0C14] shadow-[0_0_15px_rgba(195,245,60,0.3)]' : 'bg-[#1B1D30] hover:bg-[#23263A] text-white border border-[#23263A]'"
                            class="px-6 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide uppercase transition transform active:scale-95 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full" :class="currentUserReady ? 'bg-[#0B0C14]' : 'bg-gray-400'"></span>
                        <span x-text="currentUserReady ? 'BATAL SIAP' : 'SIAP TEMPUR!'">SIAP TEMPUR!</span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- Host Edit Settings Modal --}}
    <div x-show="openSettingsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#0B0C14]/80 backdrop-blur-md" @click="openSettingsModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-2xl bg-[#141625] border border-[#23263A] p-6 shadow-2xl">
                <button @click="openSettingsModal = false" class="absolute top-5 right-5 text-gray-400 hover:text-white">&times;</button>
                <h3 class="font-display text-xl font-bold text-white mb-4">⚙️ Edit Pengaturan Lobi</h3>

                <form @submit.prevent="submitSettings()" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-300 block mb-1">Durasi Ronde Menggambar</label>
                        <select x-model="modalRoundDuration" class="w-full bg-[#0E101B] border border-[#282C44] rounded-xl px-3 py-2 text-xs text-white">
                            <option value="60">60 Detik (Cepat)</option>
                            <option value="80">80 Detik (Standar)</option>
                            <option value="100">100 Detik</option>
                            <option value="120">120 Detik (Santai)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-300 block mb-1">Mode Permainan</label>
                        <select x-model="modalGameMode" class="w-full bg-[#0E101B] border border-[#282C44] rounded-xl px-3 py-2 text-xs text-white">
                            <option value="classic">Chaos Klasik (Semua Sabotase Aktif)</option>
                            <option value="relaxed">Mode Santai (Tanpa Sabotase)</option>
                            <option value="extreme">Kutukan Ekstrem (2x Speed & Sabotase Turbo)</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#1B1D30] border border-[#23263A]">
                        <span class="text-xs font-bold text-white">Room Publik</span>
                        <input type="checkbox" x-model="modalIsPublic" class="w-4 h-4 accent-[#C3F53C]">
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-[#C3F53C] text-[#0B0C14] font-black text-xs tracking-wider uppercase hover:brightness-110 cursor-pointer">
                        SIMPAN PERUBAHAN
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function lobbyRoom(config) {
    return {
        code: config.code,
        syncUrl: config.syncUrl,
        chatUrl: config.chatUrl,
        readyUrl: config.readyUrl,
        startUrl: config.startUrl,
        leaveUrl: config.leaveUrl,
        settingsUrl: config.settingsUrl,
        currentUserId: config.currentUserId,
        isHost: config.isHost,
        inviteUrl: config.inviteUrl,

        maxPlayers: config.maxPlayers,
        roundDuration: config.roundDuration,
        gameMode: config.gameMode,
        wordPacks: config.wordPacks,
        isPublic: config.isPublic,

        totalPlayers: 1,
        readyCount: 1,
        canStart: false,
        hostName: 'Host',
        currentUserReady: config.isHost,

        slots: [],
        messages: [],
        chatInput: '',
        copiedCode: false,
        copiedLink: false,
        pollTimer: null,
        openSettingsModal: false,

        modalRoundDuration: config.roundDuration,
        modalGameMode: config.gameMode,
        modalIsPublic: config.isPublic,

        init() {
            this.fetchSync();
            this.pollTimer = setInterval(() => {
                this.fetchSync();
            }, 2000);
        },

        getAvatarEmoji(avatar) {
            const map = {
                'kucing': '🐱',
                'cat': '🐱',
                'dino': '🦖',
                'raccoon': '🦝',
                'ghost': '👻',
                'blob': '👾',
                'robot': '🤖',
                'ninja': '🥷',
            };
            return map[avatar?.toLowerCase()] || '🐱';
        },

        async fetchSync() {
            try {
                const response = await fetch(this.syncUrl, {
                    headers: { 'Accept': 'application/json' }
                });

                if (response.status === 404 || response.status === 403) {
                    clearInterval(this.pollTimer);
                    window.location.href = '{{ route('lobby.hub') }}';
                    return;
                }

                const data = await response.json();

                // If game has started, redirect to game play page!
                if (data.status === 'playing' && data.game_url) {
                    clearInterval(this.pollTimer);
                    window.location.href = data.game_url;
                    return;
                }

                this.slots = data.slots || [];
                this.maxPlayers = data.lobby.max_players;
                this.roundDuration = data.lobby.round_duration;
                this.gameMode = data.lobby.game_mode;
                this.wordPacks = data.lobby.word_packs;
                this.isPublic = data.lobby.is_public;
                this.totalPlayers = data.lobby.total_players;
                this.readyCount = data.lobby.ready_count;
                this.canStart = data.lobby.can_start;
                this.isHost = data.current_user.is_host;
                this.currentUserReady = data.current_user.is_ready;

                const hostSlot = this.slots.find(s => s.is_host);
                if (hostSlot) {
                    this.hostName = hostSlot.name;
                }

                // Update messages and scroll if new messages
                const prevMsgCount = this.messages.length;
                this.messages = data.messages || [];
                if (this.messages.length > prevMsgCount) {
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                }
            } catch (err) {
                console.error('Error in lobby sync:', err);
            }
        },

        scrollToBottom() {
            const container = document.getElementById('chat-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },

        async sendChatMessage() {
            const msg = this.chatInput.trim();
            if (!msg) return;

            this.chatInput = '';
            try {
                const res = await fetch(this.chatUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: msg })
                });
                const data = await res.json();
                if (data.success && data.message) {
                    this.messages.push(data.message);
                    this.$nextTick(() => this.scrollToBottom());
                }
            } catch (e) {
                console.error('Failed to send chat', e);
            }
        },

        async toggleReady() {
            try {
                const res = await fetch(this.readyUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.currentUserReady = data.is_ready;
                    this.fetchSync();
                }
            } catch (e) {
                console.error('Failed to toggle ready', e);
            }
        },

        async startGame() {
            if (!this.canStart) return;
            try {
                const res = await fetch(this.startUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                }
            } catch (e) {
                console.error('Failed to start game', e);
            }
        },

        async submitSettings() {
            try {
                const res = await fetch(this.settingsUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        round_duration: this.modalRoundDuration,
                        game_mode: this.modalGameMode,
                        is_public: this.modalIsPublic ? 1 : 0
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.openSettingsModal = false;
                    this.fetchSync();
                }
            } catch (e) {
                console.error('Failed to update settings', e);
            }
        },

        copyCode() {
            navigator.clipboard.writeText(this.code).then(() => {
                this.copiedCode = true;
                setTimeout(() => this.copiedCode = false, 2500);
            });
        },

        copyLink() {
            navigator.clipboard.writeText(this.inviteUrl).then(() => {
                this.copiedLink = true;
                setTimeout(() => this.copiedLink = false, 2500);
            });
        }
    };
}
</script>
@endpush
@endsection
