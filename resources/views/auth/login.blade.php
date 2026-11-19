@extends('layouts.auth')

@section('title', 'Login — CORET! CHAOS')

@section('content')
<div class="w-full max-w-md mx-auto">

    {{-- Arcade Network Status --}}
    <div class="flex items-center justify-center gap-2 mb-8">
        <span class="w-2 h-2 rounded-full bg-chaos-primary animate-pulse"></span>
        <span class="text-chaos-primary text-xs font-medium tracking-widest uppercase">ARCADE NETWORK V3.4 ONLINE</span>
    </div>

    {{-- Login Card --}}
    <div class="bg-chaos-card border border-chaos-card-border rounded-2xl p-8 md:p-10 shadow-2xl shadow-black/30">

        {{-- Card Header --}}
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-2 mb-3">
                <span class="text-chaos-primary text-sm">🎨</span>
                <span class="text-chaos-text-secondary text-xs font-medium tracking-wider uppercase">CORET! CHAOS</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-chaos-text" style="font-family: 'Playfair Display', serif;">
                Masuk ke Arena
            </h1>
            <p class="text-chaos-text-secondary text-sm mt-2">
                Login coret-coret dan sebarkan kenangan.
            </p>
        </div>

        {{-- Login Form --}}
        <form id="login-form" class="space-y-5" novalidate>
            {{-- Email --}}
            <div class="form-group">
                <div class="flex items-center justify-between mb-1.5">
                    <label for="email" class="text-chaos-text-secondary text-xs font-medium tracking-wider uppercase">EMAIL</label>
                    <span class="text-chaos-text-secondary text-xs tracking-wider uppercase">WAJIB</span>
                </div>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="nama@domain.com"
                    autocomplete="email"
                    class="w-full bg-chaos-input-bg border border-chaos-input-border rounded-lg px-4 py-3 text-chaos-text text-sm placeholder-chaos-text-secondary/50 focus:outline-none focus:border-chaos-primary/50 focus:ring-1 focus:ring-chaos-primary/30 transition-all"
                    required
                >
            </div>

            {{-- Password --}}
            <div class="form-group">
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="text-chaos-text-secondary text-xs font-medium tracking-wider uppercase">PASSWORD</label>
                    <a href="#" class="text-chaos-text-secondary text-xs hover:text-chaos-primary transition-colors" onclick="event.preventDefault(); document.getElementById('forgot-modal')?.classList.toggle('hidden');">Lupa password&nbsp;?</a>
                </div>
                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        class="w-full bg-chaos-input-bg border border-chaos-input-border rounded-lg px-4 py-3 pr-12 text-chaos-text text-sm placeholder-chaos-text-secondary/50 focus:outline-none focus:border-chaos-primary/50 focus:ring-1 focus:ring-chaos-primary/30 transition-all"
                        required
                    >
                    <button
                        type="button"
                        onclick="togglePassword('password', this)"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-chaos-text-secondary hover:text-chaos-text transition-colors"
                        aria-label="Toggle password visibility"
                    >
                        <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="eye-closed w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    class="w-4 h-4 rounded border-chaos-input-border bg-chaos-input-bg text-chaos-primary focus:ring-chaos-primary/30 focus:ring-offset-0 cursor-pointer accent-[#B9F34B]"
                >
                <label for="remember" class="text-chaos-text-secondary text-sm cursor-pointer select-none">
                    Ingat saya di perangkat ini
                </label>
            </div>

            {{-- Submit Button --}}
            <button
                type="submit"
                id="login-btn"
                class="w-full bg-chaos-primary text-chaos-bg font-bold py-3.5 rounded-lg text-sm tracking-wide hover:brightness-110 active:brightness-95 transition-all shadow-lg shadow-chaos-primary/20"
            >
                MASUK SEKARANG ⚡
            </button>
        </form>

        {{-- Divider --}}
        <div class="flex items-center gap-4 my-6">
            <div class="flex-1 h-px bg-chaos-card-border"></div>
            <span class="text-chaos-text-secondary text-xs tracking-wider uppercase">ATAU</span>
            <div class="flex-1 h-px bg-chaos-card-border"></div>
        </div>

        {{-- Guest Login --}}
        <button
            type="button"
            id="guest-btn"
            onclick="guestLogin()"
            class="w-full bg-transparent border border-chaos-card-border text-chaos-text py-3 rounded-lg text-sm font-medium hover:bg-chaos-card-border/30 transition-all flex items-center justify-center gap-2"
        >
            🎭 Masuk Cepat Sebagai Tamu
        </button>

        {{-- Register Link --}}
        <p class="text-center text-chaos-text-secondary text-sm mt-6">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-chaos-primary font-medium hover:underline">Daftar di sini</a>
        </p>
    </div>

    {{-- Back to Home --}}
    <div class="text-center mt-6">
        <a href="{{ route('home') }}" class="text-chaos-text-secondary text-sm hover:text-chaos-text transition-colors inline-flex items-center gap-1">
            ← Kembali ke Beranda Utama
        </a>
    </div>
</div>

{{-- Forgot Password Modal --}}
<div id="forgot-modal" class="hidden fixed inset-0 z-[90] flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
    <div class="bg-chaos-card border border-chaos-card-border rounded-2xl p-8 max-w-sm w-full shadow-2xl">
        <h3 class="text-xl font-bold text-chaos-text mb-2" style="font-family: 'Playfair Display', serif;">Reset Password</h3>
        <p class="text-chaos-text-secondary text-sm mb-6">Masukkan email untuk menerima link reset password.</p>
        <form id="forgot-form" novalidate>
            <div class="form-group mb-4">
                <input
                    type="email"
                    name="forgot_email"
                    placeholder="nama@domain.com"
                    class="w-full bg-chaos-input-bg border border-chaos-input-border rounded-lg px-4 py-3 text-chaos-text text-sm placeholder-chaos-text-secondary/50 focus:outline-none focus:border-chaos-primary/50 transition-all"
                    required
                >
            </div>
            <button type="submit" id="forgot-btn" class="w-full bg-chaos-secondary text-white font-bold py-3 rounded-lg text-sm hover:brightness-110 transition-all">
                KIRIM LINK RESET
            </button>
        </form>
        <button type="button" onclick="document.getElementById('forgot-modal').classList.add('hidden')" class="w-full text-chaos-text-secondary text-sm mt-4 hover:text-chaos-text transition-colors">
            Batal
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const eyeOpen = button.querySelector('.eye-open');
        const eyeClosed = button.querySelector('.eye-closed');

        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        } else {
            input.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        }
    }

    // Login Form
    document.getElementById('login-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();

        const btn = document.getElementById('login-btn');
        setLoading(btn, true);

        const formData = {
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
            remember: document.getElementById('remember').checked,
        };

        try {
            const res = await fetch('{{ route("api.auth.login") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify(formData),
            });

            const data = await res.json();

            if (!res.ok) {
                if (data.errors) {
                    Object.entries(data.errors).forEach(([field, messages]) => {
                        showFieldError(field, messages[0]);
                    });
                }
                showToast(data.message || 'Login gagal.', 'error');
                setLoading(btn, false);
                return;
            }

            showToast(data.message || 'Login berhasil!', 'success');
            setTimeout(() => {
                window.location.href = data.redirect || '/';
            }, 800);
        } catch (err) {
            showToast('Terjadi kesalahan. Coba lagi.', 'error');
            setLoading(btn, false);
        }
    });

    // Guest Login
    async function guestLogin() {
        const btn = document.getElementById('guest-btn');
        setLoading(btn, true);

        try {
            const res = await fetch('{{ route("api.auth.guest") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
            });

            const data = await res.json();

            if (!res.ok) {
                showToast(data.message || 'Guest login gagal.', 'error');
                setLoading(btn, false);
                return;
            }

            showToast(`Selamat datang, ${data.player.nickname}!`, 'success');
            setTimeout(() => {
                window.location.href = data.redirect || '/';
            }, 800);
        } catch (err) {
            showToast('Terjadi kesalahan. Coba lagi.', 'error');
            setLoading(btn, false);
        }
    }

    // Forgot Password Form
    document.getElementById('forgot-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn = document.getElementById('forgot-btn');
        setLoading(btn, true);

        const email = this.querySelector('[name="forgot_email"]').value;

        try {
            const res = await fetch('{{ route("api.auth.forgot-password") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({ email }),
            });

            const data = await res.json();

            if (res.ok) {
                showToast(data.message, 'success');
                document.getElementById('forgot-modal').classList.add('hidden');
            } else {
                showToast(data.message || 'Gagal mengirim email reset.', 'error');
            }
        } catch (err) {
            showToast('Terjadi kesalahan.', 'error');
        }

        setLoading(btn, false);
    });
</script>
@endpush
