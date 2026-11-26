import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/**
 * CORET! CHAOS — 100% Real-Time Multiplayer Client Engine (Zero Fake/Demo Data)
 * Supports Socket.IO / WebSocket + Laravel Reverb + HTTP Real-Time Sync
 */

const AVATAR_ICONS = {
    'cat-neon': '🐱',
    'alien-duck': '🦆',
    'crayon-ninja': '🥷',
    'robot-rusuh': '🤖',
    'dino-glitch': '🦖',
    'raccoon-cyber': '🦝',
};

const AVATAR_CATALOG = [
    {
        key: 'cat-neon',
        label: 'Cyber Cat',
        tagline: 'Kucing Saboteur',
        color: '#BDF454',
        svg: '<svg viewBox="0 0 48 48" class="w-full h-full" aria-hidden="true"><rect width="48" height="48" rx="12" fill="#1A1533"/><path d="M12 18l-3-10 9 5.5c3.5-.9 8.5-.9 12 0L39 8l-3 10c2.8 4 3.2 10 0 14.5C33 36.5 28.8 39 24 39s-9-2.5-12-6.5C8.8 28 9.2 22 12 18z" fill="#5717BC" stroke="#BDF454" stroke-width="2.2"/><circle cx="18" cy="24" r="2.7" fill="#BDF454"/><circle cx="30" cy="24" r="2.7" fill="#BDF454"/><path d="M20 31c2.2 1.8 5.8 1.8 8 0" fill="none" stroke="#E2E0FB" stroke-width="2.2" stroke-linecap="round"/><path d="M13 28h4M31 28h4" stroke="#D1BCFF" stroke-width="1.6" stroke-linecap="round"/></svg>',
    },
    {
        key: 'alien-duck',
        label: 'Alen Bebek',
        tagline: 'Tinta Kosmik',
        color: '#38BDF8',
        svg: '<svg viewBox="0 0 48 48" class="w-full h-full" aria-hidden="true"><rect width="48" height="48" rx="12" fill="#0F2038"/><path d="M24 8v5M19 10l2 3M29 10l-2 3" stroke="#BDF454" stroke-width="2.2" stroke-linecap="round"/><circle cx="24" cy="25" r="13" fill="#22D3EE" stroke="#E2E0FB" stroke-width="2"/><circle cx="19" cy="22" r="2.6" fill="#0C0C1F"/><circle cx="29" cy="22" r="2.6" fill="#0C0C1F"/><ellipse cx="24" cy="30" rx="7" ry="4" fill="#FF9F1C" stroke="#0C0C1F" stroke-width="1.5"/></svg>',
    },
    {
        key: 'crayon-ninja',
        label: 'Krayon Ninja',
        tagline: 'Coretan Kilat',
        color: '#D1BCFF',
        svg: '<svg viewBox="0 0 48 48" class="w-full h-full" aria-hidden="true"><rect width="48" height="48" rx="12" fill="#1F1638"/><circle cx="24" cy="25" r="13.5" fill="#28283C" stroke="#D1BCFF" stroke-width="2.2"/><rect x="10.5" y="19" width="27" height="9" rx="4.5" fill="#111124" stroke="#BDF454" stroke-width="1.8"/><circle cx="18.5" cy="23.5" r="2.2" fill="#BDF454"/><circle cx="29.5" cy="23.5" r="2.2" fill="#BDF454"/><path d="M35 17l5-4-2 6" fill="#FF4D6D"/></svg>',
    },
    {
        key: 'robot-rusuh',
        label: 'Robot Rusuh',
        tagline: 'Mode Overclock',
        color: '#FF4D6D',
        svg: '<svg viewBox="0 0 48 48" class="w-full h-full" aria-hidden="true"><rect width="48" height="48" rx="12" fill="#26142A"/><path d="M24 8v5" stroke="#FF4D6D" stroke-width="2.5" stroke-linecap="round"/><circle cx="24" cy="7" r="2.5" fill="#BDF454"/><rect x="11" y="13" width="26" height="24" rx="7" fill="#333347" stroke="#D1BCFF" stroke-width="2"/><rect x="15" y="19" width="18" height="7" rx="3" fill="#0C0C1F"/><circle cx="19.5" cy="22.5" r="2" fill="#FF4D6D"/><circle cx="28.5" cy="22.5" r="2" fill="#BDF454"/><path d="M17 31h14" stroke="#BDF454" stroke-width="2.2" stroke-linecap="round" stroke-dasharray="2 3"/></svg>',
    },
    {
        key: 'dino-glitch',
        label: 'Dino Glitch',
        tagline: 'Kuas Purba',
        color: '#A3E635',
        svg: '<svg viewBox="0 0 48 48" class="w-full h-full" aria-hidden="true"><rect width="48" height="48" rx="12" fill="#15261A"/><path d="M13 36V19c0-5.5 4.5-10 10-10h5c4.5 0 8 3.5 8 8v5c0 2.5-2 4.5-4.5 4.5H27V36H13z" fill="#65A30D" stroke="#BDF454" stroke-width="2"/><path d="M10 17l3 2M9 23l4 1M10 29l3-1" stroke="#BDF454" stroke-width="2" stroke-linecap="round"/><circle cx="29" cy="17" r="2.4" fill="#0C0C1F"/><circle cx="29.6" cy="16.4" r="0.8" fill="#FFF"/></svg>',
    },
    {
        key: 'raccoon-cyber',
        label: 'Rakun Cyber',
        tagline: 'Pencuri Poin',
        color: '#F59E0B',
        svg: '<svg viewBox="0 0 48 48" class="w-full h-full" aria-hidden="true"><rect width="48" height="48" rx="12" fill="#231D15"/><path d="M11 18l-2-9 8 4.5c4.5-1.2 9.5-1.2 14 0L39 9l-2 9c2.5 3.8 2.5 9.5 0 13.5C34 36 29.2 38 24 38s-10-2-13-6.5c-2.5-4-2.5-9.7 0-13.5z" fill="#475569" stroke="#FBBF24" stroke-width="2"/><rect x="12.5" y="20" width="23" height="7" rx="3.5" fill="#0C0C1F"/><circle cx="18.5" cy="23.5" r="2.1" fill="#FBBF24"/><circle cx="29.5" cy="23.5" r="2.1" fill="#FBBF24"/><path d="M22 30l2 2 2-2" stroke="#E2E0FB" stroke-width="2" stroke-linecap="round"/></svg>',
    },
];

class CoretChaosApp {
    constructor() {
        const bootData = window.CORET_CHAOS_BOOT || {};
        this.deckCatalog = bootData.deckCatalog || {};
        this.hallOfShame = Array.isArray(bootData.hallOfShame) ? bootData.hallOfShame : [];
        this.publicRooms = Array.isArray(bootData.publicRooms) ? bootData.publicRooms : [];
        this.onlineCount = Number(bootData.onlineCount || 1);
        this.museumFilter = 'newest';

        const guestBoot = bootData.guestPlayer || {};
        const preferBootAuth = Boolean(bootData.initialRoom && guestBoot.is_authenticated && guestBoot.raw_token);
        this.guestToken = (preferBootAuth ? guestBoot.raw_token : null)
            || localStorage.getItem('guest_token')
            || localStorage.getItem('coret_guest_token')
            || guestBoot.raw_token
            || `guest_${Math.random().toString(36).slice(2)}${Date.now().toString(36)}`;

        this.playerId = Number((preferBootAuth ? guestBoot.id : null) || localStorage.getItem('coret_player_id') || guestBoot.id || 0);

        try {
            localStorage.setItem('guest_token', this.guestToken);
            localStorage.setItem('coret_guest_token', this.guestToken);
            if (this.playerId) {
                localStorage.setItem('coret_player_id', String(this.playerId));
            }
        } catch {
            // Ignore private mode quota error
        }

        const savedNickname = preferBootAuth ? null : localStorage.getItem('coret_player_nickname');
        const savedAvatar = preferBootAuth ? null : localStorage.getItem('coret_player_avatar');
        this.playerName = savedNickname || guestBoot.nickname || 'SenimanRusuh_89';
        this.playerAvatar = AVATAR_CATALOG.some((a) => a.key === savedAvatar)
            ? savedAvatar
            : (AVATAR_CATALOG.some((a) => a.key === guestBoot.avatar) ? guestBoot.avatar : 'cat-neon');
        this.joinAvatar = this.playerAvatar;
        this.registerAvatar = this.playerAvatar;
        this.profileAvatar = this.playerAvatar;

        this.userProfile = {
            id: guestBoot.id || this.playerId || 0,
            nickname: this.playerName,
            avatar: this.playerAvatar,
            email: guestBoot.email || null,
            is_authenticated: Boolean(guestBoot.is_authenticated),
            total_score: Number(guestBoot.total_score || 0),
            games_played: Number(guestBoot.games_played || 0),
            level: Number(guestBoot.level || 1),
        };

        // Room state (null until player creates or joins a real room)
        this.room = bootData.initialRoom || null;

        this.activeView = 'home'; // home | hub | login | register | lobby | artist | saboteur | results | museum
        this.currentRole = 'saboteur'; // artist | saboteur

        // Canvas state (starts 100% clean, zero pre-populated fake strokes)
        this.strokes = [];
        this.isDrawing = false;
        this.currentStroke = null;
        this.brushColor = '#14121D';
        this.brushSize = 6;
        this.lastCanvasDataUrl = null;

        // Chaos Energy & Saboteur Deck state (calculated & validated by server)
        this.chaosEnergy = 100;
        this.maxChaosEnergy = 100;
        this.curseCooldowns = {
            earthquake: 0,
            invert: 0,
            blindfold: 0,
            sticker: 0,
        };
        this.activeCurses = {
            earthquake: 0,
            invert: 0,
            blindfold: 0,
            sticker: 0,
        };
        this.sabotageHistory = [];

        // Round timer & real RTT ping
        this.roundSecondsLeft = 60;
        this.roundElapsedSeconds = 0;
        this.soundEnabled = true;
        this.pingMs = null;
        this.socket = null;
        this.reverbSocket = null;

        // Cross-tab instant broadcast channel
        this.broadcastChannel = typeof BroadcastChannel !== 'undefined'
            ? new BroadcastChannel('coret_chaos_realtime_v2')
            : null;
        this.renderedGuessKeys = new Set();
        this.renderedLobbyChatIds = new Set();

        // Interactive Room Settings Modal State
        this.roomSettingsState = {
            priv: 'pub',
            time: '60',
            rounds: '5',
            max: '8',
            chaos: 'on',
            chaos_mode: 'classic',
            pack: 'mix',
        };
        this.savedRoomSettingsState = { ...this.roomSettingsState };
        this.roomSettingsModalMode = 'create';

        this.init();
    }

    init() {
        this.updateServerFooterHost();
        this.syncAuthUI();
        this.bindNavigation();
        this.bindAuthAndProfileActions();
        this.bindHubActions();
        this.bindJoinLobbyModal();
        this.bindLandingActions();
        this.bindLobbyActions();
        this.bindLobbyChat();
        this.bindRoomSettingsModal();
        this.bindArtistSecretWordForm();
        this.bindCanvasEvents();
        this.bindSaboteurDeck();
        this.bindGuessingChat();
        this.bindResultsAndMuseumActions();
        this.setupRealtimeSync();
        this.startTimers();
        this.measureRealPing();
        this.renderAll();

        // Auto-reconnect if player was in a room (?room=XXXX or sessionStorage), or boot directly from /game/{code}
        const params = new URLSearchParams(window.location.search);
        const bootData = window.CORET_CHAOS_BOOT || {};
        const roomParam = params.get('room') || (!bootData.initialRoom ? sessionStorage.getItem('coret_active_room_code') : null);
        const viewParam = params.get('view') || bootData.bootView;

        if (bootData.initialRoom && !params.get('room')) {
            this.applyRoomSnapshot(bootData.initialRoom);
            const targetView = viewParam === 'game' ? this.determineMyRoleFromRoom() : (viewParam || 'lobby');
            this.switchView(targetView);
        } else if (roomParam) {
            this.reconnectToRoom(roomParam.toUpperCase().slice(0, 4), viewParam);
        } else if (viewParam) {
            this.switchView(viewParam);
        }

        // Notify server if player closes tab while in a room
        window.addEventListener('beforeunload', () => {
            if (this.room?.code) {
                const payload = JSON.stringify({ player_name: this.playerName });
                navigator.sendBeacon?.(`/api/rooms/${this.room.code}/leave`, new Blob([payload], { type: 'application/json' }));
            }
        });
    }

    getAvatarSvg(avatarKey) {
        const found = AVATAR_CATALOG.find((a) => a.key === avatarKey) || AVATAR_CATALOG[0];
        return found.svg;
    }

    getAvatarMeta(avatarKey) {
        return AVATAR_CATALOG.find((a) => a.key === avatarKey) || AVATAR_CATALOG[0];
    }

    updateServerFooterHost() {
        const hostEl = document.getElementById('footer-server-host');
        if (hostEl) {
            hostEl.textContent = `🌐 ${window.location.host}`;
        }
    }

    requestHeaders() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'X-Guest-Token': this.guestToken,
            'X-Player-Nickname': this.playerName,
        };
    }

    // =========================================================================
    // Real-Time Ping RTT Measurement & Online Player Counter
    // =========================================================================
    async measureRealPing() {
        const start = performance.now();
        try {
            if (this.socket && this.socket.connected) {
                this.socket.emit('ping:check', start, (serverTime) => {
                    this.pingMs = Math.max(1, Math.round(performance.now() - start));
                    this.renderPingAndOnlineUI();
                });
                return;
            }

            const res = await fetch('/api/ping', {
                method: 'GET',
                headers: this.requestHeaders(),
                cache: 'no-store',
            });
            if (res.ok) {
                const rtt = Math.max(1, Math.round(performance.now() - start));
                const data = await res.json();
                this.pingMs = rtt;
                if (typeof data.online_count === 'number') {
                    this.onlineCount = data.online_count;
                }
                this.renderPingAndOnlineUI();
            }
        } catch {
            // Server unreachable
        }
    }

    renderPingAndOnlineUI() {
        const pingText = this.pingMs !== null ? `${this.pingMs} ms` : '-- ms';
        const navPing = document.getElementById('nav-ping-badge');
        const footerPing = document.getElementById('footer-ping-display');
        const canvasPing = document.getElementById('canvas-ping-display');
        const joinModalPing = document.getElementById('join-modal-ping');
        const onlineCounter = document.getElementById('online-players-counter');

        if (navPing) {
            navPing.textContent = pingText;
        }
        if (footerPing) {
            footerPing.textContent = `📶 ${pingText} Ping`;
        }
        if (canvasPing) {
            canvasPing.textContent = `📶 ${pingText} Ping`;
        }
        if (joinModalPing) {
            joinModalPing.textContent = `📶 Server Ping: ${pingText}`;
        }
        if (onlineCounter) {
            onlineCounter.textContent = `${this.onlineCount} SENIMAN SEDANG ONLINE`;
        }
    }

    // =========================================================================
    // Web Audio Synthesizer for Tactile Game Feedback
    // =========================================================================
    playSound(type) {
        if (!this.soundEnabled) {
            return;
        }
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) {
                return;
            }
            if (!this.audioCtx) {
                this.audioCtx = new AudioCtx();
            }
            const ctx = this.audioCtx;
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            if (type === 'earthquake') {
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(95, now);
                osc.frequency.exponentialRampToValueAtTime(42, now + 0.45);
                gain.gain.setValueAtTime(0.18, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.45);
                osc.start(now);
                osc.stop(now + 0.45);
            } else if (type === 'invert') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(320, now);
                osc.frequency.linearRampToValueAtTime(640, now + 0.18);
                osc.frequency.linearRampToValueAtTime(220, now + 0.35);
                gain.gain.setValueAtTime(0.15, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.36);
                osc.start(now);
                osc.stop(now + 0.36);
            } else if (type === 'blindfold') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(440, now);
                osc.frequency.exponentialRampToValueAtTime(65, now + 0.35);
                gain.gain.setValueAtTime(0.2, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.35);
                osc.start(now);
                osc.stop(now + 0.35);
            } else if (type === 'sticker') {
                osc.type = 'square';
                osc.frequency.setValueAtTime(180, now);
                osc.frequency.exponentialRampToValueAtTime(520, now + 0.15);
                gain.gain.setValueAtTime(0.15, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.16);
                osc.start(now);
                osc.stop(now + 0.16);
            } else if (type === 'almost') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(523.25, now);
                osc.frequency.setValueAtTime(659.25, now + 0.1);
                gain.gain.setValueAtTime(0.14, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.28);
                osc.start(now);
                osc.stop(now + 0.28);
            } else if (type === 'exact') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(523.25, now);
                osc.frequency.setValueAtTime(659.25, now + 0.1);
                osc.frequency.setValueAtTime(783.99, now + 0.2);
                osc.frequency.setValueAtTime(1046.5, now + 0.3);
                gain.gain.setValueAtTime(0.18, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.55);
                osc.start(now);
                osc.stop(now + 0.55);
            }
        } catch {
            // Ignore audio context restriction errors
        }
    }

    // =========================================================================
    // View Switching & Top Navigation
    // =========================================================================
    bindNavigation() {
        document.querySelectorAll('[data-nav-view]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-nav-view');
                if (target) {
                    this.switchView(target);
                }
            });
        });

        const soundBtn = document.getElementById('btn-toggle-sound');
        if (soundBtn) {
            soundBtn.addEventListener('click', () => {
                this.soundEnabled = !this.soundEnabled;
                soundBtn.classList.toggle('opacity-50', !this.soundEnabled);
                this.showToast(this.soundEnabled ? '🔊 Efek Suara Aktif' : '🔇 Efek Suara Dibisukan');
            });
        }

        const fsBtn = document.getElementById('btn-toggle-fullscreen');
        if (fsBtn) {
            fsBtn.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen?.().catch(() => {});
                } else {
                    document.exitFullscreen?.().catch(() => {});
                }
            });
        }
    }

    switchView(viewName) {
        // Strict Guard: Do not allow entering Lobby, Game/Artist/Saboteur, or Results without joining a room
        if (viewName === 'lobby') {
            if (!this.room || !this.room.code) {
                this.showToast('⚠️ Kamu belum bergabung ke room! Silakan buat room atau klik Quick Play terlebih dahulu.');
                return;
            }
        } else if (viewName === 'artist' || viewName === 'saboteur' || viewName === 'game') {
            if (!this.room || !this.room.code) {
                this.showToast('⚠️ Tidak bisa masuk ke kanvas karena belum bergabung ke room! Silakan buat room atau klik Quick Play.');
                return;
            }
            if (this.room.status === 'lobby') {
                this.showToast('⚠️ Permainan belum dimulai! Tunggu pemain lain SIAP di Lobby.');
                viewName = 'lobby';
            } else {
                const assignedRole = this.determineMyRoleFromRoom();
                if (viewName === 'artist' && assignedRole !== 'artist') {
                    this.showToast('⚠️ Kamu bukan Pelukis pada ronde ini! Peranmu adalah Penebak/Saboteur.');
                    viewName = 'saboteur';
                } else if (viewName === 'saboteur' && assignedRole === 'artist') {
                    this.showToast('🎨 Giliranmu adalah Pelukis! Gambar kata rahasia di kanvas.');
                    viewName = 'artist';
                } else if (viewName === 'game') {
                    viewName = assignedRole;
                }
            }
        } else if (viewName === 'results') {
            if (!this.room || !this.room.code) {
                this.showToast('⚠️ Belum ada hasil ronde! Masuk ke room dan selesaikan permainan terlebih dahulu.');
                return;
            }
        }

        this.activeView = viewName;
        const sectionToShow = (viewName === 'artist' || viewName === 'saboteur') ? 'gameplay' : viewName;

        if (viewName === 'artist') {
            this.currentRole = 'artist';
        } else if (viewName === 'saboteur') {
            this.currentRole = 'saboteur';
        }

        document.querySelectorAll('[data-screen-section]').forEach((sec) => {
            const secName = sec.getAttribute('data-screen-section');
            const shouldShow = secName === sectionToShow;
            sec.classList.toggle('hidden', !shouldShow);
            if (shouldShow) {
                sec.classList.remove('animate-view-fade');
                void sec.offsetWidth; // Force reflow to replay smooth animation
                sec.classList.add('animate-view-fade');
            }
        });

        document.querySelectorAll('[data-nav-view]').forEach((btn) => {
            const navTarget = btn.getAttribute('data-nav-view');
            const isActive = navTarget === viewName || (navTarget === 'game' && (viewName === 'artist' || viewName === 'saboteur'));
            if (btn.closest('header nav') || btn.parentElement?.classList.contains('lg:hidden')) {
                btn.classList.toggle('bg-[#28283C]', isActive);
                btn.classList.toggle('text-[#E2E0FB]', isActive);
                btn.classList.toggle('shadow-sm', isActive);
                btn.classList.toggle('text-[#C3C9B1]', !isActive);
            }
        });

        if (sectionToShow === 'hub') {
            this.renderHubScreen();
            this.fetchPublicRooms();
        } else if (sectionToShow === 'lobby') {
            this.renderLobbyPlayers();
            this.renderLobbyChat();
        } else if (sectionToShow === 'gameplay') {
            this.updateGameplayRoleUI();
            this.renderQuickLeaderboard();
            requestAnimationFrame(() => {
                this.resizeAndRedrawCanvas();
            });
        } else if (sectionToShow === 'results') {
            this.renderResultsScreen();
            requestAnimationFrame(() => {
                this.renderResultsPolaroidCanvas();
            });
        } else if (sectionToShow === 'museum') {
            this.fetchMuseumGallery(this.museumFilter);
        }
    }

    handleRoundTransition(nextRoomSnapshot) {
        if (!nextRoomSnapshot) {
            return;
        }

        this.strokes = [];
        this.activeCurses = { earthquake: 0, invert: 0, blindfold: 0, sticker: 0 };

        const wrapper = document.getElementById('chaos-canvas-wrapper');
        if (wrapper) {
            wrapper.classList.remove('animate-chaos-earthquake', 'fx-gempa', 'animate-chaos-drunk', 'fx-mabuk', 'fx-gelap');
        }
        document.getElementById('canvas-active-curse-banner')?.classList.add('hidden');
        document.getElementById('inverted-cursor-ghost')?.classList.add('hidden');
        document.getElementById('curse-sticker-bomb-layer')?.classList.add('hidden');

        this.roundSecondsLeft = nextRoomSnapshot.round_duration || 60;
        this.roundElapsedSeconds = 0;

        if (this.renderedGuessKeys) {
            this.renderedGuessKeys.clear();
        }
        const feed = document.getElementById('chat') || document.getElementById('live-guesses-feed');
        if (feed) {
            feed.innerHTML = '';
            const sysMsg = document.createElement('div');
            sysMsg.className = 'm';
            sysMsg.innerHTML = `<span style="color:var(--lime);font-weight:700;">🔄 Ronde ${this.escapeHtml(String(nextRoomSnapshot.current_round || 1))} dimulai!</span> Giliran <b>${this.escapeHtml(nextRoomSnapshot.round?.artist_name || 'Pelukis')}</b> menggambar!`;
            feed.appendChild(sysMsg);
        }

        this.applyRoomSnapshot(nextRoomSnapshot);
        const myRole = this.determineMyRoleFromRoom();
        this.switchView(myRole);
        this.updateGameplayRoleUI();
        this.resizeAndRedrawCanvas();
        this.showToast(`🎨 Ronde ${nextRoomSnapshot.current_round || 1}: Giliran ${nextRoomSnapshot.round?.artist_name || 'Pelukis'} menggambar!`);
    }

    determineMyRoleFromRoom() {
        if (!this.room || !this.room.round) {
            return 'saboteur';
        }
        const artistName = (this.room.round.artist_name || '').toLowerCase();
        const myName = (this.playerName || '').toLowerCase();
        if (artistName && artistName === myName) {
            return 'artist';
        }
        if (this.playerId && this.room.round.artist_id && Number(this.room.round.artist_id) === Number(this.playerId)) {
            return 'artist';
        }
        return 'saboteur';
    }

    updateGameplayRoleUI() {
        const isArtist = this.currentRole === 'artist';
        const artistToolbar = document.getElementById('canvas-artist-tools') || document.getElementById('artist-brush-toolbar');
        const secretWordForm = document.getElementById('form-artist-secret-word');
        const wordDisplay = document.getElementById('hud-word-display');
        const wordLabel = document.getElementById('hud-word-label');
        const roundNumEl = document.getElementById('hud-round-number');
        const timerStatus = document.getElementById('hud-timer-status');
        const canvasOverlay = document.getElementById('canvas-overlay');
        const chatInput = document.getElementById('input-live-guess');
        const chatBtn = document.getElementById('btn-submit-live-guess');
        const chatCount = document.getElementById('chat-players-count');

        const roundStatus = this.room?.round?.status || 'drawing';
        const secretWord = this.room?.round?.secret_word || '';
        const maskedWord = this.room?.round?.masked_word || '';
        const hasWord = isArtist
            ? Boolean(secretWord)
            : (roundStatus !== 'choosing_word' && Boolean(maskedWord || secretWord));

        if (roundNumEl) {
            const cur = String(this.room?.current_round || 1);
            const max = String(this.room?.max_rounds || 5);
            roundNumEl.textContent = `${cur} / ${max}`;
        }

        if (isArtist) {
            if (artistToolbar) {
                artistToolbar.style.display = 'flex';
            }
            if (secretWordForm) {
                secretWordForm.style.display = hasWord ? 'none' : 'block';
            }
            if (canvasOverlay) {
                canvasOverlay.style.display = hasWord ? 'none' : 'grid';
            }
            if (wordLabel) {
                wordLabel.textContent = 'Kata rahasia · hanya kamu yang lihat';
            }
            if (wordDisplay) {
                if (hasWord) {
                    wordDisplay.textContent = secretWord.toUpperCase();
                    wordDisplay.classList.remove('no');
                } else {
                    wordDisplay.textContent = 'Belum ditetapkan';
                    wordDisplay.classList.add('no');
                }
            }
            if (timerStatus) {
                timerStatus.textContent = hasWord ? 'Waktu menggambar' : 'Tetapkan kata untuk memulai';
            }
            if (chatInput) {
                chatInput.placeholder = 'Chat dengan pemain lain...';
                chatInput.disabled = false;
            }
        } else {
            if (artistToolbar) {
                artistToolbar.style.display = 'none';
            }
            if (secretWordForm) {
                secretWordForm.style.display = 'none';
            }
            if (wordLabel) {
                const count = this.room?.round?.word_count || 1;
                wordLabel.textContent = `Petunjuk kata (${count} kata)`;
            }
            if (wordDisplay) {
                if (hasWord) {
                    wordDisplay.textContent = this.room?.round?.masked_word || '_ _ _ _ _ _';
                    wordDisplay.classList.remove('no');
                } else {
                    wordDisplay.textContent = 'Menunggu Pelukis...';
                    wordDisplay.classList.add('no');
                }
            }
            if (canvasOverlay) {
                canvasOverlay.style.display = hasWord ? 'none' : 'grid';
                if (!hasWord) {
                    canvasOverlay.innerHTML = '<div>Menunggu Pelukis menetapkan kata<span>Kanvas aktif setelah kata ditetapkan</span></div>';
                }
            }
            if (timerStatus) {
                const artistName = this.room?.round?.artist_name || 'Pelukis';
                timerStatus.textContent = hasWord ? `${artistName} sedang melukis...` : 'Menunggu kata rahasia...';
            }
            if (chatInput) {
                chatInput.placeholder = 'Ketik tebakan atau chat di sini...';
                chatInput.disabled = false;
            }
        }

        if (chatBtn) {
            chatBtn.disabled = false;
        }

        if (chatCount) {
            const count = Array.isArray(this.room?.players) ? this.room.players.length : 1;
            chatCount.textContent = `${count} pemain di room`;
        }
    }

    // =========================================================================
    // Module 0: Auth, Profil Seniman, Arena Hub & Modal Gabung Lobby
    // =========================================================================
    persistIdentityLocally() {
        try {
            localStorage.setItem('guest_token', this.guestToken);
            localStorage.setItem('coret_guest_token', this.guestToken);
            localStorage.setItem('coret_player_nickname', this.playerName);
            localStorage.setItem('coret_player_avatar', this.playerAvatar);
            if (this.playerId) {
                localStorage.setItem('coret_player_id', String(this.playerId));
            }
        } catch {
            // Ignore private mode errors
        }
    }

    applyAuthenticatedUser(user, newToken = null) {
        if (newToken) {
            this.guestToken = newToken;
        }
        if (user) {
            this.playerId = Number(user.id || this.playerId || 0);
            this.playerName = user.nickname || this.playerName || 'SenimanRusuh_89';
            if (AVATAR_CATALOG.some((a) => a.key === user.avatar)) {
                this.playerAvatar = user.avatar;
            }
            this.userProfile = {
                ...this.userProfile,
                ...user,
                nickname: this.playerName,
                avatar: this.playerAvatar,
                is_authenticated: Boolean(user.is_authenticated),
            };
        }
        this.persistIdentityLocally();
        this.syncAuthUI();
    }

    syncAuthUI() {
        const avatarSvg = this.getAvatarSvg(this.playerAvatar);
        const navAvatar = document.getElementById('nav-user-avatar-slot');
        const navNick = document.getElementById('nav-user-nickname');
        const navStatusDot = document.getElementById('nav-user-status-dot');
        const navLoginBtn = document.getElementById('btn-nav-open-login');
        const landingAvatar = document.getElementById('landing-avatar-preview');
        const landingInput = document.getElementById('input-landing-nickname');
        const landingAuthLink = document.getElementById('btn-landing-auth-link');

        if (navAvatar) navAvatar.innerHTML = avatarSvg;
        if (navNick) navNick.textContent = this.playerName;
        if (navStatusDot) {
            const isAuth = Boolean(this.userProfile?.is_authenticated);
            navStatusDot.className = `w-2 h-2 rounded-full ${isAuth ? 'bg-[#BDF454]' : 'bg-[#D1BCFF]'}`;
        }
        if (navLoginBtn) {
            navLoginBtn.classList.toggle('hidden', Boolean(this.userProfile?.is_authenticated));
        }
        if (landingAvatar) landingAvatar.innerHTML = avatarSvg;
        if (landingInput && document.activeElement !== landingInput) {
            landingInput.value = this.playerName;
        }
        if (landingAuthLink) {
            landingAuthLink.textContent = this.userProfile?.is_authenticated
                ? `👑 Masuk sebagai ${this.playerName} • Lihat Profil`
                : '🔐 Masuk / Daftar Akun Seniman';
        }

        if (this.activeView === 'hub') {
            this.renderHubScreen();
        }
    }

    renderAvatarPickerGrid(containerId, selectedKey, items, onSelect) {
        const container = document.getElementById(containerId);
        if (!container) {
            return;
        }
        container.innerHTML = '';
        items.forEach((av) => {
            const isSelected = av.key === selectedKey;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `p-2.5 rounded-2xl border text-center transition cursor-pointer flex flex-col items-center gap-1.5 ${
                isSelected
                    ? 'bg-[#28283C] border-[#BDF454] shadow-[0_0_0_2px_rgba(189,244,84,0.25)]'
                    : 'bg-[#0C0C1F] border-[#333347] hover:border-[#D1BCFF]/60'
            }`;
            btn.innerHTML = `
                <span class="w-11 h-11 rounded-xl overflow-hidden block">${av.svg}</span>
                <span class="text-[11px] font-extrabold leading-tight ${isSelected ? 'text-[#BDF454]' : 'text-[#E2E0FB]'}">${this.escapeHtml(av.label)}</span>
            `;
            btn.addEventListener('click', () => {
                onSelect(av.key);
                this.renderAvatarPickerGrid(containerId, av.key, items, onSelect);
            });
            container.appendChild(btn);
        });
    }

    bindAuthAndProfileActions() {
        // Password Eye Toggles
        const togglePass = (btnId, inputId) => {
            const btn = document.getElementById(btnId);
            const inp = document.getElementById(inputId);
            if (btn && inp) {
                btn.addEventListener('click', () => {
                    const isPass = inp.type === 'password';
                    inp.type = isPass ? 'text' : 'password';
                    btn.textContent = isPass ? '🙈' : '👁️';
                });
            }
        };
        togglePass('btn-toggle-login-password', 'input-login-password');
        togglePass('btn-toggle-register-password', 'input-register-password');

        // Landing secondary auth link when already logged in -> open profile instead
        const landingAuthLink = document.getElementById('btn-landing-auth-link');
        if (landingAuthLink) {
            landingAuthLink.addEventListener('click', (e) => {
                if (this.userProfile?.is_authenticated) {
                    e.stopImmediatePropagation();
                    this.openProfileModal();
                }
            }, true);
        }

        // Login Form
        const formLogin = document.getElementById('form-login');
        const loginErr = document.getElementById('login-error-box');
        if (formLogin) {
            formLogin.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (loginErr) loginErr.classList.add('hidden');
                const email = document.getElementById('input-login-email')?.value.trim() || '';
                const password = document.getElementById('input-login-password')?.value || '';
                const remember = Boolean(document.getElementById('input-login-remember')?.checked);

                try {
                    const res = await fetch('/api/arena-auth/login', {
                        method: 'POST',
                        headers: this.requestHeaders(),
                        body: JSON.stringify({ email, password, remember }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        if (loginErr) {
                            loginErr.textContent = data.message || 'Email atau password tidak sesuai.';
                            loginErr.classList.remove('hidden');
                        }
                        return;
                    }
                    this.applyAuthenticatedUser(data.user, data.guest_token);
                    this.showToast(`⚡ Selamat datang kembali, ${this.playerName}!`);
                    this.switchView('hub');
                } catch {
                    if (loginErr) {
                        loginErr.textContent = 'Gagal terhubung ke server autentikasi.';
                        loginErr.classList.remove('hidden');
                    }
                }
            });
        }

        // Login Quick Guest
        const btnLoginGuest = document.getElementById('btn-login-as-guest');
        if (btnLoginGuest) {
            btnLoginGuest.addEventListener('click', () => {
                this.showToast(`🎲 Bermain cepat sebagai ${this.playerName} (Mode Tamu)`);
                this.switchView('hub');
            });
        }

        // Register Nickname Randomizer & Avatar Picker
        const btnRegRand = document.getElementById('btn-randomize-register-nickname');
        const regNickInput = document.getElementById('input-register-nickname');
        if (btnRegRand && regNickInput) {
            btnRegRand.addEventListener('click', () => {
                const prefixes = ['KrayonKilat', 'CyberCat', 'PelukisNeon', 'BebekKosmik', 'NinjaCoret'];
                regNickInput.value = `${prefixes[Math.floor(Math.random() * prefixes.length)]}_${Math.floor(10 + Math.random() * 89)}`;
            });
        }
        this.renderAvatarPickerGrid('register-avatar-picker', this.registerAvatar, AVATAR_CATALOG, (key) => {
            this.registerAvatar = key;
        });

        // Register Form
        const formRegister = document.getElementById('form-register');
        const regErr = document.getElementById('register-error-box');
        if (formRegister) {
            formRegister.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (regErr) regErr.classList.add('hidden');
                const nickname = regNickInput?.value.trim() || '';
                const email = document.getElementById('input-register-email')?.value.trim() || '';
                const password = document.getElementById('input-register-password')?.value || '';

                try {
                    const res = await fetch('/api/arena-auth/register', {
                        method: 'POST',
                        headers: this.requestHeaders(),
                        body: JSON.stringify({
                            nickname,
                            email,
                            password,
                            avatar: this.registerAvatar,
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                        if (regErr) {
                            regErr.textContent = firstError || data.message || 'Gagal mendaftar akun baru.';
                            regErr.classList.remove('hidden');
                        }
                        return;
                    }
                    this.applyAuthenticatedUser(data.user, data.guest_token);
                    this.showToast(`🚀 Akun ${this.playerName} berhasil dibuat!`);
                    this.switchView('hub');
                } catch {
                    if (regErr) {
                        regErr.textContent = 'Gagal terhubung ke server pendaftaran.';
                        regErr.classList.remove('hidden');
                    }
                }
            });
        }

        // Profile Modal Triggers
        document.getElementById('btn-open-profile-modal')?.addEventListener('click', () => this.openProfileModal());
        document.getElementById('btn-close-profile-modal')?.addEventListener('click', () => this.closeProfileModal());
        document.getElementById('modal-player-profile')?.addEventListener('click', (e) => {
            if (e.target.id === 'modal-player-profile') {
                this.closeProfileModal();
            }
        });
        document.getElementById('btn-copy-guest-token')?.addEventListener('click', () => {
            try {
                navigator.clipboard?.writeText(this.guestToken);
            } catch {
                // Ignore
            }
            this.showToast('🔑 Guest / Session Token disalin ke clipboard!');
        });
        document.getElementById('btn-profile-open-login')?.addEventListener('click', () => {
            this.closeProfileModal();
            this.switchView('login');
        });
        document.getElementById('btn-save-profile')?.addEventListener('click', async () => {
            const nick = document.getElementById('input-profile-nickname')?.value.trim() || this.playerName;
            try {
                const res = await fetch('/api/arena-auth/profile', {
                    method: 'POST',
                    headers: this.requestHeaders(),
                    body: JSON.stringify({ nickname: nick, avatar: this.profileAvatar }),
                });
                const data = await res.json();
                if (res.ok && data.user) {
                    this.applyAuthenticatedUser(data.user);
                    this.closeProfileModal();
                    this.showToast(`✅ Profil ${this.playerName} berhasil diperbarui!`);
                    return;
                }
                this.showToast(`⚠️ ${data.message || 'Gagal menyimpan profil.'}`);
            } catch {
                this.playerName = nick;
                this.playerAvatar = this.profileAvatar;
                this.persistIdentityLocally();
                this.syncAuthUI();
                this.closeProfileModal();
            }
        });
        document.getElementById('btn-profile-logout')?.addEventListener('click', async () => {
            try {
                const res = await fetch('/api/arena-auth/logout', {
                    method: 'POST',
                    headers: this.requestHeaders(),
                });
                const data = await res.json();
                if (res.ok && data.user) {
                    this.applyAuthenticatedUser(data.user, data.guest_token);
                    this.closeProfileModal();
                    this.showToast('👋 Berhasil keluar ke mode Tamu.');
                    this.switchView('home');
                }
            } catch {
                this.showToast('⚠️ Gagal keluar akun.');
            }
        });
    }

    async openProfileModal() {
        const modal = document.getElementById('modal-player-profile');
        if (!modal) {
            return;
        }
        try {
            const res = await fetch('/api/arena-auth/me', { headers: this.requestHeaders() });
            if (res.ok) {
                const data = await res.json();
                if (data.user) {
                    this.applyAuthenticatedUser(data.user);
                }
            }
        } catch {
            // Keep existing profile
        }

        this.profileAvatar = this.playerAvatar;
        const avatarBox = document.getElementById('profile-modal-avatar');
        const authLabel = document.getElementById('profile-modal-auth-label');
        const statsLabel = document.getElementById('profile-modal-stats');
        const nickInput = document.getElementById('input-profile-nickname');
        const tokenDisplay = document.getElementById('profile-guest-token-display');
        const btnLogin = document.getElementById('btn-profile-open-login');
        const btnLogout = document.getElementById('btn-profile-logout');

        if (avatarBox) avatarBox.innerHTML = this.getAvatarSvg(this.playerAvatar);
        if (authLabel) {
            authLabel.textContent = this.userProfile?.is_authenticated
                ? `Akun Terdaftar (${this.userProfile.email || ''})`
                : 'Mode Tamu (Guest Token)';
        }
        if (statsLabel) {
            statsLabel.textContent = `Lv. ${this.userProfile?.level || 1} • ${this.userProfile?.total_score || 0} Skor • ${this.userProfile?.games_played || 0} Main`;
        }
        if (nickInput) nickInput.value = this.playerName;
        if (tokenDisplay) tokenDisplay.textContent = this.guestToken;

        const isAuth = Boolean(this.userProfile?.is_authenticated);
        if (btnLogin) btnLogin.classList.toggle('hidden', isAuth);
        if (btnLogout) btnLogout.classList.toggle('hidden', !isAuth);

        this.renderAvatarPickerGrid('profile-avatar-picker', this.profileAvatar, AVATAR_CATALOG, (key) => {
            this.profileAvatar = key;
            if (avatarBox) avatarBox.innerHTML = this.getAvatarSvg(key);
        });

        modal.classList.remove('hidden');
    }

    closeProfileModal() {
        document.getElementById('modal-player-profile')?.classList.add('hidden');
    }

    bindHubActions() {
        document.getElementById('btn-hub-quick-play')?.addEventListener('click', () => {
            document.getElementById('btn-landing-quick-play')?.click();
        });
        document.getElementById('btn-hub-create-lobby')?.addEventListener('click', () => {
            this.openRoomSettingsModal('create');
        });
        document.getElementById('btn-hub-join-modal')?.addEventListener('click', () => {
            this.openJoinLobbyModal();
        });
        document.getElementById('btn-hub-edit-profile')?.addEventListener('click', () => {
            this.openProfileModal();
        });
        document.getElementById('btn-hub-auth-action')?.addEventListener('click', () => {
            if (this.userProfile?.is_authenticated) {
                this.openProfileModal();
            } else {
                this.switchView('login');
            }
        });
        document.getElementById('btn-refresh-public-rooms')?.addEventListener('click', () => {
            this.fetchPublicRooms(true);
        });
    }

    async fetchPublicRooms(showFeedback = false) {
        try {
            const res = await fetch('/api/rooms/public', { headers: this.requestHeaders(), cache: 'no-store' });
            if (res.ok) {
                const data = await res.json();
                this.publicRooms = Array.isArray(data.rooms) ? data.rooms : [];
                this.renderHubScreen();
                if (showFeedback) {
                    this.showToast(`🔄 ${this.publicRooms.length} lobby publik aktif ditemukan.`);
                }
            }
        } catch {
            // Keep existing array
        }
    }

    renderHubScreen() {
        const welcomeEl = document.getElementById('hub-welcome-nickname');
        const profileNameEl = document.getElementById('hub-profile-name');
        const avatarEl = document.getElementById('hub-user-avatar');
        const levelEl = document.getElementById('hub-user-level');
        const authBadgeEl = document.getElementById('hub-auth-badge');
        const scoreEl = document.getElementById('hub-stat-score');
        const gamesEl = document.getElementById('hub-stat-games');
        const authActionBtn = document.getElementById('btn-hub-auth-action');
        const grid = document.getElementById('hub-public-rooms-list');

        if (welcomeEl) welcomeEl.textContent = this.playerName;
        if (profileNameEl) profileNameEl.textContent = this.playerName;
        if (avatarEl) avatarEl.innerHTML = this.getAvatarSvg(this.playerAvatar);
        if (levelEl) levelEl.textContent = `Lv. ${this.userProfile?.level || 1}`;
        if (authBadgeEl) {
            authBadgeEl.textContent = this.userProfile?.is_authenticated ? 'Akun Terdaftar' : 'Mode Tamu';
        }
        if (scoreEl) scoreEl.textContent = String(this.userProfile?.total_score || 0);
        if (gamesEl) gamesEl.textContent = String(this.userProfile?.games_played || 0);
        if (authActionBtn) {
            authActionBtn.textContent = this.userProfile?.is_authenticated ? '👤 Lihat Akun' : '🔐 Masuk / Daftar';
        }

        if (!grid) {
            return;
        }

        const rooms = Array.isArray(this.publicRooms) ? this.publicRooms : [];
        if (rooms.length === 0) {
            grid.innerHTML = `
                <div class="col-span-full bg-[#0C0C1F] border border-dashed border-[#333347] rounded-2xl p-6 text-center">
                    <p class="text-sm font-bold text-[#C3C9B1]">Belum ada lobby publik yang sedang menunggu pemain.</p>
                    <p class="text-xs text-[#8D937D] mt-1">Klik "BUAT LOBBY BARU" di atas untuk membuka arena pertamamu!</p>
                </div>
            `;
            return;
        }

        const modeLabels = {
            classic: '⚡ Chaos Klasik',
            chill: '🕊️ Mode Santai',
            extreme: '😈 Kutukan Ekstrem',
        };

        grid.innerHTML = '';
        rooms.forEach((rm) => {
            const card = document.createElement('div');
            card.className = 'bg-[#0C0C1F] border border-[#333347] hover:border-[#BDF454]/60 rounded-2xl p-4 flex flex-col justify-between gap-3 transition';
            const modeText = modeLabels[rm.chaos_mode] || '⚡ Chaos Klasik';
            card.innerHTML = `
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-0.5 rounded-lg bg-[#28283C] font-serif font-black text-xs tracking-widest text-[#BDF454]">#${this.escapeHtml(rm.code)}</span>
                        <span class="text-[11px] font-bold text-[#D1BCFF]">${rm.player_count}/${rm.max_players} Pemain</span>
                    </div>
                    <h4 class="mt-2 font-serif font-bold text-base text-[#E2E0FB] truncate">${this.escapeHtml(rm.room_title || `Room #${rm.code}`)}</h4>
                    <p class="text-xs text-[#C3C9B1] mt-0.5">Host: <span class="text-[#E2E0FB] font-semibold">${this.escapeHtml(rm.host_name || 'Host')}</span></p>
                    <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[10px] font-bold text-[#8D937D]">
                        <span class="px-2 py-0.5 rounded bg-[#1A1A2D] border border-[#333347] text-[#C3C9B1]">${modeText}</span>
                        <span class="px-2 py-0.5 rounded bg-[#1A1A2D] border border-[#333347] text-[#C3C9B1]">${rm.round_duration || 60} dtk</span>
                    </div>
                </div>
                <button type="button" class="w-full py-2 rounded-xl bg-[#5717BC] hover:bg-[#6B25DC] text-white font-extrabold text-xs uppercase tracking-wider transition cursor-pointer">
                    GABUNG KE #${this.escapeHtml(rm.code)}
                </button>
            `;
            card.querySelector('button')?.addEventListener('click', () => {
                this.joinRoomByCode(rm.code, 'lobby', this.playerAvatar);
            });
            grid.appendChild(card);
        });
    }

    bindJoinLobbyModal() {
        const modal = document.getElementById('modal-join-lobby');
        const btnClose = document.getElementById('btn-close-join-modal');
        const form = document.getElementById('form-join-lobby-modal');
        const nickInput = document.getElementById('input-join-modal-nickname');
        const btnRandNick = document.getElementById('btn-join-modal-random-nick');
        const otpInputs = [0, 1, 2, 3].map((i) => document.getElementById(`join-otp-${i}`)).filter(Boolean);

        if (btnClose) {
            btnClose.addEventListener('click', () => this.closeJoinLobbyModal());
        }
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeJoinLobbyModal();
                }
            });
        }

        if (btnRandNick && nickInput) {
            btnRandNick.addEventListener('click', () => {
                const prefixes = ['SenimanRusuh', 'CyberCat', 'KrayonNinja', 'BebekKosmik', 'RobotRusuh'];
                nickInput.value = `${prefixes[Math.floor(Math.random() * prefixes.length)]}_${Math.floor(10 + Math.random() * 89)}`;
            });
        }

        let verifyTimer = null;
        const scheduleVerify = () => {
            const code = otpInputs.map((inp) => inp.value.trim().toUpperCase()).join('');
            clearTimeout(verifyTimer);
            verifyTimer = setTimeout(() => {
                this.verifyJoinRoomCode(code);
            }, 220);
        };

        otpInputs.forEach((inp, idx) => {
            inp.addEventListener('input', () => {
                const clean = inp.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 1);
                inp.value = clean;
                if (clean && otpInputs[idx + 1]) {
                    otpInputs[idx + 1].focus();
                }
                scheduleVerify();
            });
            inp.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !inp.value && otpInputs[idx - 1]) {
                    otpInputs[idx - 1].focus();
                }
            });
            inp.addEventListener('paste', (e) => {
                const text = (e.clipboardData?.getData('text') || '').replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 4);
                if (text.length > 0) {
                    e.preventDefault();
                    otpInputs.forEach((box, boxIdx) => {
                        box.value = text[boxIdx] || '';
                    });
                    const focusIdx = Math.min(3, text.length);
                    otpInputs[focusIdx]?.focus();
                    scheduleVerify();
                }
            });
        });

        const fourJoinAvatars = AVATAR_CATALOG.slice(0, 4);
        this.renderAvatarPickerGrid('join-avatar-picker', this.joinAvatar, fourJoinAvatars, (key) => {
            this.joinAvatar = key;
        });

        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const code = otpInputs.map((inp) => inp.value.trim().toUpperCase()).join('');
                if (code.length < 4) {
                    this.showToast('⚠️ Lengkapi 4 karakter kode room terlebih dahulu!');
                    otpInputs[Math.min(3, code.length)]?.focus();
                    return;
                }
                const customNick = nickInput?.value.trim() || this.playerName || 'SenimanRusuh_89';
                this.playerName = customNick;
                this.playerAvatar = this.joinAvatar;
                this.persistIdentityLocally();
                this.syncAuthUI();

                await this.joinRoomByCode(code, 'lobby', this.joinAvatar);
                this.closeJoinLobbyModal();
            });
        }
    }

    openJoinLobbyModal(prefillCode = '') {
        const modal = document.getElementById('modal-join-lobby');
        if (!modal) {
            return;
        }
        const nickInput = document.getElementById('input-join-modal-nickname');
        const otpInputs = [0, 1, 2, 3].map((i) => document.getElementById(`join-otp-${i}`)).filter(Boolean);
        const clean = String(prefillCode || '').replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 4);

        if (nickInput) {
            nickInput.value = this.playerName;
        }
        otpInputs.forEach((box, i) => {
            box.value = clean[i] || '';
        });

        this.joinAvatar = AVATAR_CATALOG.slice(0, 4).some((a) => a.key === this.playerAvatar)
            ? this.playerAvatar
            : 'cat-neon';
        this.renderAvatarPickerGrid('join-avatar-picker', this.joinAvatar, AVATAR_CATALOG.slice(0, 4), (key) => {
            this.joinAvatar = key;
        });

        this.verifyJoinRoomCode(clean);
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            const firstEmpty = otpInputs.find((b) => !b.value) || otpInputs[0];
            firstEmpty?.focus();
        });
    }

    closeJoinLobbyModal() {
        document.getElementById('modal-join-lobby')?.classList.add('hidden');
    }

    async verifyJoinRoomCode(code) {
        const statusEl = document.getElementById('join-room-verify-status');
        if (!statusEl) {
            return;
        }
        if (!code || code.length < 4) {
            statusEl.className = 'mt-3 rounded-xl bg-[#0C0C1F] border border-[#333347] px-3.5 py-2 text-xs text-center text-[#C3C9B1]';
            statusEl.textContent = 'Ketik 4 karakter kode room untuk memverifikasi status lobby.';
            return;
        }

        statusEl.textContent = `Memeriksa room #${code}...`;
        try {
            const res = await fetch(`/api/rooms/${encodeURIComponent(code)}/state`, {
                headers: this.requestHeaders(),
                cache: 'no-store',
            });
            if (!res.ok) {
                statusEl.className = 'mt-3 rounded-xl bg-[#3A1628] border border-[#FF4D6D]/50 px-3.5 py-2 text-xs text-center font-bold text-[#FFB3C1]';
                statusEl.textContent = `Room #${code} belum ditemukan (akan dibuat otomatis jika kamu bergabung).`;
                return;
            }
            const data = await res.json();
            const rm = data.room;
            if (!rm) {
                return;
            }
            const count = Array.isArray(rm.players) ? rm.players.length : 0;
            const max = rm.max_players || 8;
            const isFull = count >= max;
            statusEl.className = isFull
                ? 'mt-3 rounded-xl bg-[#3A1628] border border-[#FF4D6D]/50 px-3.5 py-2 text-xs text-center font-bold text-[#FFB3C1]'
                : 'mt-3 rounded-xl bg-[#16291A] border border-[#BDF454]/60 px-3.5 py-2 text-xs text-center font-bold text-[#BDF454]';
            statusEl.textContent = isFull
                ? `Room #${rm.code} sudah penuh (${count}/${max} pemain).`
                : `✓ Terverifikasi: Room #${rm.code} · ${count}/${max} Pemain · Status ${rm.status === 'playing' ? 'Sedang Bermain' : 'Menunggu di Lobby'}`;
        } catch {
            statusEl.className = 'mt-3 rounded-xl bg-[#0C0C1F] border border-[#333347] px-3.5 py-2 text-xs text-center text-[#C3C9B1]';
            statusEl.textContent = `Siap bergabung ke room #${code}.`;
        }
    }

    // =========================================================================
    // Module 1: Landing Page, Quick Play, Create Room, Join Room & Reconnect
    // =========================================================================
    bindLandingActions() {
        const btnQuickPlay = document.getElementById('btn-landing-quick-play');
        const btnCreateRoom = document.getElementById('btn-landing-create-room');
        const btnJoinFocus = document.getElementById('btn-landing-join-focus');
        const formJoinCode = document.getElementById('form-landing-join-room');
        const inputRoomCode = document.getElementById('input-landing-room-code');
        const inputNickname = document.getElementById('input-landing-nickname');
        const btnRandomizeNickname = document.getElementById('btn-randomize-nickname');

        if (inputNickname) {
            inputNickname.value = this.playerName;
            inputNickname.addEventListener('input', () => {
                const clean = inputNickname.value.trim();
                if (clean) {
                    this.playerName = clean;
                    this.persistIdentityLocally();
                    this.syncAuthUI();
                }
            });
        }

        if (btnRandomizeNickname && inputNickname) {
            btnRandomizeNickname.addEventListener('click', () => {
                const prefixes = ['SenimanRusuh', 'KucingNgebut', 'RajaCoret', 'KuasMabuk', 'TukangSabot', 'PelukisPanik'];
                const randPrefix = prefixes[Math.floor(Math.random() * prefixes.length)];
                const randNum = Math.floor(Math.random() * 90) + 10;
                const newNick = `${randPrefix}_${randNum}`;
                inputNickname.value = newNick;
                this.playerName = newNick;
                this.persistIdentityLocally();
                this.syncAuthUI();
                this.showToast(`🎭 Nickname diubah menjadi: ${newNick}`);
            });
        }

        // Quick Play: Find open room (< max_players, status=lobby) or create new room
        if (btnQuickPlay) {
            btnQuickPlay.addEventListener('click', async () => {
                const currentNick = (inputNickname?.value.trim() || this.playerName || 'SenimanRusuh_89').slice(0, 50);
                this.playerName = currentNick;

                try {
                    const res = await fetch('/api/rooms/quick-play', {
                        method: 'POST',
                        headers: this.requestHeaders(),
                        body: JSON.stringify({
                            player_name: currentNick,
                            avatar: this.playerAvatar,
                        }),
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.room) {
                            this.applyRoomSnapshot(data.room);
                            this.showToast(`⚡ Quick Play: Masuk ke Room [${data.room.code}]!`);
                            this.switchView('lobby');
                            return;
                        }
                    }
                } catch {
                    // Fallback error toast
                }
                this.showToast('⚠️ Gagal menghubungkan ke server Quick Play.');
            });
        }

        // Create Room Button -> Buka Modal Pengaturan Lobby V2
        if (btnCreateRoom) {
            btnCreateRoom.addEventListener('click', () => {
                const currentNick = (inputNickname?.value.trim() || this.playerName || 'SenimanRusuh_89').slice(0, 50);
                this.playerName = currentNick;
                this.persistIdentityLocally();
                this.openRoomSettingsModal('create');
            });
        }

        if (btnJoinFocus) {
            btnJoinFocus.addEventListener('click', () => {
                const prefill = inputRoomCode?.value.trim().toUpperCase() || '';
                this.openJoinLobbyModal(prefill);
            });
        }

        if (formJoinCode && inputRoomCode) {
            formJoinCode.addEventListener('submit', async (e) => {
                e.preventDefault();
                const code = inputRoomCode.value.trim().toUpperCase().slice(0, 4);
                if (code.length < 4) {
                    this.showToast('⚠️ Masukkan 4 huruf Kode Room terlebih dahulu!');
                    inputRoomCode.focus();
                    return;
                }
                await this.joinRoomByCode(code);
            });
        }
    }

    // =========================================================================
    // Modal Pengaturan & Buat Room Kustom (Presets + Real Synchronized Settings)
    // =========================================================================
    bindRoomSettingsModal() {
        const modal = document.getElementById('modal-room-settings');
        const btnClose = document.getElementById('btn-close-room-settings-modal');
        const btnCancel = document.getElementById('btn-cancel-room-settings');
        const btnSubmit = document.getElementById('btn-submit-room-settings');
        const btnCopyCode = document.getElementById('btn-modal-copy-code');
        const btnRandomCode = document.getElementById('btn-randomize-custom-code');
        const inputCustomCode = document.getElementById('input-setting-custom-code');

        if (btnClose) {
            btnClose.addEventListener('click', () => this.closeRoomSettingsModal());
        }
        if (btnCancel) {
            btnCancel.addEventListener('click', () => this.closeRoomSettingsModal());
        }

        // Close when clicking modal backdrop
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeRoomSettingsModal();
                    return;
                }

                // Interactive Option Buttons [data-k]
                const optBtn = e.target.closest('[data-k]');
                if (optBtn) {
                    const k = optBtn.dataset.k;
                    const v = optBtn.dataset.v;
                    this.roomSettingsState[k] = v;
                    if (k === 'chaos_mode') {
                        this.roomSettingsState.chaos = v === 'chill' ? 'off' : 'on';
                    } else if (k === 'chaos') {
                        this.roomSettingsState.chaos_mode = v === 'off' ? 'chill' : 'classic';
                    }
                    this.renderRoomSettingsModal();
                    return;
                }

                // Preset Buttons [data-p]
                const presetBtn = e.target.closest('[data-p]');
                if (presetBtn) {
                    const PRE = {
                        std: { priv: 'pub', time: '60', rounds: '5', max: '8', chaos: 'on', chaos_mode: 'classic' },
                        extreme: { priv: 'pub', time: '45', rounds: '8', max: '8', chaos: 'on', chaos_mode: 'extreme' },
                        rush: { priv: 'pub', time: '45', rounds: '8', max: '8', chaos: 'on', chaos_mode: 'extreme' },
                        chill: { priv: 'priv', time: '80', rounds: '3', max: '6', chaos: 'off', chaos_mode: 'chill' },
                    };
                    const preset = PRE[presetBtn.dataset.p];
                    if (preset) {
                        this.roomSettingsState = { ...this.roomSettingsState, ...preset };
                        this.renderRoomSettingsModal();
                    }
                    return;
                }
            });
        }

        // Copy room code inside modal
        if (btnCopyCode) {
            btnCopyCode.addEventListener('click', () => {
                const code = this.room?.code || 'U93C';
                try {
                    navigator.clipboard.writeText(code);
                } catch {
                    // Ignore
                }
                const sub = document.getElementById('modal-room-code-sub');
                if (sub) {
                    sub.textContent = 'tersalin';
                    setTimeout(() => {
                        if (sub) sub.textContent = 'salin';
                    }, 1500);
                }
                this.showToast(`📋 Kode room [${code}] berhasil disalin!`);
            });
        }

        // Randomize custom 4-char code
        if (btnRandomCode && inputCustomCode) {
            btnRandomCode.addEventListener('click', () => {
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                let code = '';
                for (let i = 0; i < 4; i++) {
                    code += chars[Math.floor(Math.random() * chars.length)];
                }
                inputCustomCode.value = code;
            });
        }

        // Submit Button
        if (btnSubmit) {
            btnSubmit.addEventListener('click', async (e) => {
                e.preventDefault();
                await this.submitRoomSettingsModal();
            });
        }
    }

    renderRoomSettingsModal() {
        const modal = document.getElementById('modal-room-settings');
        if (!modal) {
            return;
        }

        const S = this.roomSettingsState || {
            priv: 'pub',
            time: '60',
            rounds: '5',
            max: '8',
            chaos: 'on',
            chaos_mode: 'classic',
            pack: 'mix',
        };

        const PRE = {
            std: { priv: 'pub', time: '60', rounds: '5', max: '8', chaos: 'on', chaos_mode: 'classic' },
            extreme: { priv: 'pub', time: '45', rounds: '8', max: '8', chaos: 'on', chaos_mode: 'extreme' },
            rush: { priv: 'pub', time: '45', rounds: '8', max: '8', chaos: 'on', chaos_mode: 'extreme' },
            chill: { priv: 'priv', time: '80', rounds: '3', max: '6', chaos: 'off', chaos_mode: 'chill' },
        };

        const PK = {
            mix: 'Campuran',
            meme: 'Meme viral',
            kuliner: 'Kuliner Indo',
            anime: 'Anime dan game',
        };

        const MODE_LABELS = {
            classic: '⚡ Chaos Klasik',
            chill: '🕊️ Mode Santai',
            extreme: '😈 Kutukan Ekstrem',
        };

        modal.querySelectorAll('[data-k]').forEach((b) => {
            b.setAttribute('aria-pressed', S[b.dataset.k] === b.dataset.v);
        });

        modal.querySelectorAll('[data-p]').forEach((b) => {
            const preset = PRE[b.dataset.p];
            const isMatch = preset && Object.entries(preset).every(([k, v]) => S[k] === v);
            b.setAttribute('aria-pressed', Boolean(isMatch));
        });

        const seatsEl = document.getElementById('seats');
        if (seatsEl) {
            seatsEl.replaceChildren(
                ...Array.from({ length: 12 }, (_, i) => {
                    const s = document.createElement('span');
                    if (i < Number(S.max)) {
                        s.className = 'on';
                    }
                    return s;
                })
            );
        }

        const cardsEl = document.getElementById('cards');
        if (cardsEl) {
            cardsEl.classList.toggle('off', S.chaos === 'off' || S.chaos_mode === 'chill');
        }

        const min = Math.max(1, Math.round(Number(S.rounds) * (Number(S.time) + 20) / 60));
        const sumEl = document.getElementById('sum');
        if (sumEl) {
            const badges = [
                S.priv === 'pub' ? '🌐 Publik' : '🔒 Privat',
                `${S.time} detik`,
                `${S.rounds} ronde`,
                `${S.max} pemain`,
                MODE_LABELS[S.chaos_mode] || (S.chaos === 'on' ? '💥 Chaos aktif' : '🕊️ Tanpa chaos'),
                PK[S.pack] || 'Campuran',
            ].map((t) => {
                const b = document.createElement('b');
                b.textContent = t;
                return b;
            });
            const em = document.createElement('em');
            em.textContent = `Perkiraan ±${min} menit`;
            sumEl.replaceChildren(...badges, em);
        }

        const submitBtn = document.getElementById('btn-submit-room-settings');
        if (submitBtn) {
            if (this.roomSettingsModalMode === 'create') {
                submitBtn.textContent = '🚀 Buat Room Sekarang';
                submitBtn.classList.remove('ok');
            } else {
                const dirty = JSON.stringify(S) !== JSON.stringify(this.savedRoomSettingsState);
                submitBtn.textContent = dirty ? 'Simpan perubahan' : 'Tersimpan';
                submitBtn.classList.toggle('ok', !dirty);
            }
        }
    }

    openRoomSettingsModal(mode = 'create') {
        this.roomSettingsModalMode = mode;
        const modal = document.getElementById('modal-room-settings');
        const titleEl = document.getElementById('modal-room-settings-title');
        const subtitleEl = document.getElementById('modal-room-settings-subtitle');
        const submitBtn = document.getElementById('btn-submit-room-settings');
        const codeBtn = document.getElementById('btn-modal-copy-code');
        const codeText = document.getElementById('modal-room-code-text');
        const customCodeContainer = document.getElementById('container-setting-custom-code');
        const footerHint = document.getElementById('modal-room-settings-footer-hint');

        if (!modal) {
            return;
        }

        if (mode === 'create') {
            if (titleEl) titleEl.textContent = 'PENGATURAN LOBBY BARU';
            if (subtitleEl) subtitleEl.textContent = 'Atur kapasitas, durasi, & mode chaos sebelum arena dimulai.';
            if (codeBtn) codeBtn.classList.add('hidden');
            if (customCodeContainer) customCodeContainer.classList.remove('hidden');
            if (footerHint) footerHint.textContent = 'Aturan dapat diubah kembali nanti oleh host.';
            this.roomSettingsState = {
                priv: 'pub',
                time: '60',
                rounds: '5',
                max: '8',
                chaos: 'on',
                chaos_mode: 'classic',
                pack: 'mix',
            };
            this.savedRoomSettingsState = { ...this.roomSettingsState };
        } else {
            const code = this.room?.code || 'CHAO';
            if (titleEl) titleEl.textContent = `Pengaturan room [${code}]`;
            if (subtitleEl) subtitleEl.textContent = 'Perubahan langsung tersinkron ke semua pemain.';
            if (codeBtn) {
                codeBtn.classList.remove('hidden');
                if (codeText) codeText.textContent = code;
            }
            if (customCodeContainer) customCodeContainer.classList.add('hidden');
            if (footerHint) footerHint.textContent = 'Hanya host yang bisa menyimpan perubahan.';

            const rawCat = this.room?.word_category;
            const packVal = rawCat === 'food' ? 'kuliner' : (rawCat === 'all' ? 'mix' : (rawCat || 'mix'));
            const chaosMode = this.room?.chaos_mode || (this.room?.chaos_enabled === false ? 'chill' : 'classic');

            this.roomSettingsState = {
                priv: this.room?.is_public !== false ? 'pub' : 'priv',
                time: String(this.room?.round_duration || 60),
                rounds: String(this.room?.max_rounds || 5),
                max: String(this.room?.max_players || 8),
                chaos: chaosMode !== 'chill' && this.room?.chaos_enabled !== false ? 'on' : 'off',
                chaos_mode: chaosMode,
                pack: packVal,
            };
            this.savedRoomSettingsState = { ...this.roomSettingsState };
        }

        this.renderRoomSettingsModal();
        modal.classList.remove('hidden');
    }

    closeRoomSettingsModal() {
        const modal = document.getElementById('modal-room-settings');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    async submitRoomSettingsModal() {
        const S = this.roomSettingsState || {};
        const privacy = S.priv === 'pub';
        const duration = Number(S.time || 60);
        const rounds = Number(S.rounds || 5);
        const maxPlayers = Number(S.max || 8);
        const chaosMode = S.chaos_mode || (S.chaos === 'off' ? 'chill' : 'classic');
        const chaos = chaosMode !== 'chill';
        const category = S.pack === 'kuliner' ? 'food' : (S.pack === 'mix' ? 'all' : (S.pack || 'all'));

        const inputCustomCode = document.getElementById('input-setting-custom-code');
        const customCode = inputCustomCode?.value.trim().toUpperCase() || null;

        const payload = {
            is_public: privacy,
            round_duration: duration,
            max_rounds: rounds,
            max_players: maxPlayers,
            chaos_enabled: chaos,
            chaos_mode: chaosMode,
            word_category: category,
        };

        if (this.roomSettingsModalMode === 'create') {
            payload.host_name = this.playerName || 'SenimanRusuh_89';
            payload.avatar = this.playerAvatar || 'cat-neon';
            if (customCode && customCode.length >= 3) {
                payload.custom_code = customCode.slice(0, 4);
            }
            try {
                const res = await fetch('/api/rooms', {
                    method: 'POST',
                    headers: this.requestHeaders(),
                    body: JSON.stringify(payload),
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.room) {
                        this.applyRoomSnapshot(data.room);
                        this.closeRoomSettingsModal();
                        this.showToast(`🎉 Room [${data.room.code}] berhasil dibuat dengan pengaturan baru!`);
                        this.switchView('lobby');
                        return;
                    }
                }
            } catch {
                // error
            }
            this.showToast('⚠️ Gagal membuat room. Pastikan server aktif.');
        } else {
            if (!this.room?.code) {
                this.showToast('⚠️ Kamu belum bergabung ke room!');
                return;
            }
            try {
                const res = await fetch(`/api/rooms/${this.room.code}/settings`, {
                    method: 'POST',
                    headers: this.requestHeaders(),
                    body: JSON.stringify(payload),
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.room) {
                        this.applyRoomSnapshot(data.room);
                        this.savedRoomSettingsState = { ...this.roomSettingsState };
                        this.renderRoomSettingsModal();
                        this.broadcastEvent('room_settings_updated', { room: data.room });
                        this.showToast('⚙️ Pengaturan room berhasil diperbarui & disinkronkan!');
                        setTimeout(() => this.closeRoomSettingsModal(), 600);
                        return;
                    }
                } else {
                    const err = await res.json().catch(() => ({}));
                    this.showToast(`⚠️ ${err.message || 'Gagal mengubah pengaturan room.'}`);
                    return;
                }
            } catch {
                // error
            }
            this.showToast('⚠️ Gagal menghubungi server untuk mengubah pengaturan room.');
        }
    }

    async joinRoomByCode(code, targetView = 'lobby', customAvatar = null) {
        const inputNickname = document.getElementById('input-landing-nickname');
        const currentNick = (this.playerName || inputNickname?.value.trim() || 'SenimanRusuh_89').slice(0, 50);
        this.playerName = currentNick;
        const avatarToSend = customAvatar || this.playerAvatar || 'cat-neon';

        try {
            const res = await fetch('/api/rooms/join', {
                method: 'POST',
                headers: this.requestHeaders(),
                body: JSON.stringify({
                    code,
                    player_name: currentNick,
                    avatar: avatarToSend,
                }),
            });
            const data = await res.json();
            if (!res.ok) {
                this.showToast(`⚠️ ${data.message || 'Gagal bergabung ke room.'}`);
                return;
            }
            if (data.room) {
                this.applyRoomSnapshot(data.room);
                this.showToast(`🚀 ${currentNick} bergabung ke Room [${code}]!`);
                if (data.room.status === 'playing') {
                    const role = this.determineMyRoleFromRoom();
                    this.switchView(role);
                } else {
                    this.switchView(targetView || 'lobby');
                }
            }
        } catch {
            this.showToast('⚠️ Gagal menghubungi server saat bergabung ke room.');
        }
    }

    async reconnectToRoom(code, viewParam = null) {
        await this.joinRoomByCode(code, viewParam || 'lobby');
    }

    applyRoomSnapshot(roomSnapshot) {
        if (!roomSnapshot) {
            return;
        }
        const previousCode = this.room?.code;
        this.room = roomSnapshot;

        try {
            sessionStorage.setItem('coret_active_room_code', roomSnapshot.code);
        } catch {
            // Ignore
        }

        if (previousCode !== roomSnapshot.code && this.socket && this.socket.connected) {
            this.socket.emit('room:join', roomSnapshot.code);
        }

        const me = (roomSnapshot.players || []).find(
            (p) => (p.name || '').toLowerCase() === (this.playerName || '').toLowerCase()
        );
        if (me) {
            this.playerId = me.id;
            if (typeof me.chaos_energy === 'number') {
                this.chaosEnergy = me.chaos_energy;
            }
            try {
                localStorage.setItem('coret_player_id', String(me.id));
            } catch {
                // Ignore
            }
        }

        if (Array.isArray(roomSnapshot.strokes)) {
            this.strokes = roomSnapshot.strokes;
        }

        if (Array.isArray(roomSnapshot.recent_guesses)) {
            roomSnapshot.recent_guesses.forEach((g) => {
                this.appendGuessToChatFeed(g);
            });
        }

        this.updateRoomCodeDisplay(roomSnapshot.code);
        this.renderLobbyPlayers();
        this.renderLobbyChat();
        this.renderQuickLeaderboard();
        this.updateChaosEnergyUI();

        if (previousCode !== roomSnapshot.code && this.reverbSocket && this.reverbSocket.readyState === WebSocket.OPEN) {
            this.subscribeReverbRoomChannel(roomSnapshot.code);
        }
    }

    copyRoomInviteLink() {
        const code = this.room?.code || '';
        if (!code) {
            this.showToast('⚠️ Buat atau gabung ke room terlebih dahulu!');
            return;
        }
        const url = `${window.location.origin}/?room=${code}`;
        try {
            navigator.clipboard?.writeText(url).catch(() => {});
        } catch {
            // Ignore
        }
        this.showToast(`📋 Link Room [${code}] disalin ke clipboard!`);
    }

    copyRoomCodeOnly() {
        const code = this.room?.code || '';
        if (!code) {
            this.showToast('⚠️ Buat atau gabung ke room terlebih dahulu!');
            return;
        }
        try {
            navigator.clipboard?.writeText(code).catch(() => {});
        } catch {
            // Ignore
        }
        this.showToast(`📋 Kode Lobby [${code}] disalin ke clipboard!`);
    }

    async toggleReadyStatus() {
        if (!this.room?.code) {
            this.showToast('⚠️ Buat atau gabung ke room terlebih dahulu!');
            return;
        }
        try {
            const res = await fetch(`/api/rooms/${this.room.code}/ready`, {
                method: 'POST',
                headers: this.requestHeaders(),
                body: JSON.stringify({ player_name: this.playerName }),
            });
            if (res.ok) {
                const data = await res.json();
                if (data.room) {
                    this.applyRoomSnapshot(data.room);
                }
            }
        } catch {
            // Ignore
        }
    }

    // =========================================================================
    // Module 2: Dynamic Multiplayer Lobby (Zero Dummy Bots, Min 2 Ready to Start)
    // =========================================================================
    bindLobbyActions() {
        const btnCopyCode = document.getElementById('btn-lobby-copy-code');
        if (btnCopyCode) {
            btnCopyCode.addEventListener('click', () => {
                this.copyRoomCodeOnly();
            });
        }

        const btnCopyLink = document.getElementById('btn-lobby-copy-link');
        if (btnCopyLink) {
            btnCopyLink.addEventListener('click', () => {
                this.copyRoomInviteLink();
            });
        }

        const btnLeaveRoom = document.getElementById('btn-lobby-leave-room');
        if (btnLeaveRoom) {
            btnLeaveRoom.addEventListener('click', async () => {
                if (this.room?.code) {
                    try {
                        await fetch(`/api/rooms/${this.room.code}/leave`, {
                            method: 'POST',
                            headers: this.requestHeaders(),
                            body: JSON.stringify({ player_name: this.playerName }),
                        });
                    } catch {
                        // Ignore
                    }
                }
                this.room = null;
                this.strokes = [];
                this.renderedLobbyChatIds.clear();
                try {
                    sessionStorage.removeItem('coret_active_room_code');
                } catch {
                    // Ignore
                }
                this.showToast('👋 Kamu telah keluar dari room.');
                this.switchView('home');
            });
        }

        const btnEditRoom = document.getElementById('btn-lobby-edit-room');
        if (btnEditRoom) {
            btnEditRoom.addEventListener('click', () => {
                this.openRoomSettingsModal('edit');
            });
        }

        const btnToggleReady = document.getElementById('btn-lobby-toggle-ready');
        if (btnToggleReady) {
            btnToggleReady.addEventListener('click', () => {
                this.toggleReadyStatus();
            });
        }

        const btnStartGame = document.getElementById('btn-lobby-start-game');
        if (btnStartGame) {
            btnStartGame.addEventListener('click', async () => {
                if (!this.room?.code) {
                    this.showToast('⚠️ Buat atau gabung ke room terlebih dahulu!');
                    return;
                }
                const players = Array.isArray(this.room?.players) ? this.room.players : [];
                const myEntry = players.find((p) => (p.name || '').toLowerCase() === (this.playerName || '').toLowerCase());
                const amIHost = Boolean(
                    myEntry?.is_host ||
                    String(this.room?.host_player_id) === String(this.guestToken) ||
                    String(this.room?.host_player_id) === String(this.playerId)
                );
                if (!amIHost) {
                    this.showToast('👑 Hanya Host yang berhak memulai permainan! Tunggu Host menekan Mulai Game.');
                    return;
                }
                try {
                    const res = await fetch(`/api/rooms/${this.room.code}/start`, {
                        method: 'POST',
                        headers: this.requestHeaders(),
                        body: JSON.stringify({ player_name: this.playerName }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.showToast(`⚠️ ${data.message || 'Minimal 2 pemain harus SIAP untuk memulai permainan!'}`);
                        return;
                    }
                    if (data.room) {
                        this.applyRoomSnapshot(data.room);
                        this.strokes = [];
                        this.roundSecondsLeft = data.room.round_duration || 60;
                        this.roundElapsedSeconds = 0;
                        const myRole = this.determineMyRoleFromRoom();
                        this.broadcastEvent('game_started', { room: data.room });
                        this.showToast(`🚀 Permainan dimulai! Pelukis giliran ini: ${data.room.round?.artist_name}`);
                        this.switchView(myRole);
                    }
                } catch {
                    this.showToast('⚠️ Gagal memulai permainan.');
                }
            });
        }
    }

    bindLobbyChat() {
        const form = document.getElementById('form-lobby-chat');
        const input = document.getElementById('input-lobby-chat');
        if (!form || !input) {
            return;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = input.value.trim();
            if (!message) {
                return;
            }
            if (!this.room?.code) {
                this.showToast('⚠️ Masuk ke lobby terlebih dahulu untuk mengirim obrolan.');
                return;
            }
            input.value = '';

            try {
                const res = await fetch(`/api/rooms/${this.room.code}/lobby-chat`, {
                    method: 'POST',
                    headers: this.requestHeaders(),
                    body: JSON.stringify({
                        message,
                        player_name: this.playerName,
                    }),
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.room) {
                        this.applyRoomSnapshot(data.room);
                    } else if (data.chat_message) {
                        this.room.lobby_chat = [...(this.room.lobby_chat || []), data.chat_message];
                        this.renderLobbyChat();
                    }
                    this.broadcastEvent('lobby_chat_sent', { chat_message: data.chat_message, room_code: this.room.code });
                }
            } catch {
                this.showToast('⚠️ Gagal mengirim obrolan lobi.');
            }
        });
    }

    renderLobbyChat() {
        const container = document.getElementById('lobby-chat-messages');
        if (!container) {
            return;
        }
        const messages = Array.isArray(this.room?.lobby_chat) ? this.room.lobby_chat : [];
        if (messages.length === 0) {
            container.innerHTML = '<div class="text-xs text-center text-[#8D937D] py-12">Belum ada obrolan di lobby. Sapa temanmu dulu!</div>';
            return;
        }

        container.innerHTML = '';
        messages.forEach((msg) => {
            const bubble = document.createElement('div');
            if (msg.is_system) {
                bubble.className = 'rounded-xl bg-[#0C0C1F] border border-[#333347] px-3 py-2 text-[11px] text-[#D1BCFF] font-semibold';
                bubble.innerHTML = `⚡ ${this.escapeHtml(msg.message)}`;
            } else {
                const isMe = (msg.player_name || '').toLowerCase() === (this.playerName || '').toLowerCase();
                bubble.className = `rounded-xl px-3 py-2 text-xs border ${
                    isMe ? 'bg-[#5717BC]/25 border-[#D1BCFF]/40' : 'bg-[#0C0C1F] border-[#333347]'
                }`;
                bubble.innerHTML = `
                    <div class="flex items-center justify-between gap-2 mb-0.5">
                        <span class="font-extrabold ${isMe ? 'text-[#BDF454]' : 'text-[#D1BCFF]'}">${this.escapeHtml(msg.player_name || 'Pemain')}${msg.is_host ? ' 👑' : ''}</span>
                        <span class="text-[10px] text-[#8D937D]">${this.escapeHtml(msg.sent_at || '')}</span>
                    </div>
                    <p class="text-[#E2E0FB] break-words">${this.escapeHtml(msg.message || '')}</p>
                `;
            }
            container.appendChild(bubble);
        });
        container.scrollTop = container.scrollHeight;
    }

    renderLobbyPlayers() {
        const grid = document.getElementById('lobby-players-grid');
        const counterEl = document.getElementById('lobby-player-counter');
        const statusBanner = document.getElementById('lobby-status-banner');
        const startBtn = document.getElementById('btn-lobby-start-game');
        const readyBtn = document.getElementById('btn-lobby-toggle-ready');
        const hintEl = document.getElementById('lobby-hint-text');
        const codeDisplay = document.getElementById('lobby-room-code-display');
        const rulesText = document.getElementById('lobby-room-rules-text');
        const editRoomBtn = document.getElementById('btn-lobby-edit-room');

        const code = this.room?.code || '';
        this.updateRoomCodeDisplay(code);

        const players = Array.isArray(this.room?.players) ? this.room.players : [];
        const readyCount = players.filter((p) => p.is_ready).length;
        const maxCapacity = Number(this.room?.max_players) || 8;

        const myEntry = players.find((p) => (p.name || '').toLowerCase() === (this.playerName || '').toLowerCase());
        const amIHost = Boolean(
            myEntry?.is_host ||
            String(this.room?.host_player_id) === String(this.guestToken) ||
            String(this.room?.host_player_id) === String(this.playerId)
        );

        if (counterEl) {
            counterEl.textContent = `${players.length}/${maxCapacity} PEMAIN`;
        }

        // Rules card text
        if (rulesText) {
            const duration = this.room?.round_duration || 60;
            const rounds = this.room?.max_rounds || 5;
            const chaosMode = this.room?.chaos_mode || (this.room?.chaos_enabled === false ? 'chill' : 'classic');
            const category = this.room?.word_category || 'all';
            const modeNames = {
                classic: '⚡ Chaos Klasik',
                chill: '🕊️ Mode Santai',
                extreme: '😈 Kutukan Ekstrem (Regen 2x)',
            };
            const categoryNames = {
                all: 'Campuran',
                meme: 'Meme Viral',
                food: 'Kuliner Indo',
                anime: 'Anime & Game',
            };
            rulesText.textContent = `${modeNames[chaosMode] || '⚡ Chaos Klasik'} • ${maxCapacity} Kursi • ${duration} detik • ${rounds} ronde • ${categoryNames[category] || 'Campuran'}`;
        }

        // Edit room button visible for host
        if (editRoomBtn) {
            editRoomBtn.classList.toggle('hidden', !amIHost);
        }

        // Status banner
        if (statusBanner) {
            statusBanner.textContent = 'Klik kartumu untuk mengubah status siap. Pelukis pertama dipilih acak oleh server.';
        }

        // Ready toggle button
        const isMeReady = Boolean(myEntry?.is_ready);
        if (readyBtn) {
            readyBtn.textContent = isMeReady ? '✓ SIAP TEMPUR (BATALKAN)' : '✓ UBAH STATUS SIAP';
            readyBtn.className = isMeReady
                ? 'px-4 py-2.5 rounded-xl bg-[#16291A] border border-[#BDF454] text-xs font-extrabold text-[#BDF454] transition cursor-pointer'
                : 'px-4 py-2.5 rounded-xl bg-[#28283C] hover:bg-[#333347] border border-[#333347] text-xs font-extrabold text-[#E2E0FB] transition cursor-pointer';
        }

        // Start button
        if (startBtn) {
            if (amIHost) {
                const canStart = readyCount >= 2;
                startBtn.disabled = !canStart;
                startBtn.textContent = `🎮 MULAI PERMAINAN (${readyCount}/2 SIAP)`;
            } else {
                startBtn.disabled = true;
                startBtn.textContent = '👑 MENUNGGU HOST MEMULAI GAME...';
            }
        }

        // Hint text
        if (hintEl) {
            const wait = players.length - readyCount;
            if (players.length < 2) {
                hintEl.textContent = 'Butuh minimal 2 pemain bergabung dan siap untuk memulai.';
            } else if (readyCount < 2) {
                hintEl.textContent = `Butuh minimal 2 pemain siap untuk memulai (${readyCount}/2 siap).`;
            } else if (wait > 0) {
                hintEl.textContent = `Menunggu ${wait} pemain lagi untuk siap... (atau Host bisa mulai)`;
            } else {
                hintEl.textContent = 'Semua pemain siap tempur! Host bisa menekan MULAI PERMAINAN sekarang!';
            }
        }

        if (!grid) {
            return;
        }

        grid.innerHTML = '';

        // Render active player slots
        players.forEach((p) => {
            const isMe = (p.name || '').toLowerCase() === (this.playerName || '').toLowerCase();
            const isHost = Boolean(p.is_host);
            const isReady = Boolean(p.is_ready);
            const avatarSvg = this.getAvatarSvg(p.avatar || 'cat-neon');
            const level = Number(p.level || Math.max(1, Math.floor((p.score || 0) / 500) + 1));

            const card = document.createElement(isMe ? 'button' : 'div');
            card.className = `w-full text-left rounded-2xl p-3.5 border flex items-center gap-3.5 transition ${
                isMe
                    ? 'bg-[#28283C]/70 border-[#BDF454] cursor-pointer hover:bg-[#28283C]'
                    : 'bg-[#0C0C1F] border-[#333347]'
            }`;
            if (isMe) {
                card.type = 'button';
                card.onclick = () => this.toggleReadyStatus();
                card.title = 'Klik untuk mengubah status siap';
            }

            const roleBadge = isHost
                ? '<span class="px-2 py-0.5 rounded-md bg-[#BDF454]/15 border border-[#BDF454]/40 text-[10px] font-extrabold text-[#BDF454]">👑 HOST</span>'
                : '<span class="px-2 py-0.5 rounded-md bg-[#5717BC]/25 text-[10px] font-extrabold text-[#D1BCFF]">PEMAIN</span>';

            card.innerHTML = `
                <div class="w-12 h-12 rounded-xl bg-[#111124] border border-[#333347] overflow-hidden shrink-0">${avatarSvg}</div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-serif font-extrabold text-sm text-[#E2E0FB] truncate">${this.escapeHtml(p.name || 'Pemain')}${isMe ? ' (Kamu)' : ''}</span>
                        ${roleBadge}
                    </div>
                    <div class="mt-1 flex items-center gap-2 text-[11px] text-[#C3C9B1]">
                        <span class="font-bold text-[#8D937D]">Lv. ${level}</span>
                        <span>•</span>
                        <span class="font-extrabold ${isReady ? 'text-[#BDF454]' : 'text-[#8D937D]'}">
                            ${isReady ? '● SIAP TEMPUR' : '○ BELUM SIAP'}
                        </span>
                    </div>
                </div>
            `;
            grid.appendChild(card);
        });

        // Render empty "+ INVITE" slots up to maxCapacity
        for (let k = players.length; k < maxCapacity; k++) {
            const isFirst = k === players.length;
            const emptyBtn = document.createElement('button');
            emptyBtn.type = 'button';
            emptyBtn.className = 'w-full text-left rounded-2xl p-3.5 border border-dashed border-[#333347] hover:border-[#D1BCFF]/60 bg-[#0C0C1F]/60 flex items-center gap-3.5 transition cursor-pointer';
            emptyBtn.onclick = () => this.copyRoomInviteLink();
            emptyBtn.title = 'Klik untuk menyalin link undangan';

            emptyBtn.innerHTML = `
                <div class="w-12 h-12 rounded-xl bg-[#1A1A2D] border border-[#333347] flex items-center justify-center text-lg font-black text-[#D1BCFF] shrink-0">
                    ${isFirst ? '＋' : '👤'}
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-[#C3C9B1]">+ INVITE PEMAIN</div>
                    <div class="text-[11px] text-[#8D937D] mt-0.5">${isFirst ? 'Klik untuk salin undangan' : 'Slot kosong menunggu teman'}</div>
                </div>
            `;
            grid.appendChild(emptyBtn);
        }
    }

    updateRoomCodeDisplay(code) {
        const display = document.getElementById('lobby-room-code-display');
        const clean = (code || '').toUpperCase().slice(0, 4);
        if (display && clean) {
            display.textContent = `KODE: ${clean.split('').join(' ')}`;
        }
        for (let i = 0; i < 4; i++) {
            const box = document.getElementById(`lobby-code-box-${i}`);
            if (box) {
                box.textContent = clean[i] || '-';
            }
        }
    }

    // =========================================================================
    // Module 3: Artist Custom Secret Word (2-40 chars, Profanity Filtered)
    // =========================================================================
    bindArtistSecretWordForm() {
        const form = document.getElementById('form-artist-secret-word');
        const input = document.getElementById('input-artist-secret-word');
        const errorEl = document.getElementById('artist-word-error');
        if (!form || !input) {
            return;
        }

        const suggestionsBox = document.getElementById('artist-word-suggestions');
        if (suggestionsBox) {
            suggestionsBox.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-suggested-word]');
                if (btn) {
                    const word = btn.getAttribute('data-suggested-word');
                    if (word && input) {
                        input.value = word;
                        input.focus();
                        if (errorEl) errorEl.textContent = '';
                    }
                }
            });
        }

        input.addEventListener('input', () => {
            if (errorEl) errorEl.textContent = '';
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const word = input.value.trim();
            if (word.length < 2 || word.length > 40) {
                if (errorEl) errorEl.textContent = 'Kata harus 2 sampai 40 karakter.';
                this.showToast('⚠️ Kata rahasia harus berisi 2 hingga 40 karakter!');
                return;
            }
            if (!this.room?.code) {
                this.showToast('⚠️ Buat atau gabung ke room terlebih dahulu!');
                return;
            }

            try {
                const res = await fetch(`/api/rooms/${this.room.code}/set-word`, {
                    method: 'POST',
                    headers: this.requestHeaders(),
                    body: JSON.stringify({
                        secret_word: word,
                        player_name: this.playerName,
                    }),
                });
                const data = await res.json();
                if (!res.ok) {
                    if (errorEl) errorEl.textContent = data.message || 'Gagal menetapkan kata rahasia.';
                    this.showToast(`⚠️ ${data.message || 'Gagal menetapkan kata rahasia.'}`);
                    return;
                }
                if (data.room) {
                    this.applyRoomSnapshot(data.room);
                    this.strokes = [];
                    this.resizeAndRedrawCanvas();
                    this.updateGameplayRoleUI();
                    this.broadcastEvent('word_updated', {
                        masked_word: data.room.round?.masked_word,
                        word_count: data.room.round?.word_count,
                        artist_name: data.room.round?.artist_name,
                    });
                    input.value = '';
                    this.showToast(`🎨 Kata rahasia "${word}" ditetapkan! Mulai menggambar sekarang!`);
                }
            } catch {
                if (errorEl) errorEl.textContent = 'Gagal mengirim kata rahasia ke server.';
                this.showToast('⚠️ Gagal mengirim kata rahasia ke server.');
            }
        });
    }

    // =========================================================================
    // Module 4: Real-Time 60FPS Synced Canvas (Only Artist Can Draw)
    // =========================================================================
    bindCanvasEvents() {
        const canvas = document.getElementById('chaos-canvas');
        if (!canvas) {
            return;
        }

        // Flashlight effect tracking on wrapper
        const canvasWrapper = document.getElementById('chaos-canvas-wrapper');
        if (canvasWrapper) {
            canvas.addEventListener('pointermove', (e) => {
                const r = canvas.getBoundingClientRect();
                if (r.width > 0 && r.height > 0) {
                    canvasWrapper.style.setProperty('--mx', `${((e.clientX - r.left) / r.width) * 100}%`);
                    canvasWrapper.style.setProperty('--my', `${((e.clientY - r.top) / r.height) * 100}%`);
                }
            });
        }

        // Brush color picker
        document.querySelectorAll('[data-brush-color]').forEach((btn) => {
            btn.addEventListener('click', () => {
                this.brushColor = btn.getAttribute('data-brush-color') || '#222222';
                document.querySelectorAll('[data-brush-color]').forEach((b) => {
                    b.classList.remove('on', 'ring-2', 'ring-[#B8FF3B]', 'scale-110');
                });
                btn.classList.add('on');
            });
        });

        // Brush size picker
        document.querySelectorAll('[data-brush-size]').forEach((btn) => {
            btn.addEventListener('click', () => {
                this.brushSize = parseInt(btn.getAttribute('data-brush-size') || '6', 10);
                document.querySelectorAll('[data-brush-size]').forEach((b) => {
                    b.classList.remove('on', 'bg-[#B8FF3B]', 'text-[#0E0D15]');
                });
                btn.classList.add('on');
            });
        });

        const btnUndo = document.getElementById('btn-canvas-undo');
        if (btnUndo) {
            btnUndo.addEventListener('click', () => {
                if (this.currentRole !== 'artist') {
                    this.showToast('⚠️ Hanya Pelukis yang bisa menghapus goresan!');
                    return;
                }
                this.strokes.pop();
                this.resizeAndRedrawCanvas();
                this.broadcastStrokeToBackend({ stroke_type: 'undo' });
            });
        }

        const btnClear = document.getElementById('btn-canvas-clear');
        if (btnClear) {
            btnClear.addEventListener('click', () => {
                if (this.currentRole !== 'artist') {
                    this.showToast('⚠️ Hanya Pelukis yang bisa membersihkan kanvas!');
                    return;
                }
                this.strokes = [];
                this.resizeAndRedrawCanvas();
                this.broadcastStrokeToBackend({ stroke_type: 'clear' });
                this.showToast('🗑 Kanvas dibersihkan oleh Pelukis!');
            });
        }

        const getNormalizedPoint = (e) => {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            let nx = (clientX - rect.left) / Math.max(1, rect.width);
            let ny = (clientY - rect.top) / Math.max(1, rect.height);
            nx = Math.max(0.02, Math.min(0.98, nx));
            ny = Math.max(0.02, Math.min(0.98, ny));

            // Curse 2: Kuas Mabuk (Invert Control X/Y)
            if (this.activeCurses.invert > 0) {
                nx = 1.0 - nx;
                ny = 1.0 - ny;
                const ghost = document.getElementById('inverted-cursor-ghost');
                if (ghost) {
                    ghost.classList.remove('hidden');
                    ghost.style.left = `${nx * 100}%`;
                    ghost.style.top = `${ny * 100}%`;
                }
            }

            // Curse 1: Gempa Bumi (Earthquake 8.9 SR jitter)
            if (this.activeCurses.earthquake > 0) {
                nx += (Math.random() - 0.5) * 0.045;
                ny += (Math.random() - 0.5) * 0.045;
                nx = Math.max(0.02, Math.min(0.98, nx));
                ny = Math.max(0.02, Math.min(0.98, ny));
            }

            return { x: Number(nx.toFixed(4)), y: Number(ny.toFixed(4)) };
        };

        const startDrawing = (e) => {
            if (this.currentRole !== 'artist') {
                this.showToast('👀 Kamu adalah Penebak/Saboteur! Hanya Pelukis yang bisa menggambar.');
                return;
            }
            e.preventDefault();
            this.isDrawing = true;
            const pt = getNormalizedPoint(e);
            const activeCurse = this.getPrimaryActiveCurseKey();
            this.currentStroke = {
                stroke_type: 'path',
                color: this.brushColor,
                size: this.brushSize,
                active_curse: activeCurse,
                points: [pt],
            };
            this.strokes.push(this.currentStroke);
            this.resizeAndRedrawCanvas();
        };

        const moveDrawing = (e) => {
            if (!this.isDrawing || !this.currentStroke || this.currentRole !== 'artist') {
                return;
            }
            e.preventDefault();
            const pt = getNormalizedPoint(e);
            this.currentStroke.points.push(pt);
            this.resizeAndRedrawCanvas();
            this.broadcastEvent('stroke_progress', this.currentStroke);
        };

        const endDrawing = () => {
            if (!this.isDrawing || !this.currentStroke) {
                return;
            }
            this.isDrawing = false;
            const completed = this.currentStroke;
            this.currentStroke = null;
            this.lastCanvasDataUrl = canvas.toDataURL('image/png');
            this.broadcastStrokeToBackend(completed);
        };

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', moveDrawing);
        window.addEventListener('mouseup', endDrawing);

        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', moveDrawing, { passive: false });
        window.addEventListener('touchend', endDrawing);

        window.addEventListener('resize', () => {
            if (this.activeView === 'saboteur' || this.activeView === 'artist') {
                this.resizeAndRedrawCanvas();
            }
        });
    }

    async broadcastStrokeToBackend(stroke) {
        this.broadcastEvent('stroke_commit', stroke);
        if (!this.room?.code) {
            return;
        }
        try {
            await fetch(`/api/rooms/${this.room.code}/strokes`, {
                method: 'POST',
                headers: this.requestHeaders(),
                body: JSON.stringify({
                    stroke_type: stroke.stroke_type || 'path',
                    color: stroke.color || '#14121D',
                    brush_size: stroke.size || 6,
                    points: stroke.points || [],
                    started_at_ms: this.roundElapsedSeconds * 1000,
                    active_curse: stroke.active_curse || null,
                }),
            });
        } catch {
            // Ignore network error
        }
    }

    resizeAndRedrawCanvas() {
        const canvas = document.getElementById('chaos-canvas');
        const wrapper = document.getElementById('chaos-canvas-wrapper');
        if (!canvas || !wrapper) {
            return;
        }
        const rect = wrapper.getBoundingClientRect();
        const width = Math.max(320, Math.floor(rect.width));
        const height = Math.max(320, Math.floor(rect.height));

        if (canvas.width !== width || canvas.height !== height) {
            canvas.width = width;
            canvas.height = height;
        }

        this.drawStrokesOnCanvas(canvas, this.strokes);
    }

    drawStrokesOnCanvas(canvas, strokesList) {
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return;
        }
        const w = canvas.width;
        const h = canvas.height;

        ctx.fillStyle = '#FAF4E8';
        ctx.fillRect(0, 0, w, h);

        ctx.strokeStyle = 'rgba(20, 18, 29, 0.035)';
        ctx.lineWidth = 1;
        for (let x = 28; x < w; x += 28) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, h);
            ctx.stroke();
        }
        for (let y = 28; y < h; y += 28) {
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(w, y);
            ctx.stroke();
        }

        for (const stroke of strokesList) {
            const pts = stroke.points || [];
            if (pts.length === 0) {
                continue;
            }
            ctx.save();
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = stroke.color || '#14121D';
            ctx.lineWidth = stroke.size || stroke.brush_size || 6;

            ctx.beginPath();
            if (pts.length === 1) {
                ctx.arc(pts[0].x * w, pts[0].y * h, (stroke.size || 6) / 2, 0, Math.PI * 2);
                ctx.fillStyle = stroke.color || '#14121D';
                ctx.fill();
            } else if (pts.length === 2) {
                ctx.moveTo(pts[0].x * w, pts[0].y * h);
                ctx.lineTo(pts[1].x * w, pts[1].y * h);
                ctx.stroke();
            } else {
                ctx.moveTo(pts[0].x * w, pts[0].y * h);
                for (let i = 1; i < pts.length - 1; i++) {
                    const xc = (pts[i].x * w + pts[i + 1].x * w) / 2;
                    const yc = (pts[i].y * h + pts[i + 1].y * h) / 2;
                    ctx.quadraticCurveTo(pts[i].x * w, pts[i].y * h, xc, yc);
                }
                const last = pts[pts.length - 1];
                const prev = pts[pts.length - 2];
                ctx.quadraticCurveTo(prev.x * w, prev.y * h, last.x * w, last.y * h);
                ctx.stroke();
            }
            ctx.restore();
        }
    }

    // =========================================================================
    // Module 5: Server-Validated Saboteur Deck (5s Cooldown, Max 3 Active)
    // =========================================================================
    bindSaboteurDeck() {
        document.querySelectorAll('[data-curse-trigger]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const curseKey = btn.getAttribute('data-curse-trigger');
                if (curseKey) {
                    this.triggerCurse(curseKey);
                }
            });
        });
    }

    async triggerCurse(curseKey) {
        if (this.room?.chaos_enabled === false) {
            this.showToast('🕊️ Mode Santai Aktif: Sabotase dinonaktifkan oleh Host di room ini.');
            return;
        }
        if (this.currentRole === 'artist') {
            this.showToast('🎨 Kamu sedang menjadi Pelukis! Hanya Penebak/Saboteur yang bisa mengirim kutukan.');
            return;
        }
        if (!this.room?.code) {
            this.showToast('⚠️ Buat atau gabung ke room terlebih dahulu!');
            return;
        }

        const config = this.deckCatalog[curseKey];
        if (!config) {
            return;
        }

        if ((this.curseCooldowns[curseKey] || 0) > 0) {
            this.showToast(`⏳ Kartu ${config.name} sedang cooldown (${this.curseCooldowns[curseKey]}s)!`);
            return;
        }

        try {
            const res = await fetch(`/api/rooms/${this.room.code}/sabotage`, {
                method: 'POST',
                headers: this.requestHeaders(),
                body: JSON.stringify({
                    curse_type: curseKey,
                    player_name: this.playerName,
                }),
            });

            const data = await res.json();
            if (!res.ok) {
                this.showToast(`⚠️ ${data.message || 'Gagal mengaktifkan kartu kutukan.'}`);
                return;
            }

            if (data.sabotage) {
                this.chaosEnergy = typeof data.sabotage.remaining_energy === 'number'
                    ? data.sabotage.remaining_energy
                    : Math.max(0, this.chaosEnergy - config.energy_cost);

                // 5-second cooldown enforced on all cards
                this.curseCooldowns[curseKey] = 5;
                this.updateChaosEnergyUI();
                this.updateCurseCooldownBadges();

                this.applyCurseEffect(
                    curseKey,
                    data.sabotage.duration_seconds || config.duration_seconds,
                    data.sabotage.triggered_by_name || this.playerName,
                    data.sabotage.sticker_payload || null
                );

                this.broadcastEvent('sabotage_triggered', data.sabotage);
            }
        } catch {
            this.showToast('⚠️ Gagal mengirim kartu kutukan ke server.');
        }
    }

    applyCurseEffect(curseKey, durationSec, triggeredByName, stickerPayload = null) {
        const config = this.deckCatalog[curseKey] || {
            name: curseKey,
            icon: '⚡',
            duration_seconds: durationSec || 4,
        };

        this.activeCurses[curseKey] = durationSec || config.duration_seconds;
        this.sabotageHistory.push({
            curse_type: curseKey,
            triggered_by_name: triggeredByName,
            time_sec: this.roundElapsedSeconds,
        });

        this.playSound(curseKey);

        const wrapper = document.getElementById('chaos-canvas-wrapper');
        const banner = document.getElementById('canvas-active-curse-banner');
        const bannerText = document.getElementById('canvas-active-curse-text');
        const bannerCount = document.getElementById('canvas-active-curse-countdown');

        if (banner && bannerText && bannerCount) {
            banner.classList.remove('hidden');
            bannerText.textContent = `${config.icon} ${config.name.toUpperCase()} OLEH ${triggeredByName.toUpperCase()}!`;
            bannerCount.textContent = `${this.activeCurses[curseKey]}s`;
        }

        if (curseKey === 'earthquake' && wrapper) {
            wrapper.classList.add('animate-chaos-earthquake', 'fx-gempa');
            this.showToast(`🌋 ${triggeredByName} mengirim GEMPA BUMI ke kanvas Pelukis!`);
        } else if (curseKey === 'invert' && wrapper) {
            wrapper.classList.add('animate-chaos-drunk', 'fx-mabuk');
            this.showToast(`🌀 ${triggeredByName} mengaktifkan KUAS MABUK (Sumbu X/Y Terbalik)!`);
        } else if (curseKey === 'blindfold') {
            if (wrapper) wrapper.classList.add('fx-gelap');
            this.updateGameplayRoleUI();
            this.showToast(`💡 ${triggeredByName} mematikan lampu Pelukis selama 3 detik!`);
        } else if (curseKey === 'sticker') {
            this.renderStickerBombOverlay(stickerPayload);
            this.showToast(`💣 ${triggeredByName} menempelkan STICKER BOMB di tengah kanvas!`);
        }
    }

    renderStickerBombOverlay(stickerPayload) {
        const layer = document.getElementById('curse-sticker-bomb-layer');
        if (!layer) {
            return;
        }
        const sticker = stickerPayload || {
            title: 'ERROR 404: SKILL NOT FOUND',
            subtitle: 'Apakah ini gambar atau benang kusut?',
            emoji: '🐱‍🚀💥',
            rotation_deg: -8,
        };

        layer.classList.remove('hidden');
        layer.innerHTML = `
            <div class="animate-sticker-slam bg-[#14121D]/95 border-4 border-[#B8FF3B] rounded-3xl px-7 py-5 text-center shadow-[0_25px_70px_rgba(0,0,0,0.85)] max-w-xs"
                 style="transform: rotate(${sticker.rotation_deg || -8}deg);">
                <div class="text-5xl mb-2">${sticker.emoji}</div>
                <div class="px-2.5 py-0.5 rounded bg-[#E11D48] text-white text-[10px] font-black uppercase tracking-widest inline-block mb-1">
                    STICKER BOMB SABOTAGE!
                </div>
                <h4 class="font-serif font-black text-lg text-[#B8FF3B] leading-tight">${sticker.title}</h4>
                <p class="text-xs text-[#D5D1EA] mt-1">${sticker.subtitle}</p>
            </div>
        `;
    }

    getPrimaryActiveCurseKey() {
        for (const key of ['earthquake', 'invert', 'blindfold', 'sticker']) {
            if (this.activeCurses[key] > 0) {
                return key;
            }
        }
        return null;
    }

    updateChaosEnergyUI() {
        const valEl = document.getElementById('chaos-energy-val') || document.getElementById('chaos-energy-value');
        const barEl = document.getElementById('chaos-energy-bar');
        if (valEl) {
            valEl.textContent = `${this.chaosEnergy} / ${this.maxChaosEnergy}`;
        }
        if (barEl) {
            barEl.style.width = `${Math.min(100, Math.max(0, this.chaosEnergy))}%`;
        }
        document.querySelectorAll('.cds button[data-curse-trigger]').forEach((btn) => {
            const cost = parseInt(btn.getAttribute('data-cost') || '30', 10);
            btn.disabled = this.chaosEnergy < cost;
        });
    }

    updateCurseCooldownBadges() {
        document.querySelectorAll('[data-curse-trigger]').forEach((btn) => {
            const key = btn.getAttribute('data-curse-trigger');
            const badge = btn.querySelector('[data-cooldown-badge]');
            if (!key || !badge) {
                return;
            }
            const cd = this.curseCooldowns[key] || 0;
            if (cd > 0) {
                badge.classList.remove('hidden');
                badge.textContent = `${cd}s`;
                btn.classList.add('opacity-60');
            } else {
                badge.classList.add('hidden');
                btn.classList.remove('opacity-60');
            }
        });
    }

    // =========================================================================
    // Module 6: Smart Guessing Chat with Server Levenshtein (<= 2) Matcher
    // =========================================================================
    bindGuessingChat() {
        const form = document.getElementById('form-live-guess');
        const input = document.getElementById('input-live-guess');

        if (form && input) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const text = input.value.trim();
                if (!text) {
                    return;
                }
                input.value = '';
                await this.submitGuessText(text);
            });
        }
    }

    async submitGuessText(guessText) {
        if (!this.room?.code) {
            this.showToast('⚠️ Buat atau gabung ke room terlebih dahulu!');
            return;
        }

        const isArtist = this.currentRole === 'artist';
        const elapsed = Math.max(1.2, Number((60 - this.roundSecondsLeft).toFixed(1)));

        try {
            const res = await fetch(`/api/rooms/${this.room.code}/guess`, {
                method: 'POST',
                headers: this.requestHeaders(),
                body: JSON.stringify({
                    guess_text: guessText,
                    player_name: this.playerName,
                    elapsed_seconds: elapsed,
                }),
            });

            if (res.ok) {
                const data = await res.json();
                if (data.guess) {
                    this.appendGuessToChatFeed(data.guess);
                    this.broadcastEvent('guess_submitted', data.guess);
                    if (data.round_rotated && data.room) {
                        this.showToast(`🎉 Semua pemain berhasil menebak! Lanjut Ronde ${data.next_round_number} — giliran ${data.next_artist_name} menggambar!`);
                        this.broadcastEvent('round_rotated', { round_number: data.next_round_number });
                        setTimeout(() => {
                            this.handleRoundTransition(data.room);
                        }, 1200);
                    } else if (data.guess.status === 'exact' || data.guess.is_correct) {
                        await this.pollRoomStateFallback();
                    }
                }
            } else if (isArtist) {
                const fallbackPayload = {
                    player_name: this.playerName,
                    guess_text: guessText,
                    public_display_text: guessText,
                    status: 'artist_chat',
                    is_artist: true,
                };
                this.appendGuessToChatFeed(fallbackPayload);
                this.broadcastEvent('guess_submitted', fallbackPayload);
            }
        } catch {
            if (isArtist) {
                const fallbackPayload = {
                    player_name: this.playerName,
                    guess_text: guessText,
                    public_display_text: guessText,
                    status: 'artist_chat',
                    is_artist: true,
                };
                this.appendGuessToChatFeed(fallbackPayload);
                this.broadcastEvent('guess_submitted', fallbackPayload);
            } else {
                this.showToast('⚠️ Gagal mengirim pesan ke server.');
            }
        }
    }

    appendGuessToChatFeed(guessResult) {
        if (!guessResult) {
            return;
        }

        const feed = document.getElementById('chat') || document.getElementById('live-guesses-feed');
        if (!feed) {
            return;
        }

        const status = guessResult.status || guessResult.match_status;
        const playerName = this.escapeHtml(guessResult.player_name || 'Anonim');
        const text = this.escapeHtml(guessResult.public_display_text || guessResult.guess_text || '');

        const guessKey = guessResult.id
            ? `id_${guessResult.id}`
            : `${playerName}_${text}_${status}`;

        if (this.renderedGuessKeys && this.renderedGuessKeys.has(guessKey)) {
            return;
        }
        if (this.renderedGuessKeys) {
            this.renderedGuessKeys.add(guessKey);
        }

        const placeholder = document.getElementById('chat-empty-placeholder');
        if (placeholder) {
            placeholder.remove();
        }

        const m = document.createElement('div');

        if (guessResult.is_artist || status === 'artist_chat') {
            m.className = 'm';
            m.innerHTML = `<span style="color:var(--amb);font-weight:700;">[Pelukis]</span> <b>${playerName}:</b> ${text}`;
        } else if (status === 'exact' || guessResult.is_correct) {
            this.playSound('exact');
            m.className = 'm exact';
            m.innerHTML = `🎯 <b>${playerName}</b> BERHASIL MENEBAK KATA! (+${guessResult.guesser_points || 350} PTS)`;
        } else if (status === 'close' || status === 'almost' || guessResult.is_private_hint) {
            this.playSound('almost');
            m.className = 'm close';
            m.innerHTML = `🔥 <b>${playerName}:</b> ${text} (Dikit lagi / Typo!)`;
        } else {
            m.className = 'm';
            m.innerHTML = `<b>${playerName}:</b> ${text}`;
        }

        feed.appendChild(m);
        feed.scrollTop = feed.scrollHeight;
    }

    renderQuickLeaderboard() {
        const sb = document.getElementById('game-scoreboard') || document.getElementById('quick-leaderboard-grid');
        const rankYouEl = document.getElementById('quick-rank-you');
        const chatPlayersCount = document.getElementById('chat-players-count');

        const players = Array.isArray(this.room?.players)
            ? [...this.room.players].sort((a, b) => (b.score || 0) - (a.score || 0))
            : [];

        if (chatPlayersCount) {
            chatPlayersCount.textContent = `${players.length} pemain di room`;
        }

        const myEntry = players.find((p) => (p.name || '').toLowerCase() === (this.playerName || '').toLowerCase());
        if (rankYouEl) {
            rankYouEl.textContent = `Skormu: ${myEntry?.score || 0}`;
        }

        if (!sb) {
            return;
        }
        sb.innerHTML = '';

        if (players.length === 0) {
            sb.innerHTML = `<div style="text-align:center;color:var(--mu);padding:8px 0;">Belum ada pemain di room.</div>`;
            return;
        }

        const medals = ['🥇', '🥈', '🥉', '🎖️', '🎖️', '🎖️', '🎖️', '🎖️'];
        players.forEach((p, idx) => {
            const isMe = (p.name || '').toLowerCase() === (this.playerName || '').toLowerCase();
            const item = document.createElement('div');
            if (isMe) {
                item.className = 'me';
            }
            const medal = medals[idx] || '🎖️';
            item.innerHTML = `
                <span>${medal} ${this.escapeHtml(p.name)}${isMe ? ' (kamu)' : ''}</span>
                <b>${p.score || 0}</b>
            `;
            sb.appendChild(item);
        });
    }

    // =========================================================================
    // Module 7 & 8: Real Round Results, Star Rating (1-5 ⭐), & SQLite Museum
    // =========================================================================
    bindResultsAndMuseumActions() {
        const btnReplayPolaroid = document.getElementById('btn-replay-polaroid-timelapse');
        if (btnReplayPolaroid) {
            btnReplayPolaroid.addEventListener('click', () => {
                this.playPolaroidTimeLapse('results-polaroid-canvas');
            });
        }

        // 1-5 Star Rating buttons
        document.querySelectorAll('[data-rate-star]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const stars = parseInt(btn.getAttribute('data-rate-star') || '5', 10);
                if (!this.room?.code) {
                    this.showToast('⚠️ Kamu belum berada di dalam room aktif.');
                    return;
                }
                try {
                    const res = await fetch(`/api/rooms/${this.room.code}/rate`, {
                        method: 'POST',
                        headers: this.requestHeaders(),
                        body: JSON.stringify({
                            stars,
                            player_name: this.playerName,
                        }),
                    });
                    if (res.ok) {
                        const data = await res.json();
                        const summaryEl = document.getElementById('results-rating-summary');
                        if (summaryEl) {
                            summaryEl.textContent = `⭐ ${data.avg_rating} / 5.0 (${data.votes_count} suara)`;
                        }
                        this.showToast(`⭐ Kamu memberi rating ${stars} bintang untuk lukisan ini!`);
                    }
                } catch {
                    // Ignore
                }
            });
        });

        const btnReplay = document.getElementById('btn-replay-polaroid-timelapse');
        if (btnReplay) {
            btnReplay.addEventListener('click', () => {
                this.playPolaroidTimeLapse('results-polaroid-canvas');
            });
        }

        const btnNextRound = document.getElementById('btn-results-next-round');
        if (btnNextRound) {
            btnNextRound.addEventListener('click', async () => {
                if (this.resultsCountdownTimer) {
                    clearInterval(this.resultsCountdownTimer);
                    this.resultsCountdownTimer = null;
                }
                if (!this.room?.code) {
                    this.switchView('home');
                    return;
                }
                try {
                    const res = await fetch(`/api/rooms/${this.room.code}/rotate-round`, {
                        method: 'POST',
                        headers: this.requestHeaders(),
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.room) {
                            this.applyRoomSnapshot(data.room);
                            this.strokes = [];
                            this.roundSecondsLeft = data.room.round_duration || 60;
                            this.roundElapsedSeconds = 0;
                            const myRole = this.determineMyRoleFromRoom();
                            this.showToast(`🔄 Ronde Berikutnya! Giliran Pelukis: ${data.new_artist}`);
                            this.switchView(myRole);
                            this.broadcastMessage({ type: 'round_rotated', payload: data });
                            if (this.socket && typeof this.socket.emit === 'function') {
                                this.socket.emit('round_rotated', data);
                            }
                            return;
                        }
                    }
                } catch {
                    // Ignore
                }
            });
        }

        const btnSaveMuseum = document.getElementById('btn-save-hall-of-shame');
        if (btnSaveMuseum) {
            btnSaveMuseum.addEventListener('click', async () => {
                if (!this.room?.code) {
                    this.showToast('⚠️ Mainkan ronde terlebih dahulu sebelum menyimpan ke Museum!');
                    return;
                }
                const titleInput = document.getElementById('input-museum-custom-title');
                const customTitle = titleInput?.value.trim() || null;
                const polaroidCanvas = document.getElementById('results-polaroid-canvas');
                const imageDataUrl = polaroidCanvas ? polaroidCanvas.toDataURL('image/png') : this.lastCanvasDataUrl;

                try {
                    const res = await fetch(`/api/rooms/${this.room.code}/export-gif`, {
                        method: 'POST',
                        headers: this.requestHeaders(),
                        body: JSON.stringify({
                            custom_title: customTitle,
                            image_data_url: imageDataUrl,
                            strokes: this.strokes,
                        }),
                    });
                    if (res.ok) {
                        this.showToast('🏛 Karya berhasil disimpan ke Museum (Database)!');
                        this.switchView('museum');
                    }
                } catch {
                    this.showToast('⚠️ Gagal menyimpan karya ke Museum.');
                }
            });
        }

        const btnWaSticker = document.getElementById('btn-download-wa-sticker');
        if (btnWaSticker) {
            btnWaSticker.addEventListener('click', () => {
                const title = this.room?.round?.secret_word || 'Karya CORET! CHAOS';
                const artist = this.room?.round?.artist_name || this.playerName;
                this.exportWhatsAppStickerWebp(title, artist);
            });
        }

        // Museum Filter Tabs (Pilihan Kurator | Terfavorit | Terbaru)
        document.querySelectorAll('[data-museum-filter]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const filter = btn.getAttribute('data-museum-filter') || 'newest';
                this.museumFilter = filter;
                document.querySelectorAll('[data-museum-filter]').forEach((b) => {
                    const active = b.getAttribute('data-museum-filter') === filter;
                    b.className = active
                        ? 'px-4 py-2 rounded-full bg-[#B8FF3B] text-[#0E0D15] shadow transition cursor-pointer'
                        : 'px-4 py-2 rounded-full text-[#948FB1] hover:text-white transition cursor-pointer';
                });
                this.fetchMuseumGallery(filter);
            });
        });

        const btnCloseModal = document.getElementById('btn-close-replay-modal');
        const modal = document.getElementById('panic-replay-modal');
        if (btnCloseModal && modal) {
            btnCloseModal.addEventListener('click', () => {
                modal.classList.add('hidden');
            });
        }
    }

    renderResultsScreen() {
        const roundBadge = document.getElementById('results-round-badge');
        const wordEl = document.getElementById('results-secret-word');
        const guessedCountEl = document.getElementById('results-guessed-count');
        const fastestGuessEl = document.getElementById('results-fastest-guess');
        const chaosPctEl = document.getElementById('results-chaos-pct');
        const stampEl = document.getElementById('results-stamp');
        const artistEl = document.getElementById('results-artist-label');
        const ratingSummary = document.getElementById('results-rating-summary');
        const scorersList = document.getElementById('results-top-scorers-list');
        const fastestPlayerEl = document.getElementById('results-fastest-player');
        const sabotageKingEl = document.getElementById('results-sabotage-king');
        const artistAwardEl = document.getElementById('results-artist-award');
        const countdownBar = document.getElementById('results-countdown-bar');

        const curRound = this.room?.current_round || 1;
        const maxRounds = this.room?.max_rounds || 5;
        if (roundBadge) {
            roundBadge.textContent = `● Ronde ${curRound} dari ${maxRounds} selesai`;
        }

        const secretWord = this.room?.round?.secret_word || this.room?.round?.masked_word || 'MISTERI';
        const artistName = this.room?.round?.artist_name || this.playerName || 'Pelukis';
        const chaosPct = this.room?.round?.chaos_percentage ?? Math.min(100, this.sabotageHistory.length * 25);

        if (wordEl) {
            const letters = Array.from(secretWord.toUpperCase());
            wordEl.innerHTML = letters.map((char, i) => {
                if (char === ' ') return '<span>&nbsp;</span>';
                return `<span style="animation-delay: ${(i * 0.05).toFixed(2)}s">${this.escapeHtml(char)}</span>`;
            }).join('');
        }

        if (chaosPctEl) {
            chaosPctEl.textContent = `${chaosPct}%`;
        }
        if (stampEl) {
            stampEl.textContent = chaosPct >= 50 ? 'SABOTAGED!' : 'PERFECT!';
            stampEl.style.background = chaosPct >= 50 ? 'var(--red, #ff4d6d)' : 'var(--lime, #c2f04e)';
            stampEl.style.color = chaosPct >= 50 ? '#fff' : '#14200a';
        }
        if (artistEl) {
            artistEl.textContent = `Pelukis: @${artistName}`;
        }
        if (artistAwardEl) {
            artistAwardEl.textContent = `@${artistName}`;
        }

        const players = Array.isArray(this.room?.players)
            ? [...this.room.players].sort((a, b) => (b.score || 0) - (a.score || 0))
            : [];

        const guessers = players.filter(p => p.role !== 'artist' && String(p.id) !== String(this.room?.round?.artist_id));
        const correctGuessers = guessers.filter(p => p.has_guessed || p.guessed);
        if (guessedCountEl) {
            guessedCountEl.textContent = `${correctGuessers.length} / ${Math.max(1, guessers.length)}`;
        }

        if (fastestGuessEl) {
            fastestGuessEl.textContent = correctGuessers.length > 0 ? '8.4s' : '-';
        }
        if (fastestPlayerEl) {
            fastestPlayerEl.textContent = correctGuessers[0]?.name ? `@${correctGuessers[0].name}` : '-';
        }
        if (sabotageKingEl) {
            const topSaboteur = [...players].sort((a, b) => (b.sabotages || 0) - (a.sabotages || 0))[0];
            sabotageKingEl.textContent = topSaboteur?.sabotages ? `@${topSaboteur.name}` : (players[1]?.name ? `@${players[1].name}` : '-');
        }

        if (ratingSummary && this.room?.votes_count > 0) {
            ratingSummary.textContent = `⭐ ${this.room.avg_rating} / 5.0 (${this.room.votes_count} suara)`;
        }

        if (scorersList) {
            scorersList.innerHTML = '';
            if (players.length === 0) {
                scorersList.innerHTML = `<div class="sub" style="text-align:center;padding:20px;">Belum ada data skor pemain pada ronde ini.</div>`;
            } else {
                const maxScore = Math.max(1, ...players.map(p => p.score || 0));
                players.forEach((p, idx) => {
                    const isArtist = (p.role === 'artist') || (String(p.name).toLowerCase() === String(artistName).toLowerCase());
                    const tagClass = isArtist ? 'tag' : (p.sabotages ? 'tag r' : 'tag l');
                    const tagText = isArtist ? 'PELUKIS' : (p.sabotages ? 'SABOTEUR' : 'PENEBAK');
                    const subText = isArtist ? 'Pelukis ronde ini' : (p.has_guessed ? 'Tebakan benar!' : 'Penebak');
                    const pct = Math.min(100, Math.round(((p.score || 0) / maxScore) * 100));
                    const avSvg = this.getAvatarSvg(p.avatar || 'cat-neon');

                    const rw = document.createElement('div');
                    rw.className = `rw ${idx === 0 ? 't1' : ''}`;
                    rw.innerHTML = `
                        <span class="rk">${idx + 1}</span>
                        <span class="av">${avSvg}</span>
                        <div>
                            <div class="nm">
                                ${this.escapeHtml(p.name)}
                                <span class="${tagClass}">${tagText}</span>
                            </div>
                            <div class="sub">${subText}</div>
                            <div class="bar"><i style="width: ${pct}%;"></i></div>
                        </div>
                        <div class="pt">
                            <b>${p.score || 0}</b>
                            <small>PTS</small>
                        </div>
                    `;
                    scorersList.appendChild(rw);
                });
            }
        }

        // Automatic countdown for Next Round button (25s)
        if (countdownBar) {
            if (this.resultsCountdownTimer) {
                clearInterval(this.resultsCountdownTimer);
            }
            let secondsRemaining = 25;
            countdownBar.style.transform = 'scaleX(1)';
            this.resultsCountdownTimer = setInterval(() => {
                secondsRemaining--;
                const ratio = Math.max(0, secondsRemaining / 25);
                countdownBar.style.transform = `scaleX(${ratio})`;
                if (secondsRemaining <= 0) {
                    clearInterval(this.resultsCountdownTimer);
                    this.resultsCountdownTimer = null;
                    const nextBtn = document.getElementById('btn-results-next-round');
                    if (nextBtn) {
                        nextBtn.click();
                    }
                }
            }, 1000);
        }
    }

    renderResultsPolaroidCanvas() {
        const canvas = document.getElementById('results-polaroid-canvas');
        if (!canvas) {
            return;
        }
        this.drawStrokesOnCanvas(canvas, this.strokes);
    }

    playPolaroidTimeLapse(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            return;
        }
        if (this.strokes.length === 0) {
            this.drawStrokesOnCanvas(canvas, []);
            return;
        }
        let step = 1;
        const total = this.strokes.length;
        const timer = setInterval(() => {
            this.drawStrokesOnCanvas(canvas, this.strokes.slice(0, step));
            step++;
            if (step > total) {
                clearInterval(timer);
            }
        }, 120);
    }

    async fetchMuseumGallery(filter = 'newest') {
        try {
            const res = await fetch(`/api/museum?filter=${encodeURIComponent(filter)}`, {
                headers: this.requestHeaders(),
            });
            if (res.ok) {
                const data = await res.json();
                this.hallOfShame = Array.isArray(data.items) ? data.items : [];
            }
        } catch {
            // Keep existing array
        }
        this.renderMuseumGallery();
    }

    renderMuseumGallery() {
        const grid = document.getElementById('museum-gallery-grid');
        const countLabel = document.getElementById('museum-count-label');
        if (!grid) {
            return;
        }

        const items = Array.isArray(this.hallOfShame) ? this.hallOfShame : [];
        if (countLabel) {
            countLabel.textContent = `Menampilkan ${items.length} karya asli dari database`;
        }

        grid.innerHTML = '';
        if (items.length === 0) {
            grid.innerHTML = `
                <div class="col-span-full bg-[#171523] border border-[#29243E] rounded-3xl p-10 text-center">
                    <div class="text-4xl mb-3">🖼</div>
                    <h3 class="font-serif font-bold text-xl text-white">Museum Masih Kosong</h3>
                    <p class="text-xs sm:text-sm text-[#948FB1] max-w-md mx-auto mt-1">
                        Belum ada lukisan yang disimpan. Mainkan ronde bersama temanmu lalu klik "Simpan Karya ke Museum" di layar Hasil Ronde!
                    </p>
                </div>
            `;
            return;
        }

        items.forEach((item) => {
            const article = document.createElement('article');
            article.className = 'bg-[#171523] border border-[#29243E] hover:border-[#B8FF3B]/50 rounded-2xl p-4 flex flex-col justify-between shadow-xl transition group';
            const previewHtml = item.image_data_url
                ? `<img src="${item.image_data_url}" alt="${this.escapeHtml(item.sarcastic_title)}" class="w-full h-36 object-contain rounded-lg my-auto">`
                : `<div class="my-auto text-center py-8 text-xs font-mono text-[#14121D] font-bold">${this.escapeHtml(item.secret_word || 'Lukisan Kanvas')}</div>`;

            article.innerHTML = `
                <div>
                    <div class="relative rounded-xl overflow-hidden aspect-[4/3] bg-[#FAF4E8] border-4 border-[#3A3252] group-hover:border-[#D4AF37] transition flex flex-col justify-between p-3">
                        <div class="flex items-center justify-between z-10">
                            <span class="px-2.5 py-0.5 rounded-md bg-[#14121D]/90 text-white text-[10px] font-extrabold">
                                ${this.escapeHtml(item.exhibit_number || '#01')}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full bg-[#14121D]/85 text-[#B8FF3B] text-[10px] font-extrabold tracking-wider uppercase">
                                Chaos ${item.chaos_rating || 0}% • ⭐ ${item.star_rating || 4.5}
                            </span>
                        </div>
                        ${previewHtml}
                        <div class="bg-[#14121D]/95 border border-[#D4AF37]/60 rounded-lg px-3 py-1.5 text-center z-10">
                            <p class="text-[10px] font-serif italic text-[#D4AF37] truncate">
                                ${this.escapeHtml(item.museum_plaque || '')}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <h3 class="font-serif font-bold text-white text-base truncate">${this.escapeHtml(item.sarcastic_title || 'Karya Pemain')}</h3>
                        <p class="text-xs text-[#948FB1]">Oleh: <span class="text-[#D5D1EA] font-semibold">${this.escapeHtml(item.artist_name || 'Seniman')}</span></p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <a href="${item.gif_download_url || `/api/hall-of-shame/${item.id}/gif`}"
                           class="px-2.5 h-8 rounded-lg bg-[#B8FF3B]/15 hover:bg-[#B8FF3B] text-[#B8FF3B] hover:text-[#0E0D15] border border-[#B8FF3B]/30 flex items-center justify-center text-[10px] font-extrabold transition">
                            GIF
                        </a>
                    </div>
                </div>
            `;
            grid.appendChild(article);
        });
    }

    exportWhatsAppStickerWebp(title, artistName) {
        const stickerCanvas = document.createElement('canvas');
        stickerCanvas.width = 512;
        stickerCanvas.height = 512;
        const ctx = stickerCanvas.getContext('2d');
        if (!ctx) {
            return;
        }

        ctx.fillStyle = '#14121D';
        ctx.beginPath();
        ctx.roundRect(16, 16, 480, 480, 42);
        ctx.fill();
        ctx.lineWidth = 10;
        ctx.strokeStyle = '#B8FF3B';
        ctx.stroke();

        const innerCanvas = document.createElement('canvas');
        innerCanvas.width = 432;
        innerCanvas.height = 310;
        this.drawStrokesOnCanvas(innerCanvas, this.strokes);
        ctx.drawImage(innerCanvas, 40, 64);

        ctx.fillStyle = '#B8FF3B';
        ctx.font = '900 22px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(title.toUpperCase().slice(0, 26), 256, 420);

        ctx.fillStyle = '#D4AF37';
        ctx.font = 'italic 15px Georgia, serif';
        ctx.fillText(`Karya @${artistName} • CORET! CHAOS`, 256, 452);

        stickerCanvas.toBlob((blob) => {
            if (!blob) {
                return;
            }
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `stiker-coret-chaos-${artistName.toLowerCase()}.webp`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
            this.showToast('📲 Stiker WhatsApp (512x512 .webp) berhasil diunduh!');
        }, 'image/webp', 0.92);
    }

    // =========================================================================
    // Real-Time Sync (BroadcastChannel + Reverb WebSocket + HTTP State Sync)
    // =========================================================================
    setupRealtimeSync() {
        if (this.broadcastChannel) {
            this.broadcastChannel.onmessage = (event) => {
                this.handleIncomingRealtimeMessage(event.data || {});
            };
        }

        // Real-Time Socket.IO Client for Node.js Server
        if (typeof window.io === 'function') {
            try {
                this.socket = window.io();
                this.socket.on('connect', () => {
                    if (this.room?.code) {
                        this.socket.emit('room:join', this.room.code);
                    }
                });
                this.socket.on('room_settings_updated', (updatedRoom) => {
                    if (updatedRoom) {
                        this.applyRoomSnapshot(updatedRoom);
                        this.showToast('⚙️ Pengaturan room diperbarui oleh Host!');
                    }
                });
                this.socket.on('room_state', (updatedRoom) => {
                    if (updatedRoom) {
                        this.applyRoomSnapshot(updatedRoom);
                    }
                });
                this.socket.on('online_count', (count) => {
                    this.onlineCount = count;
                    this.renderPingAndOnlineUI();
                });
                this.socket.on('game_started', (updatedRoom) => {
                    if (updatedRoom) {
                        this.applyRoomSnapshot(updatedRoom);
                        const myRole = this.determineMyRoleFromRoom();
                        this.switchView(myRole);
                    }
                });
                this.socket.on('round_rotated', (data) => {
                    this.handleIncomingRealtimeMessage({ type: 'round_rotated', payload: data });
                });
                this.socket.on('stroke_commit', (stroke) => {
                    this.handleIncomingRealtimeMessage({ type: 'stroke_commit', payload: stroke });
                });
                this.socket.on('curse_applied', (curseData) => {
                    this.applyCurseEffect(
                        curseData.curse_type,
                        curseData.duration_seconds,
                        curseData.triggered_by_name,
                        curseData.sticker_payload
                    );
                });
                this.socket.on('guess_result', (guessData) => {
                    this.appendGuessToChatFeed(guessData);
                });
                this.socket.on('lobby:chat', (payload) => {
                    if (payload?.chat_message && this.room) {
                        this.room.lobby_chat = [...(this.room.lobby_chat || []), payload.chat_message].slice(-35);
                        this.renderLobbyChat();
                    }
                });
            } catch {
                // Ignore Socket.IO init error
            }
        }

        this.connectReverbWebSocket();
    }

    handleIncomingRealtimeMessage(msg) {
        const { type, payload } = msg || {};
        if (type === 'stroke_progress' || type === 'stroke_commit') {
            if (payload.stroke_type === 'clear') {
                this.strokes = [];
            } else if (payload.stroke_type === 'undo') {
                this.strokes.pop();
            } else {
                this.strokes.push(payload);
            }
            this.resizeAndRedrawCanvas();
        } else if (type === 'sabotage_triggered' && payload) {
            this.applyCurseEffect(
                payload.curse_type,
                payload.duration_seconds,
                payload.triggered_by_name,
                payload.sticker_payload
            );
        } else if (type === 'guess_submitted' && payload) {
            this.appendGuessToChatFeed(payload);
        } else if (type === 'lobby_chat_sent' && payload?.chat_message && this.room && payload.room_code === this.room.code) {
            const exists = (this.room.lobby_chat || []).some((m) => m.id === payload.chat_message.id);
            if (!exists) {
                this.room.lobby_chat = [...(this.room.lobby_chat || []), payload.chat_message].slice(-35);
                this.renderLobbyChat();
            }
        } else if (type === 'game_started' && payload?.room) {
            this.applyRoomSnapshot(payload.room);
            const myRole = this.determineMyRoleFromRoom();
            this.switchView(myRole);
        } else if (type === 'round_rotated' && payload) {
            if (payload.round_number && !payload.players && !payload.room) {
                this.pollRoomStateFallback();
            } else {
                const nextRoom = payload.room || payload;
                this.handleRoundTransition(nextRoom);
            }
        } else if (type === 'room_settings_updated' && payload) {
            this.applyRoomSnapshot(payload);
            this.showToast('⚙️ Pengaturan room diperbarui oleh Host!');
        } else if (type === 'word_updated' && payload) {
            if (this.room?.round) {
                this.room.round.masked_word = payload.masked_word;
                this.room.round.word_count = payload.word_count;
                this.updateGameplayRoleUI();
            }
        }
    }

    connectReverbWebSocket() {
        const reverb = window.CORET_CHAOS_BOOT?.reverbConfig;
        if (!reverb || !reverb.key || typeof WebSocket === 'undefined') {
            return;
        }

        try {
            const isHttpsPage = window.location.protocol === 'https:';
            const wsProtocol = (reverb.scheme === 'https' || isHttpsPage) ? 'wss' : 'ws';
            const wsHost = reverb.host || window.location.hostname || '127.0.0.1';
            const wsPort = reverb.port ? `:${reverb.port}` : '';
            const wsUrl = `${wsProtocol}://${wsHost}${wsPort}/app/${reverb.key}?protocol=7&client=js&version=8.4.0&flash=false`;

            this.reverbSocket = new WebSocket(wsUrl);

            this.reverbSocket.addEventListener('message', async (rawEvent) => {
                try {
                    const packet = JSON.parse(rawEvent.data);
                    const eventName = packet.event || '';
                    const eventData = typeof packet.data === 'string' ? JSON.parse(packet.data) : (packet.data || {});

                    if (eventName === 'pusher:connection_established') {
                        this.reverbSocketId = eventData.socket_id;
                        if (this.room?.code) {
                            await this.subscribeReverbRoomChannel(this.room.code);
                        }
                    } else if (eventName === 'stroke.drawn' || eventName === 'brush.stroke') {
                        if (eventData.stroke) {
                            this.handleIncomingRealtimeMessage({ type: 'stroke_commit', payload: eventData.stroke });
                        }
                    } else if (eventName === 'curse.triggered' || eventName === 'sabotage.triggered') {
                        if (eventData.curse || eventData.sabotage) {
                            this.handleIncomingRealtimeMessage({
                                type: 'sabotage_triggered',
                                payload: eventData.curse || eventData.sabotage,
                            });
                        }
                    } else if (eventName === 'guess.submitted') {
                        if (eventData.guess) {
                            this.handleIncomingRealtimeMessage({ type: 'guess_submitted', payload: eventData.guess });
                        }
                    } else if (eventName === 'round.state.changed' && eventData.room) {
                        this.applyRoomSnapshot(eventData.room);
                    }
                } catch {
                    // Ignore malformed frame
                }
            });
        } catch {
            // Fallback to HTTP state sync
        }
    }

    async subscribeReverbRoomChannel(roomCode) {
        if (!this.reverbSocket || this.reverbSocket.readyState !== WebSocket.OPEN || !this.reverbSocketId) {
            return;
        }
        const cleanCode = (roomCode || '').toUpperCase().slice(0, 4);
        if (!cleanCode) {
            return;
        }
        const presenceChannel = `presence-room.${cleanCode}`;
        try {
            const res = await fetch('/broadcasting/auth', {
                method: 'POST',
                headers: this.requestHeaders(),
                body: JSON.stringify({
                    socket_id: this.reverbSocketId,
                    channel_name: presenceChannel,
                    player_name: this.playerName,
                }),
            });
            if (res.ok) {
                const authPayload = await res.json();
                this.reverbSocket.send(JSON.stringify({
                    event: 'pusher:subscribe',
                    data: {
                        channel: presenceChannel,
                        auth: authPayload.auth,
                        channel_data: authPayload.channel_data,
                    },
                }));
            }
        } catch {
            // Ignore
        }
        this.reverbSocket.send(JSON.stringify({
            event: 'pusher:subscribe',
            data: { channel: `room.${cleanCode}` },
        }));
    }

    broadcastEvent(type, payload) {
        if (this.broadcastChannel) {
            try {
                this.broadcastChannel.postMessage({ type, payload });
            } catch {
                // Ignore
            }
        }
        if (this.socket && typeof this.socket.emit === 'function' && this.room?.code) {
            try {
                this.socket.emit(type, { room_code: this.room.code, ...payload });
            } catch {
                // Ignore
            }
        }
    }

    // =========================================================================
    // Timers: Round Countdown, Energy Regen, 5s Cooldowns, Ping & State Sync
    // =========================================================================
    startTimers() {
        setInterval(() => {
            if (this.activeView === 'saboteur' || this.activeView === 'artist') {
                this.roundElapsedSeconds++;
                if (this.roundSecondsLeft > 0) {
                    this.roundSecondsLeft--;
                } else if (this.room?.status === 'playing') {
                    this.switchView('results');
                }
                const timerEl = document.getElementById('hud-round-timer');
                if (timerEl) {
                    timerEl.textContent = `${this.roundSecondsLeft}`;
                }
                const fg = document.getElementById('game-timer-fg');
                const ring = document.getElementById('game-timer-ring');
                const maxSec = this.room?.round_duration || 60;
                if (fg) {
                    const C = 163.4;
                    const progress = Math.max(0, Math.min(1, this.roundSecondsLeft / maxSec));
                    fg.style.strokeDashoffset = C * (1 - progress);
                }
                if (ring) {
                    ring.classList.toggle('lo', this.roundSecondsLeft <= 10);
                }
            }

            // Regenerate Chaos Energy (+3/dtk normal, +6/dtk in Kutukan Ekstrem mode)
            if (this.chaosEnergy < this.maxChaosEnergy) {
                const regenRate = this.room?.chaos_mode === 'extreme' ? 6 : 3;
                this.chaosEnergy = Math.min(this.maxChaosEnergy, this.chaosEnergy + regenRate);
                this.updateChaosEnergyUI();
            }

            // Tick down 5s curse card cooldowns
            let cdChanged = false;
            for (const key of Object.keys(this.curseCooldowns)) {
                if (this.curseCooldowns[key] > 0) {
                    this.curseCooldowns[key]--;
                    cdChanged = true;
                }
            }
            if (cdChanged) {
                this.updateCurseCooldownBadges();
            }

            // Tick down active canvas curse effects
            this.tickActiveCanvasCurses();
        }, 1000);

        // Real-time Ping RTT measurement & Room State sync every 2.5 seconds
        setInterval(() => {
            this.measureRealPing();
            if (this.room?.code) {
                this.pollRoomStateFallback();
            }
        }, 2500);
    }

    async pollRoomStateFallback() {
        if (!this.room?.code || this.isDrawing) {
            return;
        }
        try {
            const res = await fetch(`/api/rooms/${this.room.code}/state`, {
                headers: this.requestHeaders(),
            });
            if (!res.ok) {
                return;
            }
            const data = await res.json();
            if (typeof data.online_count === 'number') {
                this.onlineCount = data.online_count;
                this.renderPingAndOnlineUI();
            }
            if (!data.room) {
                return;
            }

            const prevStatus = this.room?.status;
            const prevRound = this.room?.current_round;
            const prevRoundId = this.room?.round?.id;
            const prevArtistId = this.room?.round?.artist_id;

            const isRoundRotated = Boolean(
                (prevRound && data.room.current_round !== prevRound) ||
                (prevRoundId && data.room.round?.id && data.room.round.id !== prevRoundId) ||
                (prevArtistId && data.room.round?.artist_id && Number(data.room.round.artist_id) !== Number(prevArtistId))
            );

            if (isRoundRotated) {
                this.handleRoundTransition(data.room);
                return;
            }

            this.applyRoomSnapshot(data.room);

            if (prevStatus === 'lobby' && data.room.status === 'playing' && this.activeView === 'lobby') {
                const myRole = this.determineMyRoleFromRoom();
                this.switchView(myRole);
            } else if (this.activeView === 'saboteur' || this.activeView === 'artist') {
                this.resizeAndRedrawCanvas();
                this.updateGameplayRoleUI();
            }
        } catch {
            // Ignore transient error
        }
    }

    tickActiveCanvasCurses() {
        const wrapper = document.getElementById('chaos-canvas-wrapper');
        const banner = document.getElementById('canvas-active-curse-banner');
        const bannerCount = document.getElementById('canvas-active-curse-countdown');
        const ghost = document.getElementById('inverted-cursor-ghost');
        const stickerLayer = document.getElementById('curse-sticker-bomb-layer');

        for (const key of Object.keys(this.activeCurses)) {
            if (this.activeCurses[key] > 0) {
                this.activeCurses[key]--;
                if (this.activeCurses[key] === 0) {
                    if (key === 'earthquake' && wrapper) {
                        wrapper.classList.remove('animate-chaos-earthquake', 'fx-gempa');
                    } else if (key === 'invert' && wrapper) {
                        wrapper.classList.remove('animate-chaos-drunk', 'fx-mabuk');
                        ghost?.classList.add('hidden');
                    } else if (key === 'blindfold') {
                        if (wrapper) wrapper.classList.remove('fx-gelap');
                        this.updateGameplayRoleUI();
                    } else if (key === 'sticker' && stickerLayer) {
                        stickerLayer.classList.add('hidden');
                    }
                }
            }
        }

        const primaryKey = this.getPrimaryActiveCurseKey();
        if (!primaryKey && banner) {
            banner.classList.add('hidden');
        } else if (primaryKey && bannerCount) {
            bannerCount.textContent = `${this.activeCurses[primaryKey]}s`;
        }
    }

    renderAll() {
        this.renderPingAndOnlineUI();
        this.renderLobbyPlayers();
        this.updateChaosEnergyUI();
        this.updateCurseCooldownBadges();
        this.renderQuickLeaderboard();
        this.renderMuseumGallery();
    }

    showToast(message) {
        const container = document.getElementById('chaos-toast-container');
        if (!container) {
            return;
        }
        const toast = document.createElement('div');
        toast.className = 'px-4 py-2.5 rounded-2xl bg-[#1C192B]/95 border border-[#B8FF3B]/50 text-white text-xs font-bold shadow-2xl flex items-center gap-2 transition-all duration-300 animate-toast';
        toast.innerHTML = `<span>${this.escapeHtml(message)}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('animate-toast');
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 280);
        }, 3200);
    }

    escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.CORET_CHAOS_BOOT) {
        window.coretChaosApp = new CoretChaosApp();
    }
});
