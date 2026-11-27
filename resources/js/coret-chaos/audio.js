export const audioMethods = {
    playSound(type) {
        if (!this.soundEnabled) {
            return;
        }
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) {
                return;
            }
            if (!this.audioCtx) {
                this.audioCtx = new AudioCtx();
            }
            const ctx = this.audioCtx;
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            if (type === 'earthquake') {
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(95, now);
                osc.frequency.exponentialRampToValueAtTime(42, now + 0.45);
                gain.gain.setValueAtTime(0.18, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.45);
                osc.start(now);
                osc.stop(now + 0.45);
            } else if (type === 'invert') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(320, now);
                osc.frequency.linearRampToValueAtTime(640, now + 0.18);
                osc.frequency.linearRampToValueAtTime(220, now + 0.35);
                gain.gain.setValueAtTime(0.15, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.36);
                osc.start(now);
                osc.stop(now + 0.36);
            } else if (type === 'blindfold') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(440, now);
                osc.frequency.exponentialRampToValueAtTime(65, now + 0.35);
                gain.gain.setValueAtTime(0.2, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.35);
                osc.start(now);
                osc.stop(now + 0.35);
            } else if (type === 'sticker') {
                osc.type = 'square';
                osc.frequency.setValueAtTime(180, now);
                osc.frequency.exponentialRampToValueAtTime(520, now + 0.15);
                gain.gain.setValueAtTime(0.15, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.16);
                osc.start(now);
                osc.stop(now + 0.16);
            } else if (type === 'almost') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(523.25, now);
                osc.frequency.setValueAtTime(659.25, now + 0.1);
                gain.gain.setValueAtTime(0.14, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.28);
                osc.start(now);
                osc.stop(now + 0.28);
            } else if (type === 'exact') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(523.25, now);
                osc.frequency.setValueAtTime(659.25, now + 0.1);
                osc.frequency.setValueAtTime(783.99, now + 0.2);
                osc.frequency.setValueAtTime(1046.5, now + 0.3);
                gain.gain.setValueAtTime(0.18, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.55);
                osc.start(now);
                osc.stop(now + 0.55);
            }
        } catch {
            // Ignore audio context restriction errors
        }
    },
};
