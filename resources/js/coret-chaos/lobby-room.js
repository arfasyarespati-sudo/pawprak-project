export const lobbyAndRoomMethods = {
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
    },

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

        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeRoomSettingsModal();
                    return;
                }

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
                }
            });
        }

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

        if (btnSubmit) {
            btnSubmit.addEventListener('click', async (e) => {
                e.preventDefault();
                await this.submitRoomSettingsModal();
            });
        }
    },

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
    },

    openRoomSettingsModal(mode = 'create') {
        this.roomSettingsModalMode = mode;
        const modal = document.getElementById('modal-room-settings');
        const titleEl = document.getElementById('modal-room-settings-title');
        const subtitleEl = document.getElementById('modal-room-settings-subtitle');
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
    },

    closeRoomSettingsModal() {
        document.getElementById('modal-room-settings')?.classList.add('hidden');
    },

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
    },

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
    },

    async reconnectToRoom(code, viewParam = null) {
        await this.joinRoomByCode(code, viewParam || 'lobby');
    },

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

        if (Array.isArray(roomSnapshot.strokes) && !this.isDrawing) {
            if (this.currentRole !== 'artist' || this.strokes.length === 0) {
                this.strokes = roomSnapshot.strokes;
            }
        }

        if (typeof roomSnapshot.round?.remaining_seconds === 'number' && roomSnapshot.round?.status === 'drawing') {
            if (Math.abs(this.roundSecondsLeft - roomSnapshot.round.remaining_seconds) >= 3) {
                this.roundSecondsLeft = Math.max(0, roomSnapshot.round.remaining_seconds);
            }
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
    },

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
    },

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
    },

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
    },

    bindLobbyActions() {
        document.getElementById('btn-lobby-copy-code')?.addEventListener('click', () => {
            this.copyRoomCodeOnly();
        });

        document.getElementById('btn-lobby-copy-link')?.addEventListener('click', () => {
            this.copyRoomInviteLink();
        });

        document.getElementById('btn-lobby-leave-room')?.addEventListener('click', async () => {
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

        document.getElementById('btn-lobby-edit-room')?.addEventListener('click', () => {
            this.openRoomSettingsModal('edit');
        });

        document.getElementById('btn-lobby-toggle-ready')?.addEventListener('click', () => {
            this.toggleReadyStatus();
        });

        document.getElementById('btn-lobby-start-game')?.addEventListener('click', async () => {
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
    },

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
    },

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
    },

    renderLobbyPlayers() {
        const grid = document.getElementById('lobby-players-grid');
        const counterEl = document.getElementById('lobby-player-counter');
        const statusBanner = document.getElementById('lobby-status-banner');
        const startBtn = document.getElementById('btn-lobby-start-game');
        const readyBtn = document.getElementById('btn-lobby-toggle-ready');
        const hintEl = document.getElementById('lobby-hint-text');
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

        if (editRoomBtn) {
            editRoomBtn.classList.toggle('hidden', !amIHost);
        }

        if (statusBanner) {
            statusBanner.textContent = 'Klik kartumu untuk mengubah status siap. Pelukis pertama dipilih acak oleh server.';
        }

        const isMeReady = Boolean(myEntry?.is_ready);
        if (readyBtn) {
            readyBtn.textContent = isMeReady ? '✓ SIAP TEMPUR (BATALKAN)' : '✓ UBAH STATUS SIAP';
            readyBtn.className = isMeReady
                ? 'px-4 py-2.5 rounded-xl bg-[#16291A] border border-[#BDF454] text-xs font-extrabold text-[#BDF454] transition cursor-pointer'
                : 'px-4 py-2.5 rounded-xl bg-[#28283C] hover:bg-[#333347] border border-[#333347] text-xs font-extrabold text-[#E2E0FB] transition cursor-pointer';
        }

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
    },

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
    },
};
