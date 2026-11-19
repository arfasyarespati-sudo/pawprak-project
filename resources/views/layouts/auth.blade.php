<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CORET! CHAOS')</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-chaos-bg text-chaos-text antialiased" style="background: linear-gradient(180deg, #0D0D1D 0%, #12102B 50%, #0D0D1D 100%);">

    {{-- Header --}}
    <header class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-6 py-4 md:px-10">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 group">
            <span class="text-chaos-primary text-lg">🎨</span>
            <span class="text-chaos-text font-bold text-lg tracking-wide">CORET! CHAOS</span>
        </a>

        {{-- Right Nav --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="hidden sm:flex items-center gap-1.5 text-chaos-text-secondary text-sm hover:text-chaos-text transition-colors">
                <span>←</span>
                <span>BACK TO HOME</span>
            </a>
            {{-- Status indicator --}}
            <div class="w-8 h-8 rounded-full bg-chaos-card border border-chaos-card-border flex items-center justify-center">
                <div class="w-2.5 h-2.5 rounded-full bg-chaos-primary animate-pulse"></div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="min-h-screen flex flex-col items-center justify-center px-4 pt-20 pb-8">
        @yield('content')
    </main>

    {{-- Toast Notification Container --}}
    <div id="toast-container" class="fixed top-6 right-6 z-[100] flex flex-col gap-3"></div>

    {{-- Base Auth JS --}}
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-chaos-primary text-chaos-bg' :
                           type === 'error' ? 'bg-red-500 text-white' :
                           'bg-chaos-secondary text-white';

            toast.className = `${bgColor} px-5 py-3 rounded-lg text-sm font-medium shadow-lg transform translate-x-full transition-transform duration-300`;
            toast.textContent = message;
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full');
                toast.classList.add('translate-x-0');
            });

            setTimeout(() => {
                toast.classList.remove('translate-x-0');
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function clearErrors() {
            document.querySelectorAll('.field-error').forEach(el => el.remove());
            document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error', 'border-red-500'));
        }

        function showFieldError(fieldName, message) {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (!field) return;

            field.classList.add('input-error', 'border-red-500');

            const errorEl = document.createElement('p');
            errorEl.className = 'field-error text-red-400 text-xs mt-1';
            errorEl.textContent = message;
            field.closest('.form-group')?.appendChild(errorEl);
        }

        function setLoading(button, isLoading) {
            if (isLoading) {
                button.disabled = true;
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = `
                    <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>`;
                button.classList.add('opacity-75', 'cursor-not-allowed');
            } else {
                button.disabled = false;
                button.innerHTML = button.dataset.originalText;
                button.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
