@auth
@php
    $allowedAvatars = [
        'kucing' => 'Cyber Cat',
        'dino' => 'Pixel Dino',
        'raccoon' => 'Raccoon',
        'ghost' => 'Spooky Pen',
        'blob' => 'Pink Blob',
        'robot' => 'Cyborg',
    ];
    $currentAvatar = old('avatar', Auth::user()->avatar ?: 'kucing');
    if (! array_key_exists($currentAvatar, $allowedAvatars)) {
        $currentAvatar = 'kucing';
    }
    $shouldOpenProfileModal = $errors->has('name') || $errors->has('avatar');
@endphp

<div
    x-data="{
        open: @js($shouldOpenProfileModal),
        selectedAvatar: @js($currentAvatar),
    }"
    @open-profile-modal.window="open = true"
    @keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="profile-modal-title"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-black/75 backdrop-blur-sm"
    ></div>

    {{-- Modal Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="relative z-10 w-full max-w-md rounded-2xl bg-[#1A1A2D] border border-[#333347] p-6 shadow-2xl"
    >
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 id="profile-modal-title" class="font-serif text-xl font-bold text-[#E2E0FB]">
                    Edit Profil Pemain
                </h2>
                <p class="text-xs text-[#C3C9B1] mt-0.5">
                    Ubah nama panggilan dan foto avatar kamu sebelum masuk arena.
                </p>
            </div>
            <button
                type="button"
                @click="open = false"
                class="w-8 h-8 rounded-lg bg-[#28283C] hover:bg-[#333347] text-[#C3C9B1] hover:text-white flex items-center justify-center transition cursor-pointer"
                aria-label="Tutup modal profil"
            >
                &times;
            </button>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Display Name --}}
            <div>
                <label for="profile-name-input" class="block text-xs font-bold uppercase tracking-wider text-[#C3C9B1] mb-2">
                    Nama Panggilan
                </label>
                <input
                    id="profile-name-input"
                    type="text"
                    name="name"
                    maxlength="50"
                    required
                    value="{{ old('name', Auth::user()->nickname) }}"
                    placeholder="Masukkan nama panggilan..."
                    class="w-full rounded-xl bg-[#0C0C1F] border border-[#333347] focus:border-[#BDF454] px-4 py-2.5 text-sm font-medium text-[#E2E0FB] placeholder-[#8D937D] focus:outline-none transition"
                >
                @error('name')
                    <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Avatar Picker --}}
            <div>
                <span class="block text-xs font-bold uppercase tracking-wider text-[#C3C9B1] mb-2.5">
                    Pilih Avatar
                </span>
                <input type="hidden" name="avatar" x-model="selectedAvatar">

                <div class="grid grid-cols-3 gap-3">
                    @foreach ($allowedAvatars as $avatarKey => $avatarLabel)
                        <button
                            type="button"
                            @click="selectedAvatar = @js($avatarKey)"
                            :class="selectedAvatar === @js($avatarKey)
                                ? 'border-[#BDF454] bg-[#28283C] ring-2 ring-[#BDF454]/60'
                                : 'border-[#333347] bg-[#0C0C1F] hover:border-[#D1BCFF]/50'"
                            class="flex flex-col items-center gap-2 rounded-xl border p-3 transition cursor-pointer"
                        >
                            <x-player-avatar :avatar="$avatarKey" size="md" glow="none" />
                            <span
                                :class="selectedAvatar === @js($avatarKey) ? 'text-[#BDF454]' : 'text-[#C3C9B1]'"
                                class="text-[11px] font-bold"
                            >
                                {{ $avatarLabel }}
                            </span>
                        </button>
                    @endforeach
                </div>

                @error('avatar')
                    <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    @click="open = false"
                    class="px-4 py-2.5 rounded-xl bg-[#28283C] hover:bg-[#333347] text-xs font-bold text-[#E2E0FB] transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-[#BDF454] hover:brightness-110 text-xs font-extrabold uppercase tracking-wider text-[#111124] transition cursor-pointer"
                >
                    Simpan Profil
                </button>
            </div>
        </form>
    </div>
</div>
@endauth
