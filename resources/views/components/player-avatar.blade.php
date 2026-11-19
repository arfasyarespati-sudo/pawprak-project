@props([
    'avatar' => 'kucing',
    'size' => 'md',
    'glow' => 'lime',
    'class' => '',
])

@php
    $sizeClasses = match($size) {
        'xs' => 'w-7 h-7 text-xs',
        'sm' => 'w-9 h-9 text-sm',
        'md' => 'w-12 h-12 text-base',
        'lg' => 'w-14 h-14 text-xl',
        'xl' => 'w-16 h-16 text-2xl',
        '2xl' => 'w-20 h-20 text-3xl',
        default => 'w-12 h-12 text-base',
    };

    $glowClasses = match($glow) {
        'lime' => 'ring-2 ring-[#C3F53C] shadow-[0_0_15px_rgba(195,245,60,0.4)]',
        'purple' => 'ring-2 ring-[#8B5CF6] shadow-[0_0_15px_rgba(139,92,246,0.4)]',
        'cyan' => 'ring-2 ring-[#06B6D4] shadow-[0_0_15px_rgba(6,182,212,0.4)]',
        'pink' => 'ring-2 ring-[#EC4899] shadow-[0_0_15px_rgba(236,72,153,0.4)]',
        'none' => 'ring-1 ring-[#23263A]',
        default => 'ring-2 ring-[#C3F53C]/60 shadow-[0_0_10px_rgba(195,245,60,0.25)]',
    };

    $avatarKey = strtolower($avatar ?: 'kucing');
@endphp

<div {{ $attributes->merge(['class' => "relative inline-flex items-center justify-center rounded-2xl overflow-hidden bg-[#1B1D30] shrink-0 {$sizeClasses} {$glowClasses} {$class}"]) }}>
    @if(str_contains($avatarKey, 'kucing') || str_contains($avatarKey, 'cat'))
        {{-- Cyber Cat with Neon Headset --}}
        <svg class="w-full h-full p-1" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" rx="20" fill="#131422"/>
            <path d="M26 40L38 18L50 34" fill="#06B6D4" stroke="#C3F53C" stroke-width="3"/>
            <path d="M74 40L62 18L50 34" fill="#06B6D4" stroke="#C3F53C" stroke-width="3"/>
            <circle cx="50" cy="56" r="30" fill="#1E293B" stroke="#06B6D4" stroke-width="3"/>
            <ellipse cx="40" cy="52" rx="7" ry="9" fill="#C3F53C"/>
            <ellipse cx="60" cy="52" rx="7" ry="9" fill="#C3F53C"/>
            <circle cx="41" cy="52" r="3" fill="#0B0C14"/>
            <circle cx="61" cy="52" r="3" fill="#0B0C14"/>
            <polygon points="50,60 46,65 54,65" fill="#F43F5E"/>
            <path d="M44 68C47 71 53 71 56 68" stroke="#F8FAFC" stroke-width="2" stroke-linecap="round"/>
            {{-- Headset band --}}
            <path d="M20 54C20 32 80 32 80 54" stroke="#C3F53C" stroke-width="5" stroke-linecap="round"/>
            <rect x="15" y="48" width="10" height="18" rx="5" fill="#C3F53C"/>
            <rect x="75" y="48" width="10" height="18" rx="5" fill="#C3F53C"/>
        </svg>
    @elseif(str_contains($avatarKey, 'dino'))
        {{-- Pixel Cyber Dino --}}
        <svg class="w-full h-full p-1" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" rx="20" fill="#1A132F"/>
            <path d="M30 75V45H40V30H75V55H65V65H55V75H30Z" fill="#8B5CF6"/>
            {{-- Neon Visor --}}
            <rect x="52" y="38" width="26" height="10" rx="3" fill="#C3F53C" stroke="#7C3AED" stroke-width="2"/>
            <line x1="55" y1="43" x2="75" y2="43" stroke="#0B0C14" stroke-width="2"/>
            <rect x="42" y="60" width="8" height="6" fill="#F43F5E"/>
            <rect x="35" y="75" width="8" height="12" fill="#7C3AED"/>
            <rect x="50" y="75" width="8" height="12" fill="#7C3AED"/>
            <rect x="25" y="45" width="6" height="6" fill="#FDE047"/>
            <rect x="20" y="52" width="6" height="6" fill="#FDE047"/>
        </svg>
    @elseif(str_contains($avatarKey, 'raccoon'))
        {{-- Raccoon Hacker with Neon Blue Mask --}}
        <svg class="w-full h-full p-1" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" rx="20" fill="#111827"/>
            <path d="M25 35L35 15L45 32" fill="#4B5563" stroke="#06B6D4" stroke-width="2"/>
            <path d="M75 35L65 15L55 32" fill="#4B5563" stroke="#06B6D4" stroke-width="2"/>
            <circle cx="50" cy="54" r="28" fill="#374151"/>
            {{-- Mask --}}
            <path d="M26 48C32 44 42 45 50 48C58 45 68 44 74 48C76 56 70 65 50 65C30 65 24 56 26 48Z" fill="#1F2937" stroke="#06B6D4" stroke-width="2"/>
            <ellipse cx="40" cy="54" rx="5" ry="6" fill="#06B6D4"/>
            <ellipse cx="60" cy="54" rx="5" ry="6" fill="#06B6D4"/>
            <circle cx="40" cy="54" r="2" fill="#FFFFFF"/>
            <circle cx="60" cy="54" r="2" fill="#FFFFFF"/>
            <polygon points="50,60 47,63 53,63" fill="#111827"/>
        </svg>
    @elseif(str_contains($avatarKey, 'ghost'))
        {{-- Cute Spooky Ghost with Spidol --}}
        <svg class="w-full h-full p-1" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" rx="20" fill="#111C24"/>
            <path d="M50 20C35 20 28 32 28 50V75C28 78 33 80 36 76C39 72 43 72 46 76C49 80 54 80 57 76C60 72 64 72 67 76C70 80 75 78 75 75V50C75 32 65 20 50 20Z" fill="#F8FAFC" stroke="#38BDF8" stroke-width="2"/>
            <circle cx="43" cy="46" r="5" fill="#0284C7"/>
            <circle cx="59" cy="46" r="5" fill="#0284C7"/>
            <ellipse cx="51" cy="56" rx="4" ry="5" fill="#0F172A"/>
            {{-- Tiny Neon Pen --}}
            <rect x="68" y="55" width="6" height="22" rx="2" transform="rotate(-30 68 55)" fill="#C3F53C" stroke="#0B0C14" stroke-width="1.5"/>
        </svg>
    @elseif(str_contains($avatarKey, 'blob'))
        {{-- Pink Slime Blobby --}}
        <svg class="w-full h-full p-1" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" rx="20" fill="#251226"/>
            <path d="M50 24C32 24 22 40 22 60C22 76 34 82 50 82C66 82 78 76 78 60C78 40 68 24 50 24Z" fill="#EC4899" stroke="#F472B6" stroke-width="3"/>
            <circle cx="40" cy="54" r="5" fill="#180719"/>
            <circle cx="60" cy="54" r="5" fill="#180719"/>
            <circle cx="42" cy="52" r="2" fill="#FFFFFF"/>
            <circle cx="62" cy="52" r="2" fill="#FFFFFF"/>
            <path d="M46 64C48 67 52 67 54 64" stroke="#180719" stroke-width="3" stroke-linecap="round"/>
            <circle cx="33" cy="62" r="4" fill="#FBCFE8" opacity="0.6"/>
            <circle cx="67" cy="62" r="4" fill="#FBCFE8" opacity="0.6"/>
        </svg>
    @elseif(str_contains($avatarKey, 'robot'))
        {{-- Pixel Cyborg Rudi --}}
        <svg class="w-full h-full p-1" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" rx="20" fill="#0E1E28"/>
            <rect x="25" y="30" width="50" height="42" rx="8" fill="#1E293B" stroke="#0284C7" stroke-width="3"/>
            <line x1="50" y1="30" x2="50" y2="18" stroke="#0284C7" stroke-width="4"/>
            <circle cx="50" cy="15" r="4" fill="#C3F53C"/>
            <rect x="33" y="42" width="12" height="8" rx="2" fill="#06B6D4"/>
            <rect x="55" y="42" width="12" height="8" rx="2" fill="#C3F53C"/>
            <line x1="38" y1="60" x2="62" y2="60" stroke="#94A3B8" stroke-width="3" stroke-linecap="round"/>
            <line x1="44" y1="57" x2="44" y2="63" stroke="#94A3B8" stroke-width="2"/>
            <line x1="50" y1="57" x2="50" y2="63" stroke="#94A3B8" stroke-width="2"/>
            <line x1="56" y1="57" x2="56" y2="63" stroke="#94A3B8" stroke-width="2"/>
        </svg>
    @else
        {{-- Default Neon Gamer Avatar --}}
        <svg class="w-full h-full p-1" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" rx="20" fill="#17192A"/>
            <circle cx="50" cy="42" r="20" fill="#C3F53C" stroke="#7C3AED" stroke-width="3"/>
            <path d="M24 82C24 66 36 62 50 62C64 62 76 66 76 82" fill="#7C3AED" stroke="#C3F53C" stroke-width="2"/>
            <circle cx="44" cy="40" r="3" fill="#0B0C14"/>
            <circle cx="56" cy="40" r="3" fill="#0B0C14"/>
            <path d="M47 48C49 50 51 50 53 48" stroke="#0B0C14" stroke-width="2" stroke-linecap="round"/>
        </svg>
    @endif
</div>
