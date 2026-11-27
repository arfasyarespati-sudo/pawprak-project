export const canvasMethods = {
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
    },

    bindCanvasEvents() {
        const canvas = document.getElementById('chaos-canvas');
        if (!canvas) {
            return;
        }

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

        document.querySelectorAll('[data-brush-color]').forEach((btn) => {
            btn.addEventListener('click', () => {
                this.brushColor = btn.getAttribute('data-brush-color') || '#222222';
                document.querySelectorAll('[data-brush-color]').forEach((b) => {
                    b.classList.remove('on', 'ring-2', 'ring-[#B8FF3B]', 'scale-110');
                });
                btn.classList.add('on');
            });
        });

        document.querySelectorAll('[data-brush-size]').forEach((btn) => {
            btn.addEventListener('click', () => {
                this.brushSize = parseInt(btn.getAttribute('data-brush-size') || '6', 10);
                document.querySelectorAll('[data-brush-size]').forEach((b) => {
                    b.classList.remove('on', 'bg-[#B8FF3B]', 'text-[#0E0D15]');
                });
                btn.classList.add('on');
            });
        });

        document.getElementById('btn-canvas-undo')?.addEventListener('click', () => {
            if (this.currentRole !== 'artist') {
                this.showToast('⚠️ Hanya Pelukis yang bisa menghapus goresan!');
                return;
            }
            this.strokes.pop();
            this.resizeAndRedrawCanvas();
            this.broadcastStrokeToBackend({ stroke_type: 'undo' });
        });

        document.getElementById('btn-canvas-clear')?.addEventListener('click', () => {
            if (this.currentRole !== 'artist') {
                this.showToast('⚠️ Hanya Pelukis yang bisa membersihkan kanvas!');
                return;
            }
            this.strokes = [];
            this.resizeAndRedrawCanvas();
            this.broadcastStrokeToBackend({ stroke_type: 'clear' });
            this.showToast('🗑 Kanvas dibersihkan oleh Pelukis!');
        });

        const getNormalizedPoint = (e) => {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            let nx = (clientX - rect.left) / Math.max(1, rect.width);
            let ny = (clientY - rect.top) / Math.max(1, rect.height);
            nx = Math.max(0.02, Math.min(0.98, nx));
            ny = Math.max(0.02, Math.min(0.98, ny));

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
    },

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
    },

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
    },

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
    },
};
