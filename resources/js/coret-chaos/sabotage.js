export const sabotageMethods = {
    bindSaboteurDeck() {
        document.querySelectorAll('[data-curse-trigger]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const curseKey = btn.getAttribute('data-curse-trigger');
                if (curseKey) {
                    this.triggerCurse(curseKey);
                }
            });
        });
    },

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

                this.curseCooldowns[curseKey] = 5;
                this.updateChaosEnergyUI();
                this.updateCurseCooldownBadges();

                this.applyCurseEffect(
                    curseKey,
                    data.sabotage.duration_seconds || config.duration_seconds,
                    data.sabotage.triggered_by_name || this.playerName,
                    data.sabotage.sticker_payload || null,
                    data.sabotage.id || null
                );

                this.broadcastEvent('sabotage_triggered', data.sabotage);
            }
        } catch {
            this.showToast('⚠️ Gagal mengirim kartu kutukan ke server.');
        }
    },

    syncActiveCursesFromRoom(roomSnapshot) {
        if (!roomSnapshot) {
            return;
        }
        const candidates = [];
        if (Array.isArray(roomSnapshot.active_curses) && roomSnapshot.active_curses.length > 0) {
            candidates.push(...roomSnapshot.active_curses);
        } else if (roomSnapshot.latest_curse) {
            candidates.push(roomSnapshot.latest_curse);
        }

        for (const curse of candidates) {
            if (!curse || !curse.id) {
                continue;
            }
            const curseIdNum = Number(curse.id);
            const remainingSec = Number(curse.remaining_seconds ?? curse.duration_seconds ?? 0);
            if (!this.appliedCurseIds.has(curseIdNum) && remainingSec > 0) {
                this.applyCurseEffect(
                    curse.curse_type,
                    remainingSec,
                    curse.triggered_by_name || 'Saboteur',
                    curse.payload || curse.sticker_payload || null,
                    curseIdNum
                );
            }
        }
    },

    applyCurseEffect(curseKey, durationSec, triggeredByName, stickerPayload = null, curseId = null) {
        if (curseId !== null && curseId !== undefined) {
            const numericId = Number(curseId);
            if (this.appliedCurseIds.has(numericId)) {
                return;
            }
            this.appliedCurseIds.add(numericId);
        }

        const config = this.deckCatalog[curseKey] || {
            name: curseKey,
            icon: '⚡',
            duration_seconds: durationSec || 4,
        };

        const effectiveDuration = Math.max(1, Number(durationSec || config.duration_seconds || 4));
        const isArtist = this.determineMyRoleFromRoom() === 'artist' || this.currentRole === 'artist';

        this.activeCurses[curseKey] = Math.max(this.activeCurses[curseKey] || 0, effectiveDuration);
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
            bannerText.textContent = isArtist
                ? `${config.icon} KAMU KENA ${config.name.toUpperCase()} OLEH ${String(triggeredByName || 'SABOTEUR').toUpperCase()}!`
                : `${config.icon} PELUKIS KENA ${config.name.toUpperCase()} (${String(triggeredByName || 'SABOTEUR').toUpperCase()})`;
            bannerCount.textContent = `${this.activeCurses[curseKey]}s`;
        }

        if (!isArtist) {
            const isSelfCast = (triggeredByName || '').toLowerCase() === (this.playerName || '').toLowerCase();
            if (isSelfCast) {
                this.showToast(`🎯 Berhasil! Kutukan ${config.name} langsung menyerang layar Pelukis!`);
            } else {
                this.showToast(`💥 ${triggeredByName} mengirim ${config.name} ke layar Pelukis!`);
            }
            return;
        }

        if (curseKey === 'earthquake' && wrapper) {
            wrapper.classList.add('animate-chaos-earthquake', 'fx-gempa');
            this.showToast(`🌋 SABOTASE! ${triggeredByName} mengguncang kanvasmu dengan GEMPA BUMI!`);
        } else if (curseKey === 'invert' && wrapper) {
            wrapper.classList.add('animate-chaos-drunk', 'fx-mabuk');
            this.showToast(`🌀 SABOTASE! ${triggeredByName} mengaktifkan KUAS MABUK (Sumbu X/Y Terbalik)!`);
        } else if (curseKey === 'blindfold') {
            if (wrapper) {
                wrapper.classList.add('fx-gelap');
            }
            this.updateGameplayRoleUI();
            this.showToast(`💡 MATI LAMPU! ${triggeredByName} menggelapkan kanvasmu selama ${effectiveDuration} detik!`);
        } else if (curseKey === 'sticker') {
            this.renderStickerBombOverlay(stickerPayload);
            this.showToast(`💣 STICKER BOMB! ${triggeredByName} menutup kanvasmu dengan stiker!`);
        }
    },

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
    },

    getPrimaryActiveCurseKey() {
        for (const key of ['earthquake', 'invert', 'blindfold', 'sticker']) {
            if (this.activeCurses[key] > 0) {
                return key;
            }
        }
        return null;
    },

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
    },

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
    },

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
    },
};
