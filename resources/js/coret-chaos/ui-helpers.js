import { AVATAR_CATALOG } from './constants';

export const uiHelperMethods = {
    getAvatarSvg(avatarKey) {
        const found = AVATAR_CATALOG.find((a) => a.key === avatarKey) || AVATAR_CATALOG[0];
        return found.svg;
    },

    getAvatarMeta(avatarKey) {
        return AVATAR_CATALOG.find((a) => a.key === avatarKey) || AVATAR_CATALOG[0];
    },

    updateServerFooterHost() {
        const hostEl = document.getElementById('footer-server-host');
        if (hostEl) {
            hostEl.textContent = `🌐 ${window.location.host}`;
        }
    },

    requestHeaders() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'X-Guest-Token': this.guestToken,
            'X-Player-Nickname': this.playerName,
        };
    },

    async measureRealPing() {
        const start = performance.now();
        try {
            if (this.socket && this.socket.connected) {
                this.socket.emit('ping:check', start, () => {
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
    },

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
    },

    isCurrentPlayerHost() {
        const players = Array.isArray(this.room?.players) ? this.room.players : [];
        const me = players.find((p) => Number(p.id) === Number(this.playerId))
            || players.find((p) => (p.name || '').toLowerCase() === (this.playerName || '').toLowerCase());
        if (me && typeof me.is_host === 'boolean') {
            return me.is_host;
        }
        return Boolean(
            this.room?.host_player_id &&
            (String(this.room.host_player_id) === String(this.playerId) || String(this.room.host_player_id) === String(this.guestToken))
        );
    },

    renderAll() {
        this.renderPingAndOnlineUI();
        this.renderLobbyPlayers();
        this.updateChaosEnergyUI();
        this.updateCurseCooldownBadges();
        this.renderQuickLeaderboard();
        this.renderMuseumGallery();
    },

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
    },

    escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    },
};
