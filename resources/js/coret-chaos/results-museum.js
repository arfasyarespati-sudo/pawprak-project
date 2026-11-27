export const resultsAndMuseumMethods = {
    bindResultsAndMuseumActions() {
        document.getElementById('btn-replay-polaroid-timelapse')?.addEventListener('click', () => {
            this.playPolaroidTimeLapse('results-polaroid-canvas');
        });

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

        document.getElementById('btn-results-next-round')?.addEventListener('click', async () => {
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
                        this.broadcastEvent('round_rotated', data);
                    }
                }
            } catch {
                // Ignore
            }
        });

        document.getElementById('btn-save-hall-of-shame')?.addEventListener('click', async () => {
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

        document.getElementById('btn-download-wa-sticker')?.addEventListener('click', () => {
            const title = this.room?.round?.secret_word || 'Karya CORET! CHAOS';
            const artist = this.room?.round?.artist_name || this.playerName;
            this.exportWhatsAppStickerWebp(title, artist);
        });

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

        document.getElementById('btn-dismiss-next-round-modal')?.addEventListener('click', () => {
            this.hideNextRoundModal();
        });

        const btnPlayAgain = document.getElementById('btn-results-play-again');
        if (btnPlayAgain) {
            btnPlayAgain.addEventListener('click', async () => {
                if (!this.room?.code) {
                    return;
                }
                btnPlayAgain.disabled = true;
                try {
                    const res = await fetch(`/api/rooms/${this.room.code}/play-again`, {
                        method: 'POST',
                        headers: this.requestHeaders(),
                        body: JSON.stringify({ player_name: this.playerName }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.showToast(`⚠️ ${data.message || 'Gagal memperbarui status siap.'}`);
                        return;
                    }
                    if (data.match_started && data.room) {
                        this.broadcastEvent('round_rotated', { round_number: 1 });
                        this.handleRoundTransition(data.room, { reason: 'play_again' });
                    } else if (data.room) {
                        this.applyRoomSnapshot(data.room);
                        this.renderResultsScreen();
                        this.broadcastEvent('results_ready_updated', { room: data.room });
                        this.showToast(data.is_ready ? '✅ Kamu SIAP main lagi! Menunggu pemain lain...' : '⏳ Status siap dibatalkan.');
                    }
                } catch {
                    this.showToast('⚠️ Gagal menghubungi server.');
                } finally {
                    btnPlayAgain.disabled = false;
                }
            });
        }

        const btnReturnLobby = document.getElementById('btn-results-return-lobby');
        if (btnReturnLobby) {
            btnReturnLobby.addEventListener('click', async () => {
                if (!this.room?.code) {
                    window.location.href = '/lobby';
                    return;
                }
                btnReturnLobby.disabled = true;
                try {
                    const res = await fetch(`/api/rooms/${this.room.code}/return-lobby`, {
                        method: 'POST',
                        headers: this.requestHeaders(),
                        body: JSON.stringify({ player_name: this.playerName }),
                    });
                    const data = await res.json();
                    if (res.ok) {
                        if (data.returned_all) {
                            this.broadcastEvent('returned_to_lobby', { lobby_url: data.redirect || this.room?.lobby_url });
                        }
                        if (data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }
                        if (data.room) {
                            this.applyRoomSnapshot(data.room);
                            this.switchView('lobby');
                            return;
                        }
                    }
                    this.showToast(`⚠️ ${data.message || 'Gagal kembali ke lobby.'}`);
                } catch {
                    this.showToast('⚠️ Gagal menghubungi server saat kembali ke lobby.');
                } finally {
                    btnReturnLobby.disabled = false;
                }
            });
        }
    },

    async triggerRoundTimeoutFinish() {
        if (!this.room?.code) {
            this.finishingRoundLock = false;
            return;
        }
        try {
            const res = await fetch(`/api/rooms/${this.room.code}/finish-round`, {
                method: 'POST',
                headers: this.requestHeaders(),
                body: JSON.stringify({ player_name: this.playerName }),
            });
            if (!res.ok) {
                this.switchView('results');
                return;
            }
            const data = await res.json();
            if (data.game_finished && data.room) {
                this.hideNextRoundModal();
                this.applyRoomSnapshot(data.room);
                this.broadcastEvent('game_finished', { room: data.room });
                this.showToast('🏆 Pertandingan selesai! Lihat hasil & leaderboard akhir!');
                this.switchView('results');
            } else if (data.round_rotated && data.room) {
                this.broadcastEvent('round_rotated', { round_number: data.next_round_number });
                this.handleRoundTransition(data.room, {
                    round_id: data.room?.round?.id,
                    reason: 'timeout',
                    previous_word: data.previous_word,
                    next_round_number: data.next_round_number,
                    max_rounds: data.room?.max_rounds,
                    next_artist_name: data.next_artist_name,
                });
            } else if (data.room) {
                this.applyRoomSnapshot(data.room);
                this.switchView('results');
            }
        } catch {
            this.switchView('results');
        } finally {
            this.finishingRoundLock = false;
        }
    },

    renderResultsScreen() {
        const roundBadge = document.getElementById('results-round-badge');
        const mainTitleEl = document.getElementById('results-main-title');
        const leaderboardHeadingEl = document.getElementById('results-leaderboard-heading');
        const wordEl = document.getElementById('results-secret-word');
        const scorersList = document.getElementById('results-top-scorers-list');
        const readyStatusEl = document.getElementById('results-ready-status-text');
        const playAgainBtn = document.getElementById('btn-results-play-again');
        const returnLobbyBtn = document.getElementById('btn-results-return-lobby');

        const maxRounds = this.room?.max_rounds || 5;
        const secretWord = this.room?.round?.secret_word || this.room?.round?.masked_word || '-';

        if (roundBadge) {
            roundBadge.textContent = `🏆 Semua ${maxRounds} ronde selesai`;
        }
        if (mainTitleEl) {
            mainTitleEl.textContent = 'SKOR AKHIR PERTANDINGAN 🏆';
        }
        if (leaderboardHeadingEl) {
            leaderboardHeadingEl.textContent = 'Leaderboard Skor Akhir';
        }
        if (wordEl) {
            wordEl.textContent = String(secretWord).toUpperCase();
        }

        const players = Array.isArray(this.room?.players)
            ? [...this.room.players].sort((a, b) => (b.score || 0) - (a.score || 0))
            : [];

        if (scorersList) {
            scorersList.innerHTML = '';
            if (players.length === 0) {
                scorersList.innerHTML = '<div class="rounded-2xl bg-[#0C0C1F] border border-[#333347] p-5 text-center text-xs text-[#C3C9B1]">Belum ada data skor pemain.</div>';
            } else {
                const rankMedals = ['🥇', '🥈', '🥉'];
                players.forEach((p, idx) => {
                    const rankDisplay = rankMedals[idx] || `#${idx + 1}`;
                    const avSvg = this.getAvatarSvg(p.avatar || 'cat-neon');
                    const isFirst = idx === 0;
                    const isReady = Boolean(p.is_ready);
                    const hostBadge = p.is_host ? '<span class="text-[11px]" title="Leader">👑</span>' : '';
                    const readyBadge = isReady
                        ? '<span class="px-2 py-0.5 rounded-full bg-[#BDF454]/15 border border-[#BDF454]/40 text-[10px] font-extrabold text-[#BDF454]">✅ SIAP</span>'
                        : '<span class="px-2 py-0.5 rounded-full bg-[#28283C] border border-[#333347] text-[10px] font-bold text-[#8D937D]">⏳ BELUM SIAP</span>';

                    const row = document.createElement('div');
                    row.className = `flex items-center justify-between gap-3 rounded-2xl px-4 py-3 border ${
                        isFirst
                            ? 'bg-gradient-to-r from-[#28283C] to-[#1A1A2D] border-[#FFC83B]/50'
                            : 'bg-[#0C0C1F] border-[#333347]'
                    }`;
                    row.innerHTML = `
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-7 text-center font-serif font-black text-base ${isFirst ? 'text-[#FFC83B]' : 'text-[#8D937D]'} shrink-0">${rankDisplay}</span>
                            <div class="w-10 h-10 rounded-xl bg-[#1A1A2D] border border-[#333347] overflow-hidden shrink-0">${avSvg}</div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-sm text-[#E2E0FB] truncate">${this.escapeHtml(p.name || 'Pemain')}</span>
                                    ${hostBadge}
                                    ${readyBadge}
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="font-serif font-black text-xl text-[#BDF454] leading-none">${p.score || 0}</div>
                            <div class="text-[10px] font-bold text-[#8D937D] mt-0.5">PTS</div>
                        </div>
                    `;
                    scorersList.appendChild(row);
                });
            }
        }

        if (this.resultsCountdownTimer) {
            clearInterval(this.resultsCountdownTimer);
            this.resultsCountdownTimer = null;
        }

        const totalPlayers = players.length;
        const readyCount = players.filter((p) => Boolean(p.is_ready)).length;
        const me = players.find((p) => Number(p.id) === Number(this.playerId))
            || players.find((p) => (p.name || '').toLowerCase() === (this.playerName || '').toLowerCase());
        const amIReady = Boolean(me?.is_ready);
        const amHost = this.isCurrentPlayerHost();

        if (readyStatusEl) {
            readyStatusEl.textContent = totalPlayers >= 2 && readyCount >= totalPlayers
                ? `🚀 Semua pemain siap (${readyCount}/${totalPlayers})! Memulai permainan baru...`
                : `Siap Main Lagi: ${readyCount} / ${totalPlayers} Pemain — Otomatis mulai saat semua pemain siap`;
        }

        if (playAgainBtn) {
            playAgainBtn.disabled = false;
            if (amIReady) {
                playAgainBtn.textContent = `✅ SUDAH SIAP (${readyCount}/${totalPlayers})`;
                playAgainBtn.className = 'flex-1 py-3.5 px-5 rounded-2xl bg-[#28283C] border-2 border-[#BDF454] text-[#BDF454] font-extrabold text-xs sm:text-sm tracking-wider uppercase transition cursor-pointer';
            } else {
                playAgainBtn.textContent = `✋ SIAP MAIN LAGI (${readyCount}/${totalPlayers})`;
                playAgainBtn.className = 'flex-1 py-3.5 px-5 rounded-2xl bg-[#BDF454] hover:brightness-110 text-[#111124] font-extrabold text-xs sm:text-sm tracking-wider uppercase shadow-[0_10px_24px_rgba(189,244,84,0.22)] transition cursor-pointer';
            }
        }

        if (returnLobbyBtn) {
            returnLobbyBtn.textContent = amHost ? '🏠 Kembali ke Lobby (Semua)' : '🚪 Keluar ke Lobby';
        }
    },

    renderResultsPolaroidCanvas() {
        const canvas = document.getElementById('results-polaroid-canvas');
        if (!canvas) {
            return;
        }
        this.drawStrokesOnCanvas(canvas, this.strokes);
    },

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
    },

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
    },

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
    },

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
    },
};
