export const realtimeSyncMethods = {
    setupRealtimeSync() {
        if (this.broadcastChannel) {
            this.broadcastChannel.onmessage = (event) => {
                this.handleIncomingRealtimeMessage(event.data || {});
            };
        }

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
                        curseData.remaining_seconds || curseData.duration_seconds,
                        curseData.triggered_by_name,
                        curseData.sticker_payload || curseData.payload || null,
                        curseData.id || null
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
    },

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
                payload.remaining_seconds || payload.duration_seconds,
                payload.triggered_by_name,
                payload.sticker_payload || payload.payload || null,
                payload.id || null
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
        } else if (type === 'game_finished' && payload?.room) {
            this.hideNextRoundModal();
            this.finishingRoundLock = false;
            this.applyRoomSnapshot(payload.room);
            this.showToast('🏆 Pertandingan selesai! Lihat hasil & leaderboard akhir!');
            this.switchView('results');
        } else if (type === 'returned_to_lobby') {
            this.hideNextRoundModal();
            const url = payload?.lobby_url || this.room?.lobby_url;
            if (url) {
                window.location.href = url;
            } else {
                this.pollRoomStateFallback();
            }
        } else if (type === 'results_ready_updated' && payload?.room) {
            this.applyRoomSnapshot(payload.room);
            if (this.activeView === 'results') {
                this.renderResultsScreen();
            }
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
    },

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
    },

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
    },

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
    },

    startTimers() {
        setInterval(() => {
            if (this.activeView === 'saboteur' || this.activeView === 'artist') {
                if (!this.nextRoundModalOpen) {
                    this.roundElapsedSeconds++;
                    if (this.roundSecondsLeft > 0) {
                        this.roundSecondsLeft--;
                    } else if (this.room?.status === 'playing' && !this.finishingRoundLock) {
                        this.finishingRoundLock = true;
                        this.triggerRoundTimeoutFinish();
                    }
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

            if (this.chaosEnergy < this.maxChaosEnergy) {
                const regenRate = this.room?.chaos_mode === 'extreme' ? 6 : 3;
                this.chaosEnergy = Math.min(this.maxChaosEnergy, this.chaosEnergy + regenRate);
                this.updateChaosEnergyUI();
            }

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

            this.tickActiveCanvasCurses();
        }, 1000);

        let pollTickCount = 0;
        setInterval(() => {
            pollTickCount++;
            const inGameplay = this.activeView === 'saboteur' || this.activeView === 'artist';
            if (pollTickCount % 3 === 1) {
                this.measureRealPing();
            }
            if (this.room?.code && (inGameplay || pollTickCount % 2 === 0)) {
                this.pollRoomStateFallback();
            }
        }, 1200);
    },

    async pollRoomStateFallback() {
        if (!this.room?.code || this.isPollingRoomState) {
            return;
        }
        this.isPollingRoomState = true;
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
            const prevGameId = this.room?.game_id || this.currentGameId;
            const prevRound = this.room?.current_round;
            const prevRoundId = this.room?.round?.id;
            const prevArtistId = this.room?.round?.artist_id;

            if (data.room.status === 'lobby' && ['artist', 'saboteur', 'results'].includes(this.activeView)) {
                this.hideNextRoundModal();
                if (this.resultsCountdownTimer) {
                    clearInterval(this.resultsCountdownTimer);
                    this.resultsCountdownTimer = null;
                }
                if (data.room.lobby_url) {
                    this.showToast('👑 Leader membawa semua pemain kembali ke Lobby!');
                    window.location.href = data.room.lobby_url;
                    return;
                }
                this.applyRoomSnapshot(data.room);
                this.switchView('lobby');
                return;
            }

            if (data.room.status === 'finished' && this.activeView !== 'results') {
                this.hideNextRoundModal();
                this.finishingRoundLock = false;
                this.applyRoomSnapshot(data.room);
                this.showToast('🏆 Pertandingan selesai! Lihat hasil & leaderboard akhir!');
                this.switchView('results');
                return;
            }

            const isNewGameStarted = Boolean(
                ((prevStatus === 'finished' || prevStatus === 'results') && data.room.status === 'playing') ||
                (prevGameId && data.room.game_id && Number(data.room.game_id) !== Number(prevGameId))
            );
            if (isNewGameStarted) {
                this.handleRoundTransition(data.room, { reason: 'play_again' });
                return;
            }

            const isRoundRotated = Boolean(
                (prevRound && data.room.current_round !== prevRound) ||
                (prevRoundId && data.room.round?.id && data.room.round.id !== prevRoundId) ||
                (prevArtistId && data.room.round?.artist_id && Number(data.room.round.artist_id) !== Number(prevArtistId))
            );

            if (isRoundRotated) {
                this.handleRoundTransition(data.room, data.room.last_round_transition || null);
                return;
            }

            this.applyRoomSnapshot(data.room);
            this.syncActiveCursesFromRoom(data.room);

            if (prevStatus === 'lobby' && data.room.status === 'playing' && this.activeView === 'lobby') {
                const myRole = this.determineMyRoleFromRoom();
                this.switchView(myRole);
            } else if (this.activeView === 'results' && data.room.status === 'finished') {
                this.renderResultsScreen();
            } else if (this.activeView === 'saboteur' || this.activeView === 'artist') {
                if (!this.isDrawing) {
                    this.resizeAndRedrawCanvas();
                }
                this.updateGameplayRoleUI();
            }
        } catch {
            // Ignore transient error
        } finally {
            this.isPollingRoomState = false;
        }
    },
};
