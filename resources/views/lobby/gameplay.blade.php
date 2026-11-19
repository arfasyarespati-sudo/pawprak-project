@extends('layouts.app')

@section('title', "CORET! CHAOS Arena — Game #{$lobby->code}")

@section('content')
<div class="space-y-6 pb-12">
    {{-- Game Arena Top Bar --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-2xl bg-[#141625] border border-[#23263A] shadow-xl">
        <div class="flex items-center gap-3">
            <span class="w-3 h-3 rounded-full bg-red-500 animate-ping"></span>
            <div>
                <span class="text-xs font-black uppercase text-[#C3F53C] tracking-wider block">RONDE 1 / 3 • LIVE PERTANDINGAN</span>
                <span class="font-display font-black text-lg text-white">LOBBY #{{ $lobby->code }}</span>
            </div>
        </div>

        {{-- Mystery Word Hint --}}
        <div class="flex items-center gap-2 px-6 py-2 rounded-xl bg-[#0E101B] border border-[#282C44]">
            <span class="text-xs text-gray-400 uppercase font-mono">KATA:</span>
            <span class="font-mono text-lg font-black tracking-widest text-[#C3F53C]">_ _ _ _ _ _</span>
            <span class="text-[10px] text-gray-500 font-mono">(6 HURUF)</span>
        </div>

        {{-- Round Timer & Leave --}}
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 px-4 py-1.5 rounded-xl bg-[#1B1D30] border border-[#23263A]">
                <span class="text-base text-yellow-400">⏱</span>
                <span class="font-mono text-base font-black text-white">{{ $lobby->round_duration }}s</span>
            </div>
            <a href="{{ route('lobby.room', ['code' => $lobby->code]) }}"
               class="px-3.5 py-1.5 rounded-xl bg-[#1B1D30] hover:bg-[#23263A] border border-[#23263A] text-xs font-bold text-gray-300 transition">
                ← KEMBALI KE LOBBY
            </a>
        </div>
    </div>

    {{-- Main Arena Grid: Drawing Canvas + Sabotage Deck --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Left 9 Cols: Canvas & Tools --}}
        <div class="lg:col-span-9 space-y-4">
            <div class="relative rounded-2xl bg-[#141625] border border-[#23263A] p-4 shadow-2xl flex flex-col items-center">
                {{-- Canvas Area --}}
                <div class="w-full h-[450px] sm:h-[500px] rounded-xl bg-[#0E101B] border-2 border-[#23263A] relative overflow-hidden flex items-center justify-center">
                    <canvas id="gameCanvas" width="800" height="500" class="w-full h-full cursor-crosshair"></canvas>
                    <div class="absolute top-4 left-4 flex items-center gap-2 px-3 py-1 rounded-full bg-[#141625]/90 border border-[#23263A] text-xs font-bold text-gray-300 pointer-events-none">
                        <span>🖌 Giliran Menggambar:</span>
                        <strong class="text-[#C3F53C]">{{ $lobby->host?->nickname ?? 'Host' }}</strong>
                    </div>
                </div>

                {{-- Drawing Toolbar --}}
                <div class="w-full mt-4 flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-[#23263A]">
                    {{-- Color Palette --}}
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-400 mr-1">WARNA:</span>
                        <button class="w-7 h-7 rounded-full bg-white ring-2 ring-white/50 cursor-pointer"></button>
                        <button class="w-7 h-7 rounded-full bg-[#C3F53C] ring-2 ring-[#C3F53C]/50 cursor-pointer"></button>
                        <button class="w-7 h-7 rounded-full bg-[#7C3AED] ring-2 ring-[#7C3AED]/50 cursor-pointer"></button>
                        <button class="w-7 h-7 rounded-full bg-[#EF4444] ring-2 ring-red-500/50 cursor-pointer"></button>
                        <button class="w-7 h-7 rounded-full bg-[#3B82F6] ring-2 ring-blue-500/50 cursor-pointer"></button>
                        <button class="w-7 h-7 rounded-full bg-[#F59E0B] ring-2 ring-amber-500/50 cursor-pointer"></button>
                    </div>

                    {{-- Brush Sizes & Eraser --}}
                    <div class="flex items-center gap-2">
                        <button class="px-3 py-1 rounded-lg bg-[#1B1D30] text-xs font-bold text-gray-300 hover:text-white border border-[#23263A]">
                            🧹 HAPUS SEMUA
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right 3 Cols: Sabotage Cards Deck & Leaderboard --}}
        <div class="lg:col-span-3 space-y-4">
            {{-- Sabotage Deck --}}
            <div class="rounded-2xl bg-[#141625] border border-[#23263A] p-4 shadow-xl space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-sm font-black text-white tracking-wide flex items-center gap-1.5">
                        <span>🃏</span> DEK SABOTASE
                    </h3>
                    <span class="text-[10px] font-mono text-[#C3F53C] font-bold">READY</span>
                </div>

                <div class="space-y-2">
                    <button class="w-full p-2.5 rounded-xl bg-gradient-to-r from-[#1B1D30] to-[#25183A] border border-purple-500/40 text-left hover:scale-[1.02] transition cursor-pointer">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-purple-300">🌪 GEMPA LAYAR</span>
                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-purple-500/20 text-purple-300 font-mono">1x USE</span>
                        </div>
                        <p class="text-[10px] text-gray-400">Goyangkan layar seniman selama 5 detik!</p>
                    </button>

                    <button class="w-full p-2.5 rounded-xl bg-gradient-to-r from-[#1B1D30] to-[#2B1B1B] border border-red-500/40 text-left hover:scale-[1.02] transition cursor-pointer">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-red-300">🌑 KUAS BUTA</span>
                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-red-500/20 text-red-300 font-mono">1x USE</span>
                        </div>
                        <p class="text-[10px] text-gray-400">Sembunyikan kursor seniman!</p>
                    </button>

                    <button class="w-full p-2.5 rounded-xl bg-gradient-to-r from-[#1B1D30] to-[#162923] border border-[#C3F53C]/40 text-left hover:scale-[1.02] transition cursor-pointer">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-[#C3F53C]">🔄 INVERSI KANVAS</span>
                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-[#C3F53C]/20 text-[#C3F53C] font-mono">1x USE</span>
                        </div>
                        <p class="text-[10px] text-gray-400">Balikkan arah kontrol kuas gambar!</p>
                    </button>
                </div>
            </div>

            {{-- Match Players Live Points --}}
            <div class="rounded-2xl bg-[#141625] border border-[#23263A] p-4 shadow-xl space-y-2">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                    KLASEMEN SEMENTARA
                </h3>
                @foreach($lobby->players as $index => $lp)
                    <div class="flex items-center justify-between p-2 rounded-xl bg-[#0E101B] border border-[#23263A]">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold text-gray-500">#{{ $index + 1 }}</span>
                            <span class="text-xs font-bold text-white">{{ $lp->user?->nickname ?? 'Player' }}</span>
                        </div>
                        <span class="text-xs font-mono font-black text-[#C3F53C]">{{ ($index === 0 ? 120 : ($index === 1 ? 85 : 40)) }} PTS</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
