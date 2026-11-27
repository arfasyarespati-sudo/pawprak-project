import { AVATAR_CATALOG } from './constants';
import { audioMethods } from './audio';
import { uiHelperMethods } from './ui-helpers';
import { navigationMethods } from './navigation';
import { authAndHubMethods } from './auth-hub';
import { lobbyAndRoomMethods } from './lobby-room';
import { canvasMethods } from './canvas';
import { sabotageMethods } from './sabotage';
import { guessingMethods } from './guessing';
import { resultsAndMuseumMethods } from './results-museum';
import { realtimeSyncMethods } from './realtime-sync';

export class CoretChaosApp {
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

        this.room = bootData.initialRoom || null;
        this.activeView = 'home';
        this.currentRole = 'saboteur';

        this.strokes = [];
        this.isDrawing = false;
        this.currentStroke = null;
        this.brushColor = '#14121D';
        this.brushSize = 6;
        this.lastCanvasDataUrl = null;

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
        this.appliedCurseIds = new Set();
        this.isPollingRoomState = false;
        this.lastHintRoundId = bootData.initialRoom?.round?.id || null;
        this.lastRevealedHintsCount = Number(bootData.initialRoom?.round?.revealed_hints || 0);

        if (bootData.initialRoom?.latest_curse?.id && Number(bootData.initialRoom.latest_curse.remaining_seconds ?? 0) <= 0) {
            this.appliedCurseIds.add(Number(bootData.initialRoom.latest_curse.id));
        }

        this.roundSecondsLeft = 60;
        this.roundElapsedSeconds = 0;
        this.nextRoundModalOpen = false;
        this.nextRoundModalTimer = null;
        this.finishingRoundLock = false;
        this.lastShownTransitionRoundId = null;
        this.currentGameId = bootData.initialRoom?.game_id || null;
        this.soundEnabled = true;
        this.pingMs = null;
        this.socket = null;
        this.reverbSocket = null;

        this.broadcastChannel = typeof BroadcastChannel !== 'undefined'
            ? new BroadcastChannel('coret_chaos_realtime_v2')
            : null;
        this.renderedGuessKeys = new Set();
        this.renderedLobbyChatIds = new Set();

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

        const params = new URLSearchParams(window.location.search);
        const bootData = window.CORET_CHAOS_BOOT || {};
        const roomParam = params.get('room') || (!bootData.initialRoom ? sessionStorage.getItem('coret_active_room_code') : null);
        const viewParam = params.get('view') || bootData.bootView;

        if (bootData.initialRoom && !params.get('room')) {
            this.applyRoomSnapshot(bootData.initialRoom);
            const targetView = viewParam === 'game' ? this.determineMyRoleFromRoom() : (viewParam || 'lobby');
            this.switchView(targetView);
            this.syncActiveCursesFromRoom(bootData.initialRoom);
        } else if (roomParam) {
            this.reconnectToRoom(roomParam.toUpperCase().slice(0, 4), viewParam);
        } else if (viewParam) {
            this.switchView(viewParam);
        }

        window.addEventListener('beforeunload', () => {
            if (this.room?.code) {
                const payload = JSON.stringify({ player_name: this.playerName });
                navigator.sendBeacon?.(`/api/rooms/${this.room.code}/leave`, new Blob([payload], { type: 'application/json' }));
            }
        });
    }
}

Object.assign(
    CoretChaosApp.prototype,
    audioMethods,
    uiHelperMethods,
    navigationMethods,
    authAndHubMethods,
    lobbyAndRoomMethods,
    canvasMethods,
    sabotageMethods,
    guessingMethods,
    resultsAndMuseumMethods,
    realtimeSyncMethods
);
