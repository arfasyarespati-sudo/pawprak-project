@extends('layouts.app')

@section('title', 'Main Hub — Buat / Gabung Lobby | CORET! CHAOS')

@section('content')
<div class="space-y-8 pb-12">

    {{-- Hero Welcome Banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#141625] via-[#16182C] to-[#141625] border border-[#23263A] p-6 lg:p-8 shadow-2xl">
        {{-- Subtle background decoration glow --}}
        <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-[#C3F53C]/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-[#7C3AED]/15 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            {{-- Left: User Greeting & Subtitle --}}
            <div class="max-w-xl space-y-2.5">
                {{-- Status Pill --}}
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#1B1D30] border border-[#C3F53C]/30 text-[11px] font-bold text-[#C3F53C]">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#C3F53C] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#C3F53C]"></span>
                    </span>
                    <span>READY TO DRAW</span>
                    <span class="text-gray-400">•</span>
                    <span class="text-gray-300 font-medium">{{ number_format($onlineCount) }} Pemain Online</span>
                </div>

                {{-- Greeting Heading --}}
                <h1 class="font-display text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight">
                    Selamat Datang Kembali, <span class="text-[#C3F53C]">{{ $user->nickname }}</span>!
                </h1>

                <p class="text-xs sm:text-sm text-gray-400 leading-relaxed">
                    Kuasai kanvas, lempar kutukan sabotage ke kawanmu, dan cetak skor tertinggi malam ini.
                </p>
            </div>

            {{-- Right: 3 Stats Cards --}}
            <div class="grid grid-cols-3 gap-2.5 sm:gap-3 shrink-0">
                {{-- Peringkat / Level --}}
                <div class="bg-[#1B1D30]/80 border border-[#23263A] rounded-xl p-3 sm:p-4 flex flex-col justify-center min-w-[95px] sm:min-w-[110px]">
                    <div class="flex items-center gap-1.5 text-yellow-400 mb-1">
                        <span class="text-xs sm:text-sm">🏆</span>
                        <span class="text-[9px] sm:text-[10px] uppercase font-bold text-gray-400 tracking-wider">PERINGKAT</span>
                    </div>
                    <span class="text-sm sm:text-base font-extrabold text-white">Level {{ $user->level ?? 14 }}</span>
                </div>

                {{-- MMR Arcade --}}
                <div class="bg-[#1B1D30]/80 border border-[#23263A] rounded-xl p-3 sm:p-4 flex flex-col justify-center min-w-[95px] sm:min-w-[110px]">
                    <div class="flex items-center gap-1.5 text-blue-400 mb-1">
                        <span class="text-xs sm:text-sm">⭐</span>
                        <span class="text-[9px] sm:text-[10px] uppercase font-bold text-gray-400 tracking-wider">MMR ARCADE</span>
                    </div>
                    <span class="text-sm sm:text-base font-extrabold text-white">{{ number_format($user->mmr ?? 1240) }}</span>
                </div>

                {{-- Akurasi Sabotase --}}
                <div class="bg-[#1B1D30]/80 border border-[#23263A] rounded-xl p-3 sm:p-4 flex flex-col justify-center min-w-[95px] sm:min-w-[110px]">
                    <div class="flex items-center gap-1.5 text-[#C3F53C] mb-1">
                        <span class="text-xs sm:text-sm">🧪</span>
                        <span class="text-[9px] sm:text-[10px] uppercase font-bold text-gray-400 tracking-wider">AKURASI SABOTASE</span>
                    </div>
                    <span class="text-sm sm:text-base font-extrabold text-white">{{ $user->sabotage_accuracy ?? 88 }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main 2-Column Action Cards (Host Mode & Fast Entry) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Left Card: Buat Lobby Baru (Host Mode) --}}
        <div class="group relative rounded-2xl bg-[#141625] hover:bg-[#16192C] border border-[#23263A] hover:border-[#C3F53C]/40 p-6 sm:p-8 transition-all duration-300 flex flex-col justify-between shadow-xl hover:shadow-[0_0_30px_rgba(195,245,60,0.15)]">
            <div class="space-y-4">
                {{-- Top Row: Icon & Mode Badge --}}
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-xl bg-[#C3F53C] text-[#0B0C14] flex items-center justify-center text-2xl shadow-[0_0_15px_rgba(195,245,60,0.4)] group-hover:scale-110 transition-transform">
                        🚀
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full border border-[#C3F53C]/40 text-[#C3F53C] bg-[#C3F53C]/5">
                        HOST MODE
                    </span>
                </div>

                <div>
                    <span class="text-[11px] uppercase tracking-wider text-gray-400 font-bold block mb-1">
                        MULAI PERMAINAN
                    </span>
                    <h2 class="font-display text-2xl sm:text-3xl font-black text-white tracking-wide">
                        BUAT LOBBY BARU
                    </h2>
                </div>

                <div class="space-y-1.5">
                    <p class="text-sm font-bold text-gray-200">
                        Jadilah Host Kekacauan
                    </p>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Atur jumlah pemain, durasi ronde menggambar, aktifkan kutukan kustom, dan undang kawan-kawanmu secara langsung.
                    </p>
                </div>
            </div>

            {{-- CTA Button --}}
            <div class="pt-6">
                <button type="button"
                        @click="$dispatch('open-create-lobby-modal')"
                        class="w-full py-3.5 px-6 rounded-xl bg-[#C3F53C] hover:brightness-110 text-[#0B0C14] font-black text-sm tracking-wide shadow-[0_0_20px_rgba(195,245,60,0.35)] transition transform active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                    <span>BUAT LOBBY SEKARANG</span>
                    <span class="text-base font-bold">➔</span>
                </button>
            </div>
        </div>

        {{-- Right Card: Gabung Dengan Kode (Fast Entry) --}}
        <div class="group relative rounded-2xl bg-[#141625] hover:bg-[#16192C] border border-[#23263A] hover:border-[#7C3AED]/40 p-6 sm:p-8 transition-all duration-300 flex flex-col justify-between shadow-xl hover:shadow-[0_0_30px_rgba(124,58,237,0.2)]">
            <div class="space-y-4">
                {{-- Top Row: Icon & Fast Entry Badge --}}
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-xl bg-[#7C3AED] text-white flex items-center justify-center text-2xl shadow-[0_0_15px_rgba(124,58,237,0.4)] group-hover:scale-110 transition-transform">
                        🎮
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full border border-[#7C3AED]/40 text-purple-400 bg-[#7C3AED]/10">
                        FAST ENTRY
                    </span>
                </div>

                <div>
                    <span class="text-[11px] uppercase tracking-wider text-gray-400 font-bold block mb-1">
                        PUNYA UNDANGAN?
                    </span>
                    <h2 class="font-display text-2xl sm:text-3xl font-black text-white tracking-wide">
                        GABUNG DENGAN KODE
                    </h2>
                </div>

                <div class="space-y-1.5">
                    <p class="text-sm font-bold text-gray-200">
                        Punya Kode Room Teman?
                    </p>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Ketik 4 digit kode unik dari teman tongkrongan dan langsung meluncur ke kanvas tanpa ribet.
                    </p>
                </div>
            </div>

            {{-- CTA Button --}}
            <div class="pt-6">
                <button type="button"
                        @click="$dispatch('open-join-lobby-modal')"
                        class="w-full py-3.5 px-6 rounded-xl bg-[#7C3AED] hover:bg-[#6D28D9] text-white font-black text-sm tracking-wide shadow-[0_0_20px_rgba(124,58,237,0.35)] transition transform active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                    <span>MASUKKAN KODE</span>
                    <span class="text-base">⌨</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Public Rooms Section --}}
    @if($publicLobbies->isNotEmpty())
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-display text-xl font-bold text-white tracking-wide">
                        LOBBY PUBLIK AKTIF
                    </h3>
                    <p class="text-xs text-gray-400">Pilih ruangan yang sedang menunggu pemain untuk bergabung langsung</p>
                </div>
                <span class="text-xs text-gray-400 font-mono">{{ $publicLobbies->count() }} Ruangan Terbuka</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($publicLobbies as $room)
                    <div class="rounded-xl bg-[#141625] border border-[#23263A] p-4 flex flex-col justify-between hover:border-gray-600 transition">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-display font-black text-lg text-[#C3F53C] tracking-widest">{{ $room->code }}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#1B1D30] text-gray-300 border border-[#23263A] uppercase">
                                        {{ $room->game_mode }}
                                    </span>
                                </div>
                                <span class="text-xs text-gray-400 block mt-1">Host: <strong class="text-white">{{ $room->host?->nickname ?? 'Unknown' }}</strong></span>
                            </div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-[#1B1D30] border border-[#23263A] text-gray-300">
                                👥 {{ $room->players->count() }}/{{ $room->max_players }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-[#23263A]">
                            <span class="text-[11px] text-gray-400">⏱ {{ $room->round_duration }}s Waktu Giliran</span>
                            <a href="{{ route('lobby.join.link', ['code' => $room->code]) }}"
                               class="px-3 py-1.5 rounded-lg bg-[#C3F53C] text-[#0B0C14] text-xs font-bold hover:brightness-110 transition">
                                GABUNG ➔
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

{{-- Modals --}}
<x-modal-create-lobby />
<x-modal-join-lobby />
@endsection
