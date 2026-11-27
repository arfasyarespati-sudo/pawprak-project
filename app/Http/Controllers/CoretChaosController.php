<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Lobby;
use App\Models\Room;
use App\Models\RoomPlayer;
use App\Models\Round;
use App\Models\User;
use App\Services\MuseumArtworkService;
use App\Services\PlayerIdentityService;
use App\Services\RoomSnapshotService;
use App\Services\SabotageDeckService;
use App\Services\WordBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CoretChaosController extends Controller
{
    public function __construct(
        public SabotageDeckService $sabotageDeck,
        public PlayerIdentityService $playerIdentity,
        public RoomSnapshotService $roomSnapshot,
        public WordBankService $wordBank,
        public MuseumArtworkService $museumArtwork,
    ) {}

    /**
     * Display the main CORET! CHAOS real multiplayer application.
     */
    public function index(Request $request): View
    {
        $deckCatalog = $this->sabotageDeck->deckCatalog();
        $guestUser = $request->attributes->get('guest_player');

        $playerId = (int) ($guestUser->id ?? 1001);
        $nickname = (string) ($guestUser->nickname ?? 'SenimanRusuh_89');
        $rawToken = (string) ($guestUser->raw_token ?? '');

        $onlineCount = $this->playerIdentity->touchOnlineHeartbeat($rawToken ?: "player_{$playerId}", $nickname);
        $hallOfShame = $this->museumArtwork->loadRealMuseumArtworks('newest');
        $publicRooms = $this->roomSnapshot->loadPublicRoomsSummary();
        $profilePayload = $this->playerIdentity->buildUserProfilePayload($guestUser);

        return view('draw-chaos', [
            'deckCatalog' => $deckCatalog,
            'hallOfShame' => $hallOfShame,
            'publicRooms' => $publicRooms,
            'initialRoom' => null,
            'bootView' => null,
            'onlineCount' => $onlineCount,
            'guestPlayer' => $profilePayload,
            'reverbConfig' => $this->roomSnapshot->reverbConfig(),
        ]);
    }

    /**
     * Launch live CORET! CHAOS gameplay directly from an authenticated team Lobby (`/game/{code}`).
     */
    public function playFromLobby(Request $request, string $code): View|RedirectResponse
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();
        $cleanCode = substr(strtoupper(trim($code)), 0, 4);

        if (! $authUser) {
            return redirect()->route('login');
        }

        $lobby = Lobby::where('code', $cleanCode)->with('players.user.player')->first();
        if (! $lobby) {
            return redirect()->route('lobby.hub')->with('error', 'Lobby tidak ditemukan.');
        }

        $isMember = $lobby->players()->where('user_id', $authUser->id)->exists();
        if (! $isMember) {
            return redirect()->route('lobby.hub')->with('error', 'Kamu bukan anggota room ini.');
        }

        if ($lobby->status !== 'playing') {
            return redirect()->route('lobby.room', ['code' => $cleanCode]);
        }

        $this->wordBank->ensureBaseWordsAndCursesExist();

        $currentPlayer = $this->playerIdentity->resolveLobbyUserPlayer($authUser);
        $hostUser = $lobby->host ?: $authUser;
        $hostPlayer = $this->playerIdentity->resolveLobbyUserPlayer($hostUser);

        $chaosMode = match ($lobby->game_mode) {
            'chill', 'santuy' => 'chill',
            'extreme', 'ekstrem', 'rush' => 'extreme',
            default => 'classic',
        };

        $maxRounds = max(2, min(8, $lobby->players->count() * 2));

        $room = Room::firstOrCreate(
            ['code' => $cleanCode],
            [
                'host_player_id' => $hostPlayer->id,
                'status' => 'playing',
                'settings' => [
                    'is_public' => (bool) $lobby->is_public,
                    'max_players' => max(2, min(16, (int) $lobby->max_players)),
                    'max_rounds' => $maxRounds,
                    'round_duration' => max(30, min(180, (int) $lobby->round_duration)),
                    'chaos_enabled' => $chaosMode !== 'chill',
                    'chaos_mode' => $chaosMode,
                    'word_category' => 'all',
                    'word_packs' => is_array($lobby->word_packs) ? $lobby->word_packs : [],
                    'empty_since_ts' => null,
                    'ratings' => [],
                    'lobby_chat' => [],
                ],
            ]
        );

        $game = $room->activeGame;
        $isNewGame = ! $game || $game->status === 'finished';

        if ($isNewGame) {
            $settings = is_array($room->settings) ? $room->settings : [];
            unset($settings['last_round_transition'], $settings['returned_to_lobby_at']);
            $settings['max_rounds'] = $maxRounds;
            $settings['round_duration'] = max(30, min(180, (int) $lobby->round_duration));
            $settings['chaos_enabled'] = $chaosMode !== 'chill';
            $settings['chaos_mode'] = $chaosMode;
            $settings['word_packs'] = is_array($lobby->word_packs) ? $lobby->word_packs : [];
            $settings['ratings'] = [];

            $room->update([
                'host_player_id' => $hostPlayer->id,
                'status' => 'playing',
                'settings' => $settings,
            ]);

            $game = Game::create([
                'room_id' => $room->id,
                'status' => 'playing',
                'started_at' => now(),
            ]);
        } elseif ($room->status !== 'playing') {
            $room->update(['status' => 'playing']);
        }

        foreach ($lobby->players as $idx => $lp) {
            if (! $lp->user) {
                continue;
            }
            $memberPlayer = $this->playerIdentity->resolveLobbyUserPlayer($lp->user, $lp->selected_avatar);

            RoomPlayer::updateOrCreate(
                ['room_id' => $room->id, 'player_id' => $memberPlayer->id],
                ['is_ready' => true, 'joined_at' => now(), 'left_at' => null]
            );

            GamePlayer::firstOrCreate(
                ['game_id' => $game->id, 'player_id' => $memberPlayer->id],
                ['turn_order' => (int) ($lp->slot_number ?: ($idx + 1)), 'total_score' => 0, 'chaos_energy' => 100]
            );
        }

        $round = $game->activeRound()->with(['artist', 'word'])->first();
        if (! $round) {
            $defaultWord = $this->wordBank->pickRandomSimpleWord(
                null,
                is_array($lobby->word_packs) ? $lobby->word_packs : null
            );
            $round = Round::create([
                'game_id' => $game->id,
                'round_number' => 1,
                'artist_player_id' => $hostPlayer->id,
                'word_id' => $defaultWord->id,
                'status' => 'drawing',
                'started_at' => now(),
            ]);
            $round->load(['artist', 'word']);
        }

        $rawToken = "auth_user_{$authUser->id}";
        $onlineCount = $this->playerIdentity->touchOnlineHeartbeat($rawToken, $currentPlayer->nickname);
        $snapshot = $this->roomSnapshot->formatRoomSnapshot($room, $game, $round, $currentPlayer->id);

        return view('draw-chaos', [
            'deckCatalog' => $this->sabotageDeck->deckCatalog(),
            'hallOfShame' => $this->museumArtwork->loadRealMuseumArtworks('newest'),
            'publicRooms' => $this->roomSnapshot->loadPublicRoomsSummary(),
            'initialRoom' => $snapshot,
            'bootView' => 'game',
            'onlineCount' => $onlineCount,
            'guestPlayer' => [
                'id' => $currentPlayer->id,
                'nickname' => $currentPlayer->nickname,
                'avatar' => $currentPlayer->avatar ?: 'cat-neon',
                'email' => $authUser->email,
                'is_authenticated' => true,
                'raw_token' => $rawToken,
                'total_score' => (int) ($authUser->mmr ?? 0),
                'games_played' => 0,
                'level' => (int) ($authUser->level ?? 1),
            ],
            'reverbConfig' => $this->roomSnapshot->reverbConfig(),
        ]);
    }

    /**
     * Real-time Ping RTT measurement & active online player counter.
     */
    public function ping(Request $request): JsonResponse
    {
        $guestUser = $request->attributes->get('guest_player');
        $token = (string) (
            $request->header('X-Guest-Token')
            ?? $guestUser->raw_token
            ?? $request->ip()
        );
        $nickname = (string) (
            $request->header('X-Player-Nickname')
            ?? $guestUser->nickname
            ?? 'Player'
        );

        $onlineCount = $this->playerIdentity->touchOnlineHeartbeat($token, $nickname);
        $this->roomSnapshot->cleanupExpiredEmptyRooms();

        return response()->json([
            'status' => 'ok',
            'online_count' => $onlineCount,
            'server_time_ms' => (int) round(microtime(true) * 1000),
        ]);
    }

    /**
     * Authenticate Guest Token players for Laravel Reverb Presence & Private channels (`POST /broadcasting/auth`).
     */
    public function authenticateBroadcast(Request $request): JsonResponse
    {
        $socketId = (string) $request->input('socket_id', '1000.1000');
        $channelName = (string) $request->input('channel_name', 'presence-room.CHAO');

        $guestPlayer = $request->attributes->get('guest_player') ?? $request->user();
        $userId = (string) ($guestPlayer->id ?? (abs(crc32((string) $request->ip())) % 90000 + 1000));
        $nickname = (string) ($guestPlayer->nickname ?? $request->input('player_name', 'SenimanRusuh_89'));

        $appKey = (string) env('REVERB_APP_KEY', 'coret_chaos_key');
        $appSecret = (string) env('REVERB_APP_SECRET', 'coret_chaos_secret');

        $channelData = json_encode([
            'user_id' => $userId,
            'user_info' => [
                'id' => (int) $userId,
                'nickname' => $nickname,
                'avatar' => $guestPlayer->avatar ?? 'cat-neon',
            ],
        ], JSON_UNESCAPED_SLASHES) ?: '{}';

        if (str_starts_with($channelName, 'presence-')) {
            $stringToSign = "{$socketId}:{$channelName}:{$channelData}";
            $signature = hash_hmac('sha256', $stringToSign, $appSecret);

            return response()->json([
                'auth' => "{$appKey}:{$signature}",
                'channel_data' => $channelData,
            ]);
        }

        $stringToSign = "{$socketId}:{$channelName}";
        $signature = hash_hmac('sha256', $stringToSign, $appSecret);

        return response()->json([
            'auth' => "{$appKey}:{$signature}",
        ]);
    }
}
