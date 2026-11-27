<?php

namespace App\Http\Controllers;

use App\Events\RoundStateChanged;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Lobby;
use App\Models\LobbyMessage;
use App\Models\LobbyPlayer;
use App\Models\Room;
use App\Models\RoomPlayer;
use App\Models\Round;
use App\Models\Stroke;
use App\Models\Word;
use App\Services\GameRoundService;
use App\Services\PlayerIdentityService;
use App\Services\RoomSnapshotService;
use App\Services\WordBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class GameRoomController extends Controller
{
    public function __construct(
        public WordBankService $wordBank,
        public PlayerIdentityService $playerIdentity,
        public RoomSnapshotService $roomSnapshot,
        public GameRoundService $gameRound,
    ) {}

    /**
     * List real open public lobbies (`GET /api/rooms/public`).
     */
    public function publicRooms(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'rooms' => $this->roomSnapshot->loadPublicRoomsSummary(),
        ]);
    }

    /**
     * Create a new multiplayer room with ONLY the real host player (no dummy bots).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'host_name' => ['nullable', 'string', 'max:50'],
            'custom_code' => ['nullable', 'string', 'min:3', 'max:6'],
            'secret_word' => ['nullable', 'string', 'max:255'],
            'is_public' => ['nullable', 'boolean'],
            'max_rounds' => ['nullable', 'integer', 'min:1', 'max:15'],
            'round_duration' => ['nullable', 'integer', 'min:30', 'max:180'],
            'max_players' => ['nullable', 'integer', 'min:2', 'max:16'],
            'chaos_enabled' => ['nullable', 'boolean'],
            'chaos_mode' => ['nullable', 'string', 'in:classic,chill,extreme'],
            'word_category' => ['nullable', 'string', 'max:50'],
            'room_title' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'string', 'max:50'],
        ]);

        $guestUser = $request->attributes->get('guest_player');
        $hostName = $this->wordBank->sanitizeText(
            trim((string) ($validated['host_name'] ?? $guestUser->nickname ?? 'SenimanRusuh_89')) ?: 'SenimanRusuh_89'
        );
        $rawCode = isset($validated['custom_code'])
            ? strtoupper($validated['custom_code'])
            : $this->roomSnapshot->generateUniqueRoomCode();
        $code = substr($rawCode, 0, 4);

        $secretPhrase = isset($validated['secret_word']) && trim((string) $validated['secret_word']) !== ''
            ? $this->wordBank->sanitizeSecretWord((string) $validated['secret_word'])
            : 'Kucing Naik Helikopter';

        $chaosMode = (string) ($validated['chaos_mode'] ?? (($validated['chaos_enabled'] ?? true) ? 'classic' : 'chill'));
        $chaosEnabled = $chaosMode !== 'chill' && (bool) ($validated['chaos_enabled'] ?? true);

        try {
            $this->wordBank->ensureBaseWordsAndCursesExist();

            $hostAvatar = (string) ($validated['avatar'] ?? $guestUser->avatar ?? 'cat-neon');
            $hostPlayer = $this->playerIdentity->resolveRequestPlayer($request, $hostName, $hostAvatar);
            $welcomeLobbyMessage = [
                'id' => 'lc_init_'.Str::random(8),
                'player_id' => $hostPlayer->id,
                'player_name' => 'SYSTEM',
                'avatar' => 'cat-neon',
                'is_host' => false,
                'is_system' => true,
                'message' => "Lobby #{$code} berhasil dibuat oleh {$hostPlayer->nickname}!",
                'sent_at' => now()->format('H:i'),
            ];

            $room = Room::updateOrCreate(
                ['code' => $code],
                [
                    'host_player_id' => $hostPlayer->id,
                    'status' => 'lobby',
                    'settings' => [
                        'is_public' => (bool) ($validated['is_public'] ?? true),
                        'max_players' => (int) ($validated['max_players'] ?? 8),
                        'max_rounds' => (int) ($validated['max_rounds'] ?? 5),
                        'round_duration' => (int) ($validated['round_duration'] ?? 60),
                        'chaos_enabled' => $chaosEnabled,
                        'chaos_mode' => $chaosMode,
                        'word_category' => (string) ($validated['word_category'] ?? 'all'),
                        'room_title' => (string) ($validated['room_title'] ?? "Room [{$code}]"),
                        'empty_since_ts' => null,
                        'ratings' => [],
                        'lobby_chat' => [$welcomeLobbyMessage],
                    ],
                ]
            );

            RoomPlayer::where('room_id', $room->id)->delete();
            RoomPlayer::create([
                'room_id' => $room->id,
                'player_id' => $hostPlayer->id,
                'is_ready' => true,
                'joined_at' => now(),
                'left_at' => null,
            ]);

            $game = Game::updateOrCreate(
                ['room_id' => $room->id, 'status' => 'playing'],
                ['started_at' => now()]
            );

            GamePlayer::where('game_id', $game->id)->delete();
            GamePlayer::create([
                'game_id' => $game->id,
                'player_id' => $hostPlayer->id,
                'turn_order' => 1,
                'total_score' => 0,
                'chaos_energy' => 100,
            ]);

            $word = Word::firstOrCreate(
                ['phrase' => $secretPhrase],
                ['category' => 'Custom', 'difficulty' => 'medium']
            );

            $round = Round::updateOrCreate(
                [
                    'game_id' => $game->id,
                    'round_number' => 1,
                ],
                [
                    'artist_player_id' => $hostPlayer->id,
                    'word_id' => $word->id,
                    'status' => 'drawing',
                    'started_at' => now(),
                ]
            );

            $snapshot = $this->roomSnapshot->formatRoomSnapshot($room, $game, $round, $hostPlayer->id);
        } catch (Throwable) {
            $snapshot = $this->roomSnapshot->buildFallbackRoomSnapshot($code, $hostName, $secretPhrase);
        }

        event(new RoundStateChanged($code, $snapshot));

        return response()->json([
            'status' => 'ok',
            'room' => $snapshot,
        ], 201);
    }

    /**
     * Quick Play: Find a public room in `'lobby'` status below its `max_players` capacity, or create a new one.
     */
    public function quickPlay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'player_name' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'string', 'max:50'],
        ]);

        $guestUser = $request->attributes->get('guest_player');
        $playerName = $this->wordBank->sanitizeText(
            trim((string) ($validated['player_name'] ?? $guestUser->nickname ?? 'SenimanRusuh_89')) ?: 'SenimanRusuh_89'
        );

        try {
            $openRooms = Room::where('status', 'lobby')->latest('updated_at')->get();
            foreach ($openRooms as $candidate) {
                $settings = is_array($candidate->settings) ? $candidate->settings : [];
                if (array_key_exists('is_public', $settings) && ! $settings['is_public']) {
                    continue;
                }
                $maxPlayers = max(2, min(16, (int) ($settings['max_players'] ?? 8)));
                $activeCount = $candidate->roomPlayers()->whereNull('left_at')->count();
                if ($activeCount > 0 && $activeCount < $maxPlayers) {
                    $request->merge([
                        'code' => $candidate->code,
                        'player_name' => $playerName,
                    ]);

                    return $this->join($request);
                }
            }
        } catch (Throwable) {
            // Fall through to creating a new room
        }

        $request->merge([
            'host_name' => $playerName,
            'is_public' => true,
        ]);

        return $this->store($request);
    }

    /**
     * Join an existing room by 4-char code (`rooms.code`).
     */
    public function join(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'min:4', 'max:5'],
            'player_name' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'string', 'max:50'],
        ]);

        $code = substr(strtoupper(trim($validated['code'])), 0, 4);
        $guestUser = $request->attributes->get('guest_player');
        $playerName = $this->wordBank->sanitizeText(
            trim((string) ($validated['player_name'] ?? $guestUser->nickname ?? 'SenimanRusuh_89')) ?: 'SenimanRusuh_89'
        );
        $allowedAvatars = $this->playerIdentity->allowedAvatars();
        $preferredAvatar = in_array($validated['avatar'] ?? '', $allowedAvatars, true)
            ? (string) $validated['avatar']
            : (string) ($guestUser->avatar ?? 'dino-glitch');

        try {
            $this->wordBank->ensureBaseWordsAndCursesExist();

            $joiningPlayer = $this->playerIdentity->resolveRequestPlayer($request, $playerName, $preferredAvatar);
            if ($joiningPlayer->avatar !== $preferredAvatar && isset($validated['avatar'])) {
                $joiningPlayer->update(['avatar' => $preferredAvatar]);
            }

            $room = Room::where('code', $code)->first();
            if (! $room) {
                $room = Room::create([
                    'code' => $code,
                    'host_player_id' => $joiningPlayer->id,
                    'status' => 'lobby',
                    'settings' => [
                        'is_public' => true,
                        'max_players' => 8,
                        'max_rounds' => 5,
                        'round_duration' => 60,
                        'chaos_enabled' => true,
                        'chaos_mode' => 'classic',
                        'empty_since_ts' => null,
                        'ratings' => [],
                        'lobby_chat' => [[
                            'id' => 'lc_init_'.Str::random(8),
                            'player_id' => $joiningPlayer->id,
                            'player_name' => 'SYSTEM',
                            'avatar' => 'cat-neon',
                            'is_host' => false,
                            'is_system' => true,
                            'message' => "Lobby #{$code} dibuat oleh {$joiningPlayer->nickname}!",
                            'sent_at' => now()->format('H:i'),
                        ]],
                    ],
                ]);
            }

            $maxCapacity = max(2, min(16, (int) ($room->settings['max_players'] ?? 8)));
            $activeCount = $room->roomPlayers()
                ->whereNull('left_at')
                ->where('player_id', '!=', $joiningPlayer->id)
                ->count();

            if ($activeCount >= $maxCapacity) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Room sudah penuh (maksimal {$maxCapacity} pemain)!",
                ], 422);
            }

            $wasAlreadyMember = RoomPlayer::where('room_id', $room->id)
                ->where('player_id', $joiningPlayer->id)
                ->whereNull('left_at')
                ->exists();

            RoomPlayer::updateOrCreate(
                ['room_id' => $room->id, 'player_id' => $joiningPlayer->id],
                ['is_ready' => true, 'joined_at' => now(), 'left_at' => null]
            );

            $settings = is_array($room->settings) ? $room->settings : [];
            $settings['empty_since_ts'] = null;
            if (! $wasAlreadyMember) {
                $lobbyChat = is_array($settings['lobby_chat'] ?? null) ? $settings['lobby_chat'] : [];
                $lobbyChat[] = [
                    'id' => 'lc_join_'.Str::random(8),
                    'player_id' => $joiningPlayer->id,
                    'player_name' => 'SYSTEM',
                    'avatar' => $joiningPlayer->avatar ?? $preferredAvatar,
                    'is_host' => false,
                    'is_system' => true,
                    'message' => "{$joiningPlayer->nickname} masuk ke ruang tunggu lobby.",
                    'sent_at' => now()->format('H:i'),
                ];
                $settings['lobby_chat'] = array_slice($lobbyChat, -35);
            }
            $room->update(['settings' => $settings]);

            $game = $room->activeGame ?? Game::create([
                'room_id' => $room->id,
                'status' => 'playing',
                'started_at' => now(),
            ]);

            $nextTurnOrder = ((int) $game->gamePlayers()->max('turn_order')) + 1;
            GamePlayer::firstOrCreate(
                ['game_id' => $game->id, 'player_id' => $joiningPlayer->id],
                [
                    'turn_order' => $nextTurnOrder,
                    'total_score' => 0,
                    'chaos_energy' => 100,
                ]
            );

            $word = Word::first() ?? Word::create([
                'phrase' => 'Kucing Naik Helikopter',
                'category' => 'Custom',
                'difficulty' => 'medium',
            ]);

            $round = $game->activeRound ?? Round::create([
                'game_id' => $game->id,
                'artist_player_id' => $room->host_player_id,
                'word_id' => $word->id,
                'round_number' => 1,
                'status' => 'drawing',
                'started_at' => now(),
            ]);

            $snapshot = $this->roomSnapshot->formatRoomSnapshot($room, $game, $round, $joiningPlayer->id);
        } catch (Throwable) {
            $snapshot = $this->roomSnapshot->buildFallbackRoomSnapshot($code, $playerName, 'Kucing Naik Helikopter');
        }

        event(new RoundStateChanged($code, $snapshot));

        return response()->json([
            'status' => 'ok',
            'joined_player' => $playerName,
            'room' => $snapshot,
        ]);
    }

    /**
     * Get the current synchronized state of a room.
     * Secret word is ONLY revealed if the requester is the active Artist.
     */
    public function state(Request $request, string $code): JsonResponse
    {
        $cleanCode = substr(strtoupper(trim($code)), 0, 4);
        $guestUser = $request->attributes->get('guest_player');
        $requesterId = isset($guestUser->id) ? (int) $guestUser->id : null;

        $token = (string) ($request->header('X-Guest-Token') ?? $guestUser->raw_token ?? $request->ip());
        $nickname = (string) ($request->header('X-Player-Nickname') ?? $guestUser->nickname ?? 'Player');
        $onlineCount = $this->playerIdentity->touchOnlineHeartbeat($token, $nickname);

        try {
            $room = Room::where('code', $cleanCode)->first();
            if ($room) {
                $game = $room->activeGame;
                $round = $game?->activeRound;

                return response()->json([
                    'status' => 'ok',
                    'online_count' => $onlineCount,
                    'room' => $this->roomSnapshot->formatRoomSnapshot($room, $game, $round, $requesterId),
                ]);
            }
        } catch (Throwable) {
            // Fall through
        }

        return response()->json([
            'status' => 'not_found',
            'online_count' => $onlineCount,
            'room' => null,
        ], 404);
    }

    /**
     * Send a real-time chat message in the waiting Lobby (`POST /api/rooms/{code}/lobby-chat`).
     */
    public function lobbyChat(Request $request, string $code): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:200'],
            'player_name' => ['nullable', 'string', 'max:50'],
        ]);

        $cleanCode = substr(strtoupper(trim($code)), 0, 4);
        $room = Room::where('code', $cleanCode)->first();
        if (! $room) {
            return response()->json(['status' => 'error', 'message' => 'Room tidak ditemukan.'], 404);
        }

        $player = $this->playerIdentity->resolveRequestPlayer($request, (string) ($validated['player_name'] ?? 'Player'));
        $cleanMessage = $this->wordBank->sanitizeText(trim($validated['message']));
        if ($cleanMessage === '') {
            return response()->json(['status' => 'error', 'message' => 'Pesan tidak boleh kosong.'], 422);
        }

        $chatEntry = [
            'id' => 'lc_'.Str::random(10),
            'player_id' => $player->id,
            'player_name' => $player->nickname,
            'avatar' => $player->avatar ?? 'cat-neon',
            'is_host' => (int) $player->id === (int) $room->host_player_id,
            'is_system' => false,
            'message' => $cleanMessage,
            'sent_at' => now()->format('H:i'),
        ];

        $settings = is_array($room->settings) ? $room->settings : [];
        $lobbyChat = is_array($settings['lobby_chat'] ?? null) ? $settings['lobby_chat'] : [];
        $lobbyChat[] = $chatEntry;
        if (count($lobbyChat) > 35) {
            $lobbyChat = array_slice($lobbyChat, -35);
        }
        $settings['lobby_chat'] = array_values($lobbyChat);
        $room->update(['settings' => $settings]);

        $game = $room->activeGame;
        $snapshot = $this->roomSnapshot->formatRoomSnapshot($room, $game, $game?->activeRound, $player->id);
        event(new RoundStateChanged($cleanCode, $snapshot));

        return response()->json([
            'status' => 'ok',
            'chat_message' => $chatEntry,
            'room' => $snapshot,
        ]);
    }

    /**
     * Update room settings (only accessible by Host while in Lobby).
     */
    public function updateSettings(Request $request, string $code): JsonResponse
    {
        $cleanCode = substr(strtoupper(trim($code)), 0, 4);
        $room = Room::where('code', $cleanCode)->first();
        if (! $room) {
            return response()->json(['status' => 'error', 'message' => 'Room tidak ditemukan.'], 404);
        }

        $guestUser = $request->attributes->get('guest_player');
        if ($guestUser && $room->host_player_id !== $guestUser->id) {
            return response()->json(['status' => 'error', 'message' => 'Hanya Host yang bisa mengubah pengaturan room!'], 403);
        }

        if ($room->status !== 'lobby') {
            return response()->json(['status' => 'error', 'message' => 'Pengaturan room hanya dapat diubah saat berada di Lobby!'], 400);
        }

        $validated = $request->validate([
            'is_public' => ['nullable', 'boolean'],
            'max_rounds' => ['nullable', 'integer', 'min:1', 'max:15'],
            'round_duration' => ['nullable', 'integer', 'min:30', 'max:180'],
            'max_players' => ['nullable', 'integer', 'min:2', 'max:16'],
            'chaos_enabled' => ['nullable', 'boolean'],
            'chaos_mode' => ['nullable', 'string', 'in:classic,chill,extreme'],
            'word_category' => ['nullable', 'string', 'max:50'],
            'room_title' => ['nullable', 'string', 'max:50'],
        ]);

        $settings = $room->settings ?? [];
        if (isset($validated['is_public'])) {
            $settings['is_public'] = (bool) $validated['is_public'];
        }
        if (isset($validated['max_rounds'])) {
            $settings['max_rounds'] = (int) $validated['max_rounds'];
        }
        if (isset($validated['round_duration'])) {
            $settings['round_duration'] = (int) $validated['round_duration'];
        }
        if (isset($validated['max_players'])) {
            $settings['max_players'] = (int) $validated['max_players'];
        }
        if (isset($validated['chaos_mode'])) {
            $settings['chaos_mode'] = (string) $validated['chaos_mode'];
            $settings['chaos_enabled'] = $validated['chaos_mode'] !== 'chill';
        } elseif (isset($validated['chaos_enabled'])) {
            $settings['chaos_enabled'] = (bool) $validated['chaos_enabled'];
            $settings['chaos_mode'] = $settings['chaos_enabled'] ? 'classic' : 'chill';
        }
        if (isset($validated['word_category'])) {
            $settings['word_category'] = (string) $validated['word_category'];
        }
        if (isset($validated['room_title'])) {
            $settings['room_title'] = $this->wordBank->sanitizeText((string) $validated['room_title']);
        }

        $room->settings = $settings;
        $room->save();

        $game = $room->activeGame;
        $snapshot = $this->roomSnapshot->formatRoomSnapshot($room, $game, $game?->activeRound, $guestUser?->id);
        event(new RoundStateChanged($cleanCode, $snapshot));

        return response()->json([
            'status' => 'ok',
            'message' => 'Pengaturan room berhasil disimpan!',
            'room' => $snapshot,
        ]);
    }

    /**
     * Toggle player's Ready status (`room_players.is_ready`).
     */
    public function toggleReady(Request $request, string $code): JsonResponse
    {
        $cleanCode = substr(strtoupper(trim($code)), 0, 4);
        $player = $this->playerIdentity->resolveRequestPlayer($request, (string) $request->input('player_name', 'Player'));

        $room = Room::where('code', $cleanCode)->firstOrFail();
        $roomPlayer = RoomPlayer::where('room_id', $room->id)
            ->where('player_id', $player->id)
            ->first();

        if ($roomPlayer) {
            $newReady = $request->has('is_ready')
                ? (bool) $request->boolean('is_ready')
                : ! $roomPlayer->is_ready;
            $roomPlayer->update(['is_ready' => $newReady, 'left_at' => null]);
        }

        $game = $room->activeGame;
        $snapshot = $this->roomSnapshot->formatRoomSnapshot($room, $game, $game?->activeRound, $player->id);

        event(new RoundStateChanged($cleanCode, $snapshot));

        return response()->json([
            'status' => 'ok',
            'room' => $snapshot,
        ]);
    }

    /**
     * Start the game if at least 2 players in the room are Ready (`is_ready = true`).
     */
    public function start(Request $request, string $code): JsonResponse
    {
        $cleanCode = substr(strtoupper(trim($code)), 0, 4);
        $player = $this->playerIdentity->resolveRequestPlayer($request, (string) $request->input('player_name', 'Player'));

        $room = Room::where('code', $cleanCode)->firstOrFail();
        $guestUser = $request->attributes->get('guest_player');
        if ($guestUser && (int) $room->host_player_id !== (int) $guestUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => '👑 Hanya Host yang berhak memulai permainan!',
            ], 403);
        }

        $readyCount = $room->roomPlayers()
            ->whereNull('left_at')
            ->where('is_ready', true)
            ->count();

        if ($readyCount < 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'Minimal 2 pemain harus berstatus SIAP untuk memulai permainan!',
                'ready_count' => $readyCount,
            ], 422);
        }

        $room->update(['status' => 'playing']);
        $game = $room->activeGame ?? Game::create([
            'room_id' => $room->id,
            'status' => 'playing',
            'started_at' => now(),
        ]);

        $firstGamePlayer = $game->gamePlayers()->orderBy('turn_order')->first();
        $artistId = $firstGamePlayer ? $firstGamePlayer->player_id : $room->host_player_id;

        $round = $game->activeRound;
        if ($round) {
            $round->update([
                'artist_player_id' => $artistId,
                'status' => 'choosing_word',
                'started_at' => now(),
            ]);
            Stroke::where('round_id', $round->id)->delete();
        }

        $snapshot = $this->roomSnapshot->formatRoomSnapshot($room, $game, $round, $player->id);
        event(new RoundStateChanged($cleanCode, $snapshot));

        return response()->json([
            'status' => 'ok',
            'room' => $snapshot,
        ]);
    }

    /**
     * Handle player leaving/disconnecting from a room.
     * Transfers artist turn if the leaving player was the active artist, and marks empty timestamp for cleanup.
     */
    public function leave(Request $request, string $code): JsonResponse
    {
        $cleanCode = substr(strtoupper(trim($code)), 0, 4);
        $room = Room::where('code', $cleanCode)->first();
        if (! $room) {
            return response()->json(['status' => 'ok']);
        }

        $player = $this->playerIdentity->resolveRequestPlayer($request, (string) $request->input('player_name', 'Player'));

        RoomPlayer::where('room_id', $room->id)
            ->where('player_id', $player->id)
            ->update(['left_at' => now(), 'is_ready' => false]);

        $remainingPlayers = $room->roomPlayers()->whereNull('left_at')->pluck('player_id')->all();
        $game = $room->activeGame;
        $round = $game?->activeRound;

        if ($remainingPlayers === []) {
            $settings = is_array($room->settings) ? $room->settings : [];
            $settings['empty_since_ts'] = time();
            $room->update(['status' => 'lobby', 'settings' => $settings]);
        } else {
            if ($room->host_player_id === $player->id) {
                $room->update(['host_player_id' => $remainingPlayers[0]]);
            }

            if ($round && $round->artist_player_id === $player->id) {
                $round->update([
                    'artist_player_id' => $remainingPlayers[0],
                    'status' => 'choosing_word',
                    'started_at' => now(),
                ]);
            }
        }

        $snapshot = $this->roomSnapshot->formatRoomSnapshot($room->fresh(), $game, $round?->fresh());
        event(new RoundStateChanged($cleanCode, $snapshot));

        return response()->json([
            'status' => 'ok',
            'room' => $snapshot,
        ]);
    }

    /**
     * Toggle player readiness for a rematch and start a brand-new match once all active players are ready.
     */
    public function playAgain(Request $request, string $code): JsonResponse
    {
        $cleanCode = substr(strtoupper(trim($code)), 0, 4);

        try {
            $this->wordBank->ensureBaseWordsAndCursesExist();
            $room = Room::where('code', $cleanCode)->firstOrFail();
            $player = $this->playerIdentity->resolveRequestPlayer($request, (string) $request->input('player_name', 'Player'));

            $roomPlayer = RoomPlayer::firstOrCreate(
                ['room_id' => $room->id, 'player_id' => $player->id],
                ['is_ready' => false, 'joined_at' => now(), 'left_at' => null]
            );

            $newReady = $request->has('is_ready')
                ? (bool) $request->boolean('is_ready')
                : ! $roomPlayer->is_ready;

            $roomPlayer->update([
                'is_ready' => $newReady,
                'left_at' => null,
            ]);

            $activeRoomPlayers = RoomPlayer::where('room_id', $room->id)
                ->whereNull('left_at')
                ->get();
            $activePlayerIds = $activeRoomPlayers->pluck('player_id')->all();
            $totalActive = count($activePlayerIds);
            $readyCount = $activeRoomPlayers->where('is_ready', true)->count();

            if ($totalActive >= 2 && $readyCount >= $totalActive) {
                $previousGame = $room->activeGame;
                if ($previousGame && $previousGame->status !== 'finished') {
                    $previousGame->update([
                        'status' => 'finished',
                        'finished_at' => now(),
                    ]);
                }

                $newGame = Game::create([
                    'room_id' => $room->id,
                    'status' => 'playing',
                    'started_at' => now(),
                ]);

                foreach ($activePlayerIds as $idx => $pid) {
                    GamePlayer::create([
                        'game_id' => $newGame->id,
                        'player_id' => $pid,
                        'turn_order' => $idx + 1,
                        'total_score' => 0,
                        'chaos_energy' => 100,
                    ]);
                }

                $settings = is_array($room->settings) ? $room->settings : [];
                unset($settings['last_round_transition'], $settings['returned_to_lobby_at']);
                $settings['ratings'] = [];
                $wordPacks = is_array($settings['word_packs'] ?? null) ? $settings['word_packs'] : null;

                $firstWord = $this->wordBank->pickRandomSimpleWord(null, $wordPacks);
                $firstArtistId = (int) ($activePlayerIds[0] ?? $room->host_player_id);

                $firstRound = Round::create([
                    'game_id' => $newGame->id,
                    'round_number' => 1,
                    'artist_player_id' => $firstArtistId,
                    'word_id' => $firstWord->id,
                    'status' => 'drawing',
                    'started_at' => now(),
                ]);
                $firstRound->load(['artist', 'word']);

                $room->update([
                    'status' => 'playing',
                    'settings' => $settings,
                ]);

                $lobby = Lobby::where('code', $cleanCode)->first();
                if ($lobby) {
                    $lobby->update(['status' => 'playing']);
                }

                $freshRoom = $room->fresh();
                event(new RoundStateChanged($cleanCode, $this->roomSnapshot->formatRoomSnapshot($freshRoom, $newGame, $firstRound, 0)));

                return response()->json([
                    'status' => 'ok',
                    'match_started' => true,
                    'is_ready' => true,
                    'ready_count' => $readyCount,
                    'total_players' => $totalActive,
                    'room' => $this->roomSnapshot->formatRoomSnapshot($freshRoom, $newGame, $firstRound, $player->id),
                ]);
            }

            $freshRoom = $room->fresh();
            $activeGame = $freshRoom->activeGame;
            $snapshot = $this->roomSnapshot->formatRoomSnapshot($freshRoom, $activeGame, $activeGame?->activeRound, $player->id);
            event(new RoundStateChanged($cleanCode, $snapshot));

            return response()->json([
                'status' => 'ok',
                'match_started' => false,
                'is_ready' => $newReady,
                'ready_count' => $readyCount,
                'total_players' => $totalActive,
                'room' => $snapshot,
            ]);
        } catch (Throwable) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui status siap main lagi.',
            ], 500);
        }
    }

    /**
     * Return to the Lobby. If called by the Leader/Host, transitions the whole room & lobby back together.
     */
    public function returnToLobby(Request $request, string $code): JsonResponse
    {
        $cleanCode = substr(strtoupper(trim($code)), 0, 4);

        try {
            $room = Room::where('code', $cleanCode)->firstOrFail();
            $player = $this->playerIdentity->resolveRequestPlayer($request, (string) $request->input('player_name', 'Player'));
            $isHost = (int) $room->host_player_id === (int) $player->id;
            $lobby = Lobby::where('code', $cleanCode)->first();

            if ($isHost) {
                $activeGame = $room->activeGame;
                if ($activeGame && $activeGame->status !== 'finished') {
                    $activeGame->update([
                        'status' => 'finished',
                        'finished_at' => now(),
                    ]);
                }

                $settings = is_array($room->settings) ? $room->settings : [];
                unset($settings['last_round_transition']);
                $settings['returned_to_lobby_at'] = time();

                $room->update([
                    'status' => 'lobby',
                    'settings' => $settings,
                ]);

                if ($lobby) {
                    $lobby->update(['status' => 'waiting']);
                    LobbyPlayer::where('lobby_id', $lobby->id)
                        ->where('user_id', '!=', $lobby->host_id)
                        ->update(['is_ready' => false]);
                    LobbyPlayer::where('lobby_id', $lobby->id)
                        ->where('user_id', $lobby->host_id)
                        ->update(['is_ready' => true]);

                    LobbyMessage::create([
                        'lobby_id' => $lobby->id,
                        'user_id' => null,
                        'sender_type' => 'system',
                        'message' => 'Host membawa semua pemain kembali ke Lobby.',
                    ]);
                }

                $snapshot = $this->roomSnapshot->formatRoomSnapshot($room->fresh(), $activeGame, $activeGame?->activeRound, $player->id);
                event(new RoundStateChanged($cleanCode, $snapshot));

                return response()->json([
                    'status' => 'ok',
                    'returned_all' => true,
                    'redirect' => $lobby ? route('lobby.room', ['code' => $cleanCode]) : null,
                    'room' => $snapshot,
                ]);
            }

            if ($lobby && $lobby->status === 'waiting') {
                return response()->json([
                    'status' => 'ok',
                    'returned_all' => false,
                    'redirect' => route('lobby.room', ['code' => $cleanCode]),
                ]);
            }

            RoomPlayer::where('room_id', $room->id)
                ->where('player_id', $player->id)
                ->update(['left_at' => now(), 'is_ready' => false]);

            if ($lobby) {
                $authUser = Auth::user();
                if ($authUser) {
                    LobbyPlayer::where('lobby_id', $lobby->id)
                        ->where('user_id', $authUser->id)
                        ->delete();
                }
            }

            $freshRoom = $room->fresh();
            $activeGame = $freshRoom->activeGame;
            $snapshot = $this->roomSnapshot->formatRoomSnapshot($freshRoom, $activeGame, $activeGame?->activeRound, 0);
            event(new RoundStateChanged($cleanCode, $snapshot));

            return response()->json([
                'status' => 'ok',
                'returned_all' => false,
                'redirect' => $lobby ? route('lobby.hub') : null,
            ]);
        } catch (Throwable) {
            return response()->json(['status' => 'error'], 500);
        }
    }
}
