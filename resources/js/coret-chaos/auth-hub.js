import { AVATAR_CATALOG } from './constants';

export const authAndHubMethods = {
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
    },

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
    },

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
    },

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
    },

    bindAuthAndProfileActions() {
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

        const landingAuthLink = document.getElementById('btn-landing-auth-link');
        if (landingAuthLink) {
            landingAuthLink.addEventListener('click', (e) => {
                if (this.userProfile?.is_authenticated) {
                    e.stopImmediatePropagation();
                    this.openProfileModal();
                }
            }, true);
        }

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

        document.getElementById('btn-login-as-guest')?.addEventListener('click', () => {
            this.showToast(`🎲 Bermain cepat sebagai ${this.playerName} (Mode Tamu)`);
            this.switchView('hub');
        });

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
    },

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
    },

    closeProfileModal() {
        document.getElementById('modal-player-profile')?.classList.add('hidden');
    },

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
    },

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
    },

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
    },

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
    },

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
    },

    closeJoinLobbyModal() {
        document.getElementById('modal-join-lobby')?.classList.add('hidden');
    },

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
    },
};
