export const navigationMethods = {
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
    },

    switchView(viewName) {
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
            this.clearCanvasCurseVisuals(false);
        } else {
            this.clearCanvasCurseVisuals(true);
        }

        document.querySelectorAll('[data-screen-section]').forEach((sec) => {
            const secName = sec.getAttribute('data-screen-section');
            const shouldShow = secName === sectionToShow;
            sec.classList.toggle('hidden', !shouldShow);
            if (shouldShow) {
                sec.classList.remove('animate-view-fade');
                void sec.offsetWidth;
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
        } else if (sectionToShow === 'museum') {
            this.fetchMuseumGallery(this.museumFilter);
        }
    },

    hideNextRoundModal() {
        if (this.nextRoundModalTimer) {
            clearInterval(this.nextRoundModalTimer);
            this.nextRoundModalTimer = null;
        }
        this.nextRoundModalOpen = false;
        document.getElementById('next-round-transition-modal')?.classList.add('hidden');
    },

    showNextRoundModal(transitionInfo, myNewRole) {
        const modal = document.getElementById('next-round-transition-modal');
        if (!modal) {
            return;
        }

        this.hideNextRoundModal();
        this.nextRoundModalOpen = true;

        const badgeEl = document.getElementById('next-round-modal-badge');
        const titleEl = document.getElementById('next-round-modal-title');
        const prevWordEl = document.getElementById('next-round-modal-prev-word');
        const artistEl = document.getElementById('next-round-modal-next-artist');
        const roleEl = document.getElementById('next-round-modal-your-role');
        const countEl = document.getElementById('next-round-modal-countdown');

        const reason = transitionInfo?.reason || 'all_guessed';
        const nextRound = transitionInfo?.next_round_number || this.room?.current_round || 2;
        const maxRounds = transitionInfo?.max_rounds || this.room?.max_rounds || 5;
        const prevWord = transitionInfo?.previous_word || '-';
        const artistName = transitionInfo?.next_artist_name || this.room?.round?.artist_name || 'Pelukis';

        if (badgeEl) {
            badgeEl.textContent = reason === 'timeout'
                ? '⏱️ WAKTU RONDE HABIS!'
                : '🎉 SEMUA BERHASIL MENEBAK!';
        }
        if (titleEl) {
            titleEl.textContent = `Lanjut ke Ronde ${nextRound} dari ${maxRounds}`;
        }
        if (prevWordEl) {
            prevWordEl.textContent = prevWord ? String(prevWord).toUpperCase() : '-';
        }
        if (artistEl) {
            artistEl.textContent = artistName;
        }
        if (roleEl) {
            if (myNewRole === 'artist') {
                roleEl.textContent = '🎨 Peranmu: GILIRAN MENGGAMBAR!';
                roleEl.className = 'inline-block px-3 py-1 rounded-full bg-[#BDF454]/20 border border-[#BDF454]/40 text-xs font-extrabold text-[#BDF454]';
            } else {
                roleEl.textContent = '💥 Peranmu: Penebak & Saboteur';
                roleEl.className = 'inline-block px-3 py-1 rounded-full bg-[#5717BC]/30 border border-[#D1BCFF]/30 text-xs font-bold text-[#D1BCFF]';
            }
        }

        let sec = 3;
        if (countEl) {
            countEl.textContent = `Mulai dalam ${sec} detik...`;
        }

        modal.classList.remove('hidden');

        this.nextRoundModalTimer = setInterval(() => {
            sec--;
            if (sec <= 0) {
                this.hideNextRoundModal();
                return;
            }
            if (countEl) {
                countEl.textContent = `Mulai dalam ${sec} detik...`;
            }
        }, 1000);
    },

    clearCanvasCurseVisuals(clearBanner = true) {
        this.activeCurses = { earthquake: 0, invert: 0, blindfold: 0, sticker: 0 };

        const wrapper = document.getElementById('chaos-canvas-wrapper');
        if (wrapper) {
            wrapper.classList.remove('animate-chaos-earthquake', 'fx-gempa', 'animate-chaos-drunk', 'fx-mabuk', 'fx-gelap');
        }
        document.getElementById('inverted-cursor-ghost')?.classList.add('hidden');
        document.getElementById('curse-sticker-bomb-layer')?.classList.add('hidden');
        if (clearBanner) {
            document.getElementById('canvas-active-curse-banner')?.classList.add('hidden');
        }
    },

    handleRoundTransition(nextRoomSnapshot, transitionOverride = null) {
        if (!nextRoomSnapshot) {
            return;
        }

        if (this.resultsCountdownTimer) {
            clearInterval(this.resultsCountdownTimer);
            this.resultsCountdownTimer = null;
        }
        this.finishingRoundLock = false;
        this.currentGameId = nextRoomSnapshot.game_id || this.currentGameId;

        this.strokes = [];
        this.appliedCurseIds.clear();
        this.clearCanvasCurseVisuals(true);

        this.lastHintRoundId = nextRoomSnapshot.round?.id || null;
        this.lastRevealedHintsCount = Number(nextRoomSnapshot.round?.revealed_hints || 0);

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

        const transitionInfo = transitionOverride || nextRoomSnapshot.last_round_transition || null;
        const roundIdKey = nextRoomSnapshot.round?.id ? `${nextRoomSnapshot.game_id || 'g'}_${nextRoomSnapshot.round.id}` : null;
        if (transitionInfo?.reason === 'play_again') {
            this.hideNextRoundModal();
            this.lastShownTransitionRoundId = roundIdKey;
            this.showToast(`🔁 Permainan baru dimulai! Giliran ${nextRoomSnapshot.round?.artist_name || 'Pelukis'} menggambar!`);
        } else if (roundIdKey && this.lastShownTransitionRoundId !== roundIdKey) {
            this.lastShownTransitionRoundId = roundIdKey;
            this.showNextRoundModal(
                transitionInfo || {
                    reason: 'all_guessed',
                    next_round_number: nextRoomSnapshot.current_round,
                    max_rounds: nextRoomSnapshot.max_rounds,
                    next_artist_name: nextRoomSnapshot.round?.artist_name,
                },
                myRole
            );
        } else {
            this.showToast(`🎨 Ronde ${nextRoomSnapshot.current_round || 1}: Giliran ${nextRoomSnapshot.round?.artist_name || 'Pelukis'} menggambar!`);
        }
    },

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
    },

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

            const revealedHints = Number(this.room?.round?.revealed_hints || 0);
            const maxHints = Number(this.room?.round?.max_hints || 0);
            const nextHintSec = this.room?.round?.next_hint_in_seconds;
            const curRoundId = this.room?.round?.id || null;

            if (curRoundId !== null) {
                if (hasWord && this.lastHintRoundId === curRoundId && revealedHints > this.lastRevealedHintsCount) {
                    this.playSound('almost');
                    this.showToast(`💡 Clue huruf terbuka: ${maskedWord}`);
                }
                this.lastHintRoundId = curRoundId;
                this.lastRevealedHintsCount = revealedHints;
            }

            if (wordLabel) {
                const count = this.room?.round?.word_count || 1;
                if (hasWord && maxHints > 0) {
                    wordLabel.textContent = (revealedHints < maxHints && nextHintSec)
                        ? `Petunjuk (${count} kata) · Huruf ${revealedHints}/${maxHints} (+${nextHintSec}d)`
                        : `Petunjuk (${count} kata) · Huruf ${revealedHints}/${maxHints} terbuka`;
                } else {
                    wordLabel.textContent = `Petunjuk kata (${count} kata)`;
                }
            }
            if (wordDisplay) {
                if (hasWord) {
                    wordDisplay.textContent = maskedWord || '_ _ _ _ _ _';
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
    },
};
