/**
 * CORET! CHAOS — Real-Time Multiplayer Server (Node.js + Express + Socket.IO + SQLite)
 * Listens on 0.0.0.0:(process.env.PORT || 3000) for LAN WiFi & Render/Railway deployment.
 */

import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { DatabaseSync } from 'node:sqlite';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const PORT = Number(process.env.PORT || 3000);
const HOST = '0.0.0.0';

// ============================================================================
// 1. SQLite Database Initialization (Museum / Hall of Shame Persistence)
// ============================================================================
const dbDir = path.join(__dirname, 'database');
if (!fs.existsSync(dbDir)) {
    fs.mkdirSync(dbDir, { recursive: true });
}
const sqliteDb = new DatabaseSync(path.join(dbDir, 'museum_real.sqlite'));

sqliteDb.exec(`
    CREATE TABLE IF NOT EXISTS museum_artworks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        secret_word TEXT NOT NULL,
        artist_name TEXT NOT NULL,
        image_data_url TEXT NOT NULL,
        chaos_rating INTEGER NOT NULL DEFAULT 0,
        star_rating REAL NOT NULL DEFAULT 0,
        votes_count INTEGER NOT NULL DEFAULT 0,
        created_at INTEGER NOT NULL
    );
`);

// ============================================================================
// 2. Profanity Filter & Levenshtein Distance Engine
// ============================================================================
const PROFANITY_WORDS = [
    'anjing', 'anjir', 'babi', 'bangsat', 'bajingan', 'kontol',
    'memek', 'jembut', 'ngentot', 'tolol', 'goblok', 'idiot',
    'asu', 'jancok', 'cok', 'kampret', 'keparat', 'perek', 'lonte',
];

function containsProfanity(text) {
    const lower = String(text || '').toLowerCase();
    return PROFANITY_WORDS.some((word) => new RegExp(`\\b${word}\\b`, 'i').test(lower));
}

function sanitizeText(text) {
    let cleaned = String(text || '').trim();
    for (const word of PROFANITY_WORDS) {
        cleaned = cleaned.replace(new RegExp(`\\b${word}\\b`, 'gi'), '*'.repeat(word.length));
    }
    return cleaned;
}

function levenshteinDistance(a, b) {
    const s = String(a || '').toLowerCase().trim();
    const t = String(b || '').toLowerCase().trim();
    if (s === t) return 0;
    if (s.length === 0) return t.length;
    if (t.length === 0) return s.length;

    const dp = Array.from({ length: s.length + 1 }, () => new Array(t.length + 1).fill(0));
    for (let i = 0; i <= s.length; i++) dp[i][0] = i;
    for (let j = 0; j <= t.length; j++) dp[0][j] = j;

    for (let i = 1; i <= s.length; i++) {
        for (let j = 1; j <= t.length; j++) {
            const cost = s[i - 1] === t[j - 1] ? 0 : 1;
            dp[i][j] = Math.min(
                dp[i - 1][j] + 1,
                dp[i][j - 1] + 1,
                dp[i - 1][j - 1] + cost
            );
        }
    }
    return dp[s.length][t.length];
}

function maskSecretWord(word) {
    const tokens = String(word || '').trim().split(/\s+/).filter(Boolean);
    return tokens.map((t) => Array.from(t).map(() => '_').join(' ')).join('   /   ');
}

// ============================================================================
// 3. Server-Side Room State, Chaos Energy, 5s Cooldown, & Max 3 Active Curses
// ============================================================================
const WORD_CATEGORIES = {
    all: [
        'Kucing Naik Helikopter', 'Sate Ayam Madura', 'Superman Terbang Miring',
        'Mobil Terbang Nyasar', 'Godzilla Makan Bakso', 'Dinosaurus Main Gitar',
        'Robot Nyapu Halaman', 'Astronot Masak Mi Instan', 'Pinguin Selancar Ombak',
        'Monyet Main Skateboard', 'Hantu Takut Kecoak', 'Kuda Nil Ikut Senam'
    ],
    meme: [
        'Kucing Oren Barbar', 'Giga Chad Senyum', 'Kapal Api Karam',
        'Sepeda Hantu Keliling', 'Mba Taylor Nangis', 'Otong Kena Razia',
        'Lord Rangga Bintang', 'Wibu Nonton Anime', 'Bocil Epep Mabar',
        'Mas Bro Santai di Kali', 'Kucing Meletus Balon', 'Pocong Lomba Lari'
    ],
    food: [
        'Nasi Padang Rendang', 'Martabak Telor Bebek', 'Es Teh Manis Jumbo',
        'Ayam Geprek Level 10', 'Pempek Kapal Selam', 'Bakso Beranak Jumbo',
        'Soto Betawi Kuah Santan', 'Pisang Goreng Keju', 'Kerak Telor Betawi',
        'Cireng Bumbu Rujak', 'Seblak Komplit Ceker', 'Mie Goreng Telur Ceplok'
    ],
    anime: [
        'Naruto Lari Pagi', 'Luffy Makan Daging', 'Pikachu Nyetrum TV',
        'Goku Kena Tilang Polisi', 'Sailor Moon Mewek', 'Gojo Satoru Buka Mata',
        'Tanjiro Tebas Bambu', 'Doraemon Makan Dorayaki', 'Shinchan Goyang Pantat',
        'Totoro Nunggu Bus', 'Levi Bersih-Bersih Debu', 'Saitama Belanja Diskon'
    ],
};
WORD_CATEGORIES.mix = WORD_CATEGORIES.all;
WORD_CATEGORIES.kuliner = WORD_CATEGORIES.food;

const CURSE_DECK = {
    earthquake: { key: 'earthquake', name: 'Gempa Bumi', icon: '🌋', energy_cost: 30, duration_seconds: 5, cooldown_ms: 5000, chaos_damage: 35 },
    invert: { key: 'invert', name: 'Kuas Mabuk', icon: '🌀', energy_cost: 45, duration_seconds: 4, cooldown_ms: 5000, chaos_damage: 50 },
    blindfold: { key: 'blindfold', name: 'Mati Lampu', icon: '💡', energy_cost: 60, duration_seconds: 3, cooldown_ms: 5000, chaos_damage: 65 },
    sticker: { key: 'sticker', name: 'Sticker Bomb', icon: '💣', energy_cost: 25, duration_seconds: 6, cooldown_ms: 5000, chaos_damage: 30 },
};

/** @type {Map<string, object>} */
const rooms = new Map();

/** @type {Map<string, { nickname: string, seenAt: number, socketId?: string }>} */
const onlineClients = new Map();

function generateRoomCode() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    for (let attempt = 0; attempt < 20; attempt++) {
        let code = '';
        for (let i = 0; i < 4; i++) {
            code += chars[Math.floor(Math.random() * chars.length)];
        }
        if (!rooms.has(code)) return code;
    }
    return 'CHAO';
}

function createRoomObject(code, hostId, hostName, optionsOrPublic = true) {
    const opts = typeof optionsOrPublic === 'object' && optionsOrPublic !== null
        ? optionsOrPublic
        : { isPublic: optionsOrPublic !== false };

    const isPublic = opts.isPublic !== false;
    const maxRounds = Math.max(1, Math.min(15, Number(opts.maxRounds) || 5));
    const roundDuration = Math.max(30, Math.min(180, Number(opts.roundDuration) || 60));
    const maxPlayers = Math.max(2, Math.min(16, Number(opts.maxPlayers) || 8));
    const chaosEnabled = opts.chaosEnabled !== false;
    const wordCategory = opts.wordCategory && WORD_CATEGORIES[opts.wordCategory] ? opts.wordCategory : 'all';
    const roomTitle = sanitizeText(opts.roomTitle || `Room [${code}]`).slice(0, 50);

    const room = {
        id: rooms.size + 1,
        code,
        roomTitle,
        isPublic,
        maxRounds,
        roundDuration,
        maxPlayers,
        chaosEnabled,
        wordCategory,
        status: 'lobby', // lobby | playing | results
        hostPlayerId: hostId,
        currentRound: 1,
        secondsLeft: roundDuration,
        artistId: hostId,
        secretWord: '',
        strokes: [],
        activeCurses: [], // { curse_type, expiresAtMs, triggered_by_name }
        sabotageCount: 0,
        guessedPlayerIds: new Set(),
        ratings: new Map(), // playerId -> stars (1..5)
        recentGuesses: [],
        emptySinceMs: null,
        players: new Map(), // playerId -> playerObj
    };

    room.players.set(hostId, {
        id: hostId,
        name: hostName,
        avatar: 'cat-neon',
        is_host: true,
        is_ready: true,
        score: 0,
        chaos_energy: 100,
        lastCurseAtMs: 0,
        turn_order: 1,
        connected: true,
    });

    rooms.set(code, room);
    return room;
}

function formatRoomForPlayer(room, requesterId = null) {
    const playersList = Array.from(room.players.values()).filter((p) => p.connected !== false);
    const artist = room.players.get(room.artistId) || playersList[0] || { id: 1, name: 'Pelukis' };
    const isArtistRequester = requesterId !== null && String(requesterId) === String(artist.id);

    const ratingsArr = Array.from(room.ratings.values());
    const avgRating = ratingsArr.length > 0
        ? Number((ratingsArr.reduce((a, b) => a + b, 0) / ratingsArr.length).toFixed(1))
        : 0.0;

    const catWords = WORD_CATEGORIES[room.wordCategory] || WORD_CATEGORIES.all;
    const shuffledCat = [...catWords].sort(() => 0.5 - Math.random());
    const suggestedWords = shuffledCat.slice(0, 3);

    return {
        id: room.id,
        code: room.code,
        status: room.status,
        room_title: room.roomTitle || `Room [${room.code}]`,
        is_public: room.isPublic,
        max_rounds: room.maxRounds,
        round_duration: room.roundDuration,
        max_players: room.maxPlayers || 8,
        chaos_enabled: room.chaosEnabled !== false,
        word_category: room.wordCategory || 'all',
        seconds_left: room.secondsLeft,
        host_player_id: room.hostPlayerId,
        host_player_name: room.players.get(room.hostPlayerId)?.name || artist.name,
        ready_count: playersList.filter((p) => p.is_ready).length,
        avg_rating: avgRating,
        votes_count: ratingsArr.length,
        suggested_words: suggestedWords,
        players: playersList.map((p) => ({
            id: p.id,
            name: p.name,
            avatar: p.avatar,
            role: String(p.id) === String(artist.id) ? 'artist' : 'saboteur',
            is_host: String(p.id) === String(room.hostPlayerId),
            is_ready: Boolean(p.is_ready),
            score: p.score,
            chaos_energy: Math.round(p.chaos_energy),
            turn_order: p.turn_order,
        })),
        strokes: room.strokes,
        recent_guesses: room.recentGuesses || [],
        round: {
            id: room.currentRound,
            round_number: room.currentRound,
            artist_id: artist.id,
            artist_name: artist.name,
            secret_word: isArtistRequester || room.status === 'results' ? (room.secretWord || '') : null,
            masked_word: room.secretWord ? maskSecretWord(room.secretWord) : '_ _ _ _ _ _',
            word_count: room.secretWord ? room.secretWord.trim().split(/\s+/).length : 1,
            status: room.secretWord ? 'drawing' : 'choosing_word',
            chaos_percentage: Math.min(100, room.sabotageCount * 25),
        },
    };
}

// ============================================================================
// 4. 1-Second Server Game Loop (Timers, Chaos Energy Regen, & 5-Min Room Cleanup)
// ============================================================================
setInterval(() => {
    const now = Date.now();

    // Clean offline heartbeats older than 25s
    for (const [token, info] of onlineClients.entries()) {
        if (now - info.seenAt > 25000) {
            onlineClients.delete(token);
        }
    }

    for (const [code, room] of rooms.entries()) {
        const activePlayers = Array.from(room.players.values()).filter((p) => p.connected !== false);

        // Delete empty rooms after 5 minutes (300,000 ms)
        if (activePlayers.length === 0) {
            if (!room.emptySinceMs) {
                room.emptySinceMs = now;
            } else if (now - room.emptySinceMs >= 300000) {
                rooms.delete(code);
            }
            continue;
        }
        room.emptySinceMs = null;

        // Prune expired active curses
        room.activeCurses = room.activeCurses.filter((c) => c.expiresAtMs > now);

        // Regenerate Chaos Energy (+3 per second, max 100) for all players
        for (const p of activePlayers) {
            if (p.chaos_energy < 100) {
                p.chaos_energy = Math.min(100, p.chaos_energy + 3);
            }
        }

        // Tick round timer if playing
        if (room.status === 'playing' && room.secretWord) {
            if (room.secondsLeft > 0) {
                room.secondsLeft--;
            } else {
                room.status = 'results';
            }
        }
    }
}, 1000);

// ============================================================================
// 5. HTTP & REST / Socket Handler
// ============================================================================
function parseJsonBody(req) {
    return new Promise((resolve) => {
        let raw = '';
        req.on('data', (chunk) => {
            raw += chunk;
            if (raw.length > 5 * 1024 * 1024) req.destroy();
        });
        req.on('end', () => {
            try {
                resolve(raw ? JSON.parse(raw) : {});
            } catch {
                resolve({});
            }
        });
    });
}

function sendJson(res, status, payload) {
    res.writeHead(status, {
        'Content-Type': 'application/json; charset=utf-8',
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': 'Content-Type, X-Guest-Token, X-Player-Nickname, X-CSRF-TOKEN',
        'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
    });
    res.end(JSON.stringify(payload));
}

function serveStaticFile(res, reqPath) {
    const safePath = path.normalize(reqPath).replace(/^(\.\.[\/\\])+/, '');
    const fullPath = path.join(__dirname, 'public', safePath);

    if (fs.existsSync(fullPath) && fs.statSync(fullPath).isFile()) {
        const ext = path.extname(fullPath).toLowerCase();
        const mimeTypes = {
            '.html': 'text/html; charset=utf-8',
            '.js': 'application/javascript; charset=utf-8',
            '.css': 'text/css; charset=utf-8',
            '.json': 'application/json; charset=utf-8',
            '.png': 'image/png',
            '.jpg': 'image/jpeg',
            '.jpeg': 'image/jpeg',
            '.gif': 'image/gif',
            '.svg': 'image/svg+xml',
            '.webp': 'image/webp',
            '.ico': 'image/x-icon',
            '.woff2': 'font/woff2',
        };
        const contentType = mimeTypes[ext] || 'application/octet-stream';
        res.writeHead(200, {
            'Content-Type': contentType,
            'Access-Control-Allow-Origin': '*',
            'Cache-Control': ext === '.html' ? 'no-cache' : 'public, max-age=31536000, immutable',
        });
        fs.createReadStream(fullPath).pipe(res);
        return true;
    }
    return false;
}

function renderIndexHtml() {
    try {
        const bladePath = path.join(__dirname, 'resources', 'views', 'draw-chaos.blade.php');
        let html = fs.readFileSync(bladePath, 'utf8');

        let cssFile = 'assets/app-D8TctOH-.css';
        let jsFile = 'assets/app-BYRSB8Dk.js';
        const manifestPath = path.join(__dirname, 'public', 'build', 'manifest.json');
        if (fs.existsSync(manifestPath)) {
            try {
                const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
                if (manifest['resources/css/app.css']?.file) {
                    cssFile = manifest['resources/css/app.css'].file;
                }
                if (manifest['resources/js/app.js']?.file) {
                    jsFile = manifest['resources/js/app.js'].file;
                }
            } catch {
                // Ignore
            }
        }

        const hallOfShameRows = sqliteDb.prepare('SELECT * FROM museum_artworks ORDER BY star_rating DESC LIMIT 6').all().map((r) => ({
            id: r.id,
            sarcastic_title: r.title,
            museum_plaque: `Karya: @${r.artist_name}`,
            chaos_rating: r.chaos_rating,
            star_rating: r.star_rating,
            image_data_url: r.image_data_url,
        }));

        html = html.replace(/@csrf/g, '');
        html = html.replace(/\{\{\s*csrf_token\(\)\s*\}\}/g, 'real_csrf_drawchaos_token');
        html = html.replace(
            /@vite\(\['resources\/css\/app\.css',\s*'resources\/js\/app\.js'\]\)/g,
            `<link rel="stylesheet" href="/build/${cssFile}"><script type="module" src="/build/${jsFile}"></script>`
        );
        html = html.replace(/@json\(\$deckCatalog\)/g, JSON.stringify(CURSE_DECK));
        html = html.replace(/@json\(\$hallOfShame\)/g, JSON.stringify(hallOfShameRows));
        html = html.replace(/@json\(\$initialRoom\)/g, 'null');
        html = html.replace(/@json\(\$onlineCount\s*\?\?\s*1\)/g, String(Math.max(1, onlineClients.size)));
        html = html.replace(/@json\(\$guestPlayer\s*\?\?\s*null\)/g, 'null');
        html = html.replace(/@json\(\$reverbConfig\s*\?\?\s*\[\]\)/g, 'null');

        return html;
    } catch (err) {
        return `<!DOCTYPE html><html><body><h1>Error rendering CORET! CHAOS: ${err.message}</h1></body></html>`;
    }
}

const server = http.createServer(async (req, res) => {
    if (req.method === 'OPTIONS') {
        return sendJson(res, 200, { status: 'ok' });
    }

    const parsedUrl = new URL(req.url || '/', `http://${req.headers.host || 'localhost'}`);
    const pathname = parsedUrl.pathname;

    // 0. Serve static assets from /public (e.g. /build/assets/...)
    if (pathname.startsWith('/build/') || pathname.startsWith('/assets/') || pathname === '/favicon.ico') {
        if (serveStaticFile(res, pathname)) {
            return;
        }
    }

    // 0. Serve full game page on any non-API route (SPA fallback)
    if (!pathname.startsWith('/api/')) {
        const html = renderIndexHtml();
        res.writeHead(200, {
            'Content-Type': 'text/html; charset=utf-8',
            'Access-Control-Allow-Origin': '*',
        });
        res.end(html);
        return;
    }

    const guestToken = String(req.headers['x-guest-token'] || req.socket.remoteAddress || 'guest_default');
    const headerNick = sanitizeText(req.headers['x-player-nickname'] || 'SenimanRusuh_89');
    onlineClients.set(guestToken, { nickname: headerNick, seenAt: Date.now() });
    const onlineCount = Math.max(1, onlineClients.size);

    // 1. Ping & Real Online Count
    if (pathname === '/api/ping' && req.method === 'GET') {
        return sendJson(res, 200, {
            status: 'ok',
            online_count: onlineCount,
            server_time_ms: Date.now(),
        });
    }

    // 2. Quick Play
    if (pathname === '/api/rooms/quick-play' && req.method === 'POST') {
        const body = await parseJsonBody(req);
        const playerName = sanitizeText(body.player_name || headerNick || 'SenimanRusuh_89');

        for (const room of rooms.values()) {
            const activeCount = Array.from(room.players.values()).filter((p) => p.connected !== false).length;
            if (room.isPublic && room.status === 'lobby' && activeCount > 0 && activeCount < 8) {
                room.players.set(guestToken, {
                    id: guestToken,
                    name: playerName,
                    avatar: 'dino-glitch',
                    is_host: false,
                    is_ready: true,
                    score: room.players.get(guestToken)?.score || 0,
                    chaos_energy: 100,
                    lastCurseAtMs: 0,
                    turn_order: room.players.size + 1,
                    connected: true,
                });
                return sendJson(res, 200, {
                    status: 'ok',
                    room: formatRoomForPlayer(room, guestToken),
                });
            }
        }

        const newCode = generateRoomCode();
        const newRoom = createRoomObject(newCode, guestToken, playerName, true);
        return sendJson(res, 201, {
            status: 'ok',
            room: formatRoomForPlayer(newRoom, guestToken),
        });
    }

    // 3. Create Room
    if (pathname === '/api/rooms' && req.method === 'POST') {
        const body = await parseJsonBody(req);
        const hostName = sanitizeText(body.host_name || headerNick || 'SenimanRusuh_89');
        const customCode = body.custom_code
            ? String(body.custom_code).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 4)
            : '';
        const code = (customCode && customCode.length >= 3 && !rooms.has(customCode))
            ? customCode
            : generateRoomCode();

        const roomOptions = {
            isPublic: body.is_public !== false,
            maxRounds: Number(body.max_rounds) || 5,
            roundDuration: Number(body.round_duration) || 60,
            maxPlayers: Number(body.max_players) || 8,
            chaosEnabled: body.chaos_enabled !== false,
            wordCategory: body.word_category || 'all',
            roomTitle: body.room_title || '',
        };

        const room = createRoomObject(code, guestToken, hostName, roomOptions);
        if (body.secret_word) {
            room.secretWord = sanitizeText(body.secret_word).slice(0, 40);
        }
        return sendJson(res, 201, {
            status: 'ok',
            room: formatRoomForPlayer(room, guestToken),
        });
    }

    // 4. Join Room
    if (pathname === '/api/rooms/join' && req.method === 'POST') {
        const body = await parseJsonBody(req);
        const code = String(body.code || '').toUpperCase().slice(0, 4);
        const playerName = sanitizeText(body.player_name || headerNick || 'SenimanRusuh_89');

        let room = rooms.get(code);
        if (!room) {
            room = createRoomObject(code, guestToken, playerName, true);
        } else {
            const activeCount = Array.from(room.players.values()).filter(
                (p) => p.connected !== false && p.id !== guestToken
            ).length;
            const maxCapacity = room.maxPlayers || 8;
            if (activeCount >= maxCapacity) {
                return sendJson(res, 422, { status: 'error', message: `Room sudah penuh (maksimal ${maxCapacity} pemain)!` });
            }
            const existing = room.players.get(guestToken);
            room.players.set(guestToken, {
                id: guestToken,
                name: playerName,
                avatar: existing?.avatar || 'raccoon-cyber',
                is_host: room.hostPlayerId === guestToken,
                is_ready: true,
                score: existing?.score || 0,
                chaos_energy: existing?.chaos_energy ?? 100,
                lastCurseAtMs: existing?.lastCurseAtMs || 0,
                turn_order: existing?.turn_order || (room.players.size + 1),
                connected: true,
            });
        }

        return sendJson(res, 200, {
            status: 'ok',
            room: formatRoomForPlayer(room, guestToken),
        });
    }

    // Room-scoped endpoints: /api/rooms/:code/...
    const roomMatch = pathname.match(/^\/api\/rooms\/([A-Za-z0-9]{4})(?:\/([a-z-]+))?$/);
    if (roomMatch) {
        const code = roomMatch[1].toUpperCase();
        const action = roomMatch[2] || 'state';
        const room = rooms.get(code);

        if (!room) {
            return sendJson(res, 404, { status: 'error', message: 'Room tidak ditemukan.' });
        }

        if (action === 'state' && req.method === 'GET') {
            return sendJson(res, 200, {
                status: 'ok',
                online_count: onlineCount,
                room: formatRoomForPlayer(room, guestToken),
            });
        }

        const body = req.method === 'POST' ? await parseJsonBody(req) : {};

        if (action === 'settings' && req.method === 'POST') {
            if (String(room.hostPlayerId) !== String(guestToken)) {
                return sendJson(res, 403, { status: 'error', message: 'Hanya Host yang berhak mengubah pengaturan room!' });
            }
            if (room.status !== 'lobby') {
                return sendJson(res, 400, { status: 'error', message: 'Pengaturan room hanya dapat diubah saat berada di Lobby!' });
            }
            if (typeof body.is_public === 'boolean') {
                room.isPublic = body.is_public;
            }
            if (body.max_rounds) {
                room.maxRounds = Math.max(1, Math.min(15, Number(body.max_rounds) || 5));
            }
            if (body.round_duration) {
                room.roundDuration = Math.max(30, Math.min(180, Number(body.round_duration) || 60));
                room.secondsLeft = room.roundDuration;
            }
            if (body.max_players) {
                room.maxPlayers = Math.max(2, Math.min(16, Number(body.max_players) || 8));
            }
            if (typeof body.chaos_enabled === 'boolean') {
                room.chaosEnabled = body.chaos_enabled;
            }
            if (body.word_category && WORD_CATEGORIES[body.word_category]) {
                room.wordCategory = body.word_category;
            }
            if (body.room_title) {
                room.roomTitle = sanitizeText(body.room_title).slice(0, 50);
            }

            const updatedSnapshot = formatRoomForPlayer(room, guestToken);
            io.to(`room:${code}`).emit('room_settings_updated', updatedSnapshot);
            io.to(`room:${code}`).emit('room_state', updatedSnapshot);

            return sendJson(res, 200, {
                status: 'ok',
                message: 'Pengaturan room berhasil disimpan!',
                room: updatedSnapshot,
            });
        }

        if (action === 'ready' && req.method === 'POST') {
            const p = room.players.get(guestToken);
            if (p) {
                p.is_ready = !p.is_ready;
            }
            return sendJson(res, 200, { status: 'ok', room: formatRoomForPlayer(room, guestToken) });
        }

        if (action === 'start' && req.method === 'POST') {
            if (String(room.hostPlayerId) !== String(guestToken)) {
                return sendJson(res, 403, {
                    status: 'error',
                    message: '👑 Hanya Host yang berhak memulai permainan!',
                });
            }
            const readyPlayers = Array.from(room.players.values()).filter((p) => p.connected !== false && p.is_ready);
            if (readyPlayers.length < 2) {
                return sendJson(res, 422, {
                    status: 'error',
                    message: 'Minimal 2 pemain harus berstatus SIAP untuk memulai permainan!',
                });
            }
            room.status = 'playing';
            room.secondsLeft = room.roundDuration;
            room.strokes = [];
            room.sabotageCount = 0;
            room.guessedPlayerIds.clear();
            room.artistId = readyPlayers[0].id;

            const updatedSnapshot = formatRoomForPlayer(room, guestToken);
            io.to(`room:${code}`).emit('game_started', updatedSnapshot);
            io.to(`room:${code}`).emit('room_state', updatedSnapshot);

            return sendJson(res, 200, { status: 'ok', room: updatedSnapshot });
        }

        if (action === 'set-word' && req.method === 'POST') {
            const rawWord = String(body.secret_word || '').trim();
            if (rawWord.length < 2 || rawWord.length > 40) {
                return sendJson(res, 422, { status: 'error', message: 'Kata rahasia harus 2-40 karakter!' });
            }
            if (containsProfanity(rawWord)) {
                return sendJson(res, 422, { status: 'error', message: 'Kata rahasia mengandung kata kasar yang dilarang!' });
            }
            room.secretWord = rawWord;
            room.secondsLeft = room.roundDuration;
            room.strokes = [];
            return sendJson(res, 200, { status: 'ok', room: formatRoomForPlayer(room, guestToken) });
        }

        if (action === 'strokes' && req.method === 'POST') {
            if (String(room.artistId) !== String(guestToken) && room.players.size > 1) {
                return sendJson(res, 403, { status: 'error', message: 'Hanya Pelukis yang boleh menggambar!' });
            }
            if (body.stroke_type === 'clear') {
                room.strokes = [];
            } else if (body.stroke_type === 'undo') {
                room.strokes.pop();
            } else {
                room.strokes.push({
                    stroke_type: 'path',
                    color: body.color || '#14121D',
                    size: Number(body.brush_size || 6),
                    points: Array.isArray(body.points) ? body.points : [],
                });
            }
            return sendJson(res, 200, { status: 'ok', stroke: body });
        }

        if (action === 'sabotage' && req.method === 'POST') {
            if (room.chaosEnabled === false) {
                return sendJson(res, 400, { status: 'error', message: 'Mode Santai aktif: Sabotase dinonaktifkan oleh Host di room ini!' });
            }
            const curseKey = String(body.curse_type || '');
            const curse = CURSE_DECK[curseKey];
            if (!curse) {
                return sendJson(res, 422, { status: 'error', message: 'Kartu kutukan tidak valid.' });
            }
            const caster = room.players.get(guestToken);
            if (!caster) {
                return sendJson(res, 403, { status: 'error', message: 'Pemain tidak terdaftar di room.' });
            }
            const now = Date.now();
            room.activeCurses = room.activeCurses.filter((c) => c.expiresAtMs > now);

            if (room.activeCurses.length >= 3) {
                return sendJson(res, 422, { status: 'error', message: 'Maksimal 3 efek aktif bersamaan!' });
            }
            if (now - (caster.lastCurseAtMs || 0) < 5000) {
                return sendJson(res, 422, { status: 'error', message: 'Cooldown 5 detik belum selesai!' });
            }
            if (caster.chaos_energy < curse.energy_cost) {
                return sendJson(res, 422, { status: 'error', message: 'Chaos Energy tidak cukup!' });
            }

            caster.chaos_energy -= curse.energy_cost;
            caster.lastCurseAtMs = now;
            room.sabotageCount++;

            const activeEffect = {
                curse_type: curse.key,
                curse_name: curse.name,
                icon: curse.icon,
                energy_cost: curse.energy_cost,
                remaining_energy: Math.round(caster.chaos_energy),
                duration_seconds: curse.duration_seconds,
                cooldown_seconds: 5,
                triggered_by_name: caster.name,
                expiresAtMs: now + curse.duration_seconds * 1000,
            };
            room.activeCurses.push(activeEffect);

            return sendJson(res, 200, { status: 'ok', sabotage: activeEffect });
        }

        if (action === 'guess' && req.method === 'POST') {
            const guessText = sanitizeText(body.guess_text || '');
            const secret = room.secretWord || 'Kucing Naik Helikopter';
            const dist = levenshteinDistance(guessText, secret);
            const guesser = room.players.get(guestToken) || { name: headerNick, score: 0 };
            const artist = room.players.get(room.artistId);
            const isArtist = String(guestToken) === String(room.artistId);

            let guessPayload;
            if (isArtist) {
                guessPayload = {
                    id: Date.now() + Math.random(),
                    player_name: guesser.name,
                    guess_text: guessText,
                    public_display_text: guessText,
                    status: 'artist_chat',
                    match_status: 'artist_chat',
                    is_artist: true,
                    is_correct: false,
                    guesser_points: 0,
                    artist_bonus_points: 0,
                };
            } else if (dist === 0) {
                const points = Math.max(150, 350 - room.guessedPlayerIds.size * 50);
                const artistBonus = 120 + room.sabotageCount * 25;
                if (!room.guessedPlayerIds.has(guestToken)) {
                    room.guessedPlayerIds.add(guestToken);
                    guesser.score = (guesser.score || 0) + points;
                    if (artist) {
                        artist.score = (artist.score || 0) + artistBonus;
                    }
                }
                guessPayload = {
                    id: Date.now() + Math.random(),
                    player_name: guesser.name,
                    guess_text: guessText,
                    public_display_text: '🎉 [MENJAWAB BENAR!]',
                    status: 'exact',
                    match_status: 'exact',
                    is_correct: true,
                    levenshtein_distance: 0,
                    is_private_hint: false,
                    private_hint: null,
                    guesser_points: points,
                    artist_bonus_points: artistBonus,
                };
            } else if (dist <= 2) {
                guessPayload = {
                    id: Date.now() + Math.random(),
                    player_name: guesser.name,
                    guess_text: guessText,
                    public_display_text: '*** (Hampir Benar!)',
                    status: 'close',
                    match_status: 'close',
                    is_correct: false,
                    levenshtein_distance: dist,
                    is_private_hint: true,
                    private_hint: '🔥 DIKIT LAGI BANG! Dikit lagi / Typo!',
                    guesser_points: 0,
                    artist_bonus_points: 0,
                };
            } else {
                guessPayload = {
                    id: Date.now() + Math.random(),
                    player_name: guesser.name,
                    guess_text: guessText,
                    public_display_text: guessText,
                    status: 'miss',
                    match_status: 'miss',
                    is_correct: false,
                    levenshtein_distance: dist,
                    is_private_hint: false,
                    private_hint: null,
                    guesser_points: 0,
                    artist_bonus_points: 0,
                };
            }

            if (!room.recentGuesses) {
                room.recentGuesses = [];
            }
            room.recentGuesses.push(guessPayload);
            if (room.recentGuesses.length > 50) {
                room.recentGuesses.shift();
            }

            io.to(`room:${code}`).emit('guess_result', guessPayload);

            return sendJson(res, 200, {
                status: 'ok',
                guess: guessPayload,
            });
        }

        if (action === 'rate' && req.method === 'POST') {
            const stars = Math.max(1, Math.min(5, Number(body.stars || 5)));
            room.ratings.set(guestToken, stars);
            const vals = Array.from(room.ratings.values());
            const avg = Number((vals.reduce((a, b) => a + b, 0) / vals.length).toFixed(1));
            return sendJson(res, 200, {
                status: 'ok',
                stars,
                avg_rating: avg,
                votes_count: vals.length,
            });
        }

        if (action === 'rotate-round' && req.method === 'POST') {
            const activePlayers = Array.from(room.players.values()).filter((p) => p.connected !== false);
            const curIdx = activePlayers.findIndex((p) => String(p.id) === String(room.artistId));
            const nextPlayer = activePlayers[(curIdx + 1) % Math.max(1, activePlayers.length)] || activePlayers[0];
            room.artistId = nextPlayer.id;
            room.currentRound = (room.currentRound % room.maxRounds) + 1;
            room.strokes = [];
            room.sabotageCount = 0;
            room.secondsLeft = room.roundDuration;
            room.status = 'playing';

            return sendJson(res, 200, {
                status: 'ok',
                new_artist: nextPlayer.name,
                room: formatRoomForPlayer(room, guestToken),
            });
        }

        if (action === 'export-gif' && req.method === 'POST') {
            const artist = room.players.get(room.artistId) || { name: headerNick };
            const vals = Array.from(room.ratings.values());
            const avgRating = vals.length > 0
                ? Number((vals.reduce((a, b) => a + b, 0) / vals.length).toFixed(1))
                : 4.5;
            const chaosPct = Math.min(100, room.sabotageCount * 25);
            const title = sanitizeText(body.custom_title || `Karya: ${room.secretWord || 'Misteri'}`);

            const stmt = sqliteDb.prepare(`
                INSERT INTO museum_artworks (title, secret_word, artist_name, image_data_url, chaos_rating, star_rating, votes_count, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            `);
            const result = stmt.run(
                title,
                room.secretWord || 'Lukisan Bebas',
                artist.name,
                String(body.image_data_url || ''),
                chaosPct,
                avgRating,
                Math.max(1, vals.length),
                Date.now()
            );

            return sendJson(res, 200, {
                status: 'ok',
                entry: {
                    id: Number(result.lastInsertRowid),
                    sarcastic_title: title,
                    museum_plaque: `Karya @${artist.name}`,
                    artist_name: artist.name,
                    chaos_rating: chaosPct,
                    star_rating: avgRating,
                },
            });
        }

        if (action === 'leave' && req.method === 'POST') {
            const leaving = room.players.get(guestToken);
            if (leaving) {
                leaving.connected = false;
                leaving.is_ready = false;
            }
            const remaining = Array.from(room.players.values()).filter((p) => p.connected !== false);
            if (remaining.length > 0 && String(room.artistId) === String(guestToken)) {
                room.artistId = remaining[0].id;
            }
            return sendJson(res, 200, { status: 'ok', room: formatRoomForPlayer(room, guestToken) });
        }
    }

    // 5. Museum SQLite List with Filters (curator | favorite | newest)
    if (pathname === '/api/museum' && req.method === 'GET') {
        const filter = parsedUrl.searchParams.get('filter') || 'newest';
        let query = 'SELECT * FROM museum_artworks ORDER BY created_at DESC LIMIT 24';
        if (filter === 'curator') {
            query = 'SELECT * FROM museum_artworks WHERE chaos_rating >= 50 OR star_rating >= 4.0 ORDER BY star_rating DESC, chaos_rating DESC LIMIT 24';
        } else if (filter === 'favorite') {
            query = 'SELECT * FROM museum_artworks ORDER BY star_rating DESC, votes_count DESC LIMIT 24';
        }
        const rows = sqliteDb.prepare(query).all();
        const items = rows.map((r) => ({
            id: r.id,
            exhibit_number: `#${String(r.id).padStart(2, '0')}`,
            sarcastic_title: r.title,
            museum_plaque: `Karya: @${r.artist_name} (${r.secret_word})`,
            secret_word: r.secret_word,
            artist_name: r.artist_name,
            chaos_rating: r.chaos_rating,
            star_rating: r.star_rating,
            votes_count: r.votes_count,
            image_data_url: r.image_data_url,
        }));
        return sendJson(res, 200, { status: 'ok', filter, items });
    }

    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ status: 'not_found' }));
});

if (process.argv[1] && path.resolve(process.argv[1]) === __filename) {
    server.listen(PORT, HOST, () => {
        console.log(`=============================================================`);
        console.log(`🚀 CORET! CHAOS Real Multiplayer Server Active`);
        console.log(`👉 Local:   http://localhost:${PORT}`);
        console.log(`👉 LAN/HP:  http://0.0.0.0:${PORT}`);
        console.log(`=============================================================`);
    });
}

export default server;
