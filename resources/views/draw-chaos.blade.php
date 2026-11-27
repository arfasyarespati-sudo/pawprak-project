<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CORET! CHAOS — Real-Time Multiplayer Drawing & Sabotage</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700;9..144,900&family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="/socket.io/socket.io.js" onerror="this.onerror=null;"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#111124] text-[#E2E0FB] min-h-screen flex flex-col font-sans antialiased selection:bg-[#BDF454] selection:text-[#14200A] overflow-x-hidden relative">

    <!-- Ambient Radial Glows (Figma Arena Backdrop) -->
    <div class="pointer-events-none fixed -top-40 -left-40 w-[560px] h-[560px] rounded-full bg-[#5717BC]/22 blur-[140px] z-0"></div>
    <div class="pointer-events-none fixed -bottom-44 -right-40 w-[560px] h-[560px] rounded-full bg-[#BDF454]/12 blur-[150px] z-0"></div>

    @include('draw-chaos.partials.navbar')

    @include('draw-chaos.partials.screen-home')

    @include('draw-chaos.partials.screen-hub')

    @include('draw-chaos.partials.screen-auth')

    @include('draw-chaos.partials.screen-lobby')

    @include('draw-chaos.partials.screen-gameplay')

    @include('draw-chaos.partials.screen-results-museum')

    @include('draw-chaos.partials.modals')

    <!-- Floating Toast Notifications -->
    <div id="chaos-toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm"></div>

    <!-- Hydrate Initial Backend State to JS Engine -->
    <script>
        window.CORET_CHAOS_BOOT = {
            deckCatalog: @json($deckCatalog),
            hallOfShame: @json($hallOfShame),
            publicRooms: @json($publicRooms ?? []),
            initialRoom: @json($initialRoom),
            bootView: @json($bootView ?? null),
            onlineCount: @json($onlineCount ?? 1),
            guestPlayer: @json($guestPlayer ?? null),
            reverbConfig: @json($reverbConfig ?? []),
        };
    </script>
</body>
</html>
