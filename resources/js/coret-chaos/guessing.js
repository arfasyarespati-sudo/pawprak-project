export const guessingMethods = {
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
    },

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
                    if (data.game_finished && data.room) {
                        this.hideNextRoundModal();
                        this.finishingRoundLock = false;
                        this.applyRoomSnapshot(data.room);
                        this.broadcastEvent('game_finished', { room: data.room });
                        this.showToast('🏆 Pertandingan selesai! Lihat hasil & leaderboard akhir!');
                        this.switchView('results');
                    } else if (data.round_rotated && data.room) {
                        this.broadcastEvent('round_rotated', { round_number: data.next_round_number });
                        this.handleRoundTransition(data.room, {
                            round_id: data.room?.round?.id,
                            reason: 'all_guessed',
                            previous_word: data.previous_word,
                            next_round_number: data.next_round_number,
                            max_rounds: data.room?.max_rounds,
                            next_artist_name: data.next_artist_name,
                        });
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
    },

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
    },

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
    },
};
