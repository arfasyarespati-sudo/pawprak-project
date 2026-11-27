<?php

namespace App\Services;

use App\Models\CurseUse;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Guess;
use App\Models\Lobby;
use App\Models\Room;
use App\Models\RoomPlayer;
use App\Models\Round;
use App\Models\Stroke;
use Throwable;

class RoomSnapshotService
{
    public function __construct(
        public SabotageDeckService $sabotageDeck,
    ) {}

    /**
     * Return Reverb WebSocket configuration passed to the frontend.
     *
     * @return array{key: string, host: string, port: int, scheme: string}
     */
    public function reverbConfig(): array
    {
        return [
            'key' => (string) env('REVERB_APP_KEY', 'coret_chaos_key'),
            'host' => (string) env('REVERB_HOST', '127.0.0.1'),
            'port' => (int) env('REVERB_PORT', 8080),
            'scheme' => (string) env('REVERB_SCHEME', 'http'),
        ];
    }

    /**
     * Format a Room, Game, and Round into a structured snapshot.
     * Only sends `secret_word` if `$requesterPlayerId` matches `$round->artist_player_id` (or in unit tests).
     *
     * @return array<string, mixed>
     */
    public function formatRoomSnapshot(
        Room $room,
        ?Game $game = null,
        ?Round $round = null,
        ?int $requesterPlayerId = null
    ): array {
        $round?->loadMissing(['artist', 'word']);
        $settings = is_array($room->settings) ? $room->settings : [];
        $roundDuration = max(20, (int) ($settings['round_duration'] ?? 60));
        $secretWord = $round?->word?->phrase ?? 'Kucing Naik Helikopter';
        $hintData = $round
            ? $round->maskedPhraseWithHints($roundDuration)
            : [
                'masked_word' => '_ _ _ _ _ _',
                'revealed_count' => 0,
                'max_reveals' => 0,
                'interval_seconds' => max(10, (int) floor($roundDuration / 4)),
                'next_hint_in_seconds' => null,
            ];
        $maskedWord = $hintData['masked_word'];
        $wordCount = $round ? $round->wordCount() : 1;
        $artistId = $round?->artist_player_id;
        $artistName = $round?->artist?->nickname ?? ($room->host?->nickname ?? 'Pelukis');

        $activeRoomPlayers = $room->roomPlayers()
            ->whereNull('left_at')
            ->with('player')
            ->get();

        $gamePlayersMap = $game
            ? $game->gamePlayers()->get()->keyBy('player_id')
            : collect();

        $correctGuesserIds = $round
            ? Guess::where('round_id', $round->id)->where('is_correct', true)->distinct()->pluck('player_id')->all()
            : [];

        $sabotageCountsByPlayer = [];
        if ($game) {
            $roundIds = $game->rounds()->pluck('id')->all();
            if ($roundIds !== []) {
                $sabotageCountsByPlayer = CurseUse::whereIn('round_id', $roundIds)
                    ->selectRaw('player_id, count(*) as total')
                    ->groupBy('player_id')
                    ->pluck('total', 'player_id')
                    ->all();
            }
        }

        $playersPayload = $activeRoomPlayers->values()->map(function (RoomPlayer $rp, int $index) use ($gamePlayersMap, $artistId, $room, $correctGuesserIds, $sabotageCountsByPlayer): array {
            /** @var ?GamePlayer $gp */
            $gp = $gamePlayersMap->get($rp->player_id);
            $score = (int) ($gp->total_score ?? 0);

            return [
                'id' => $rp->player_id,
                'name' => $rp->player?->nickname ?? 'Player',
                'avatar' => $rp->player?->avatar ?? 'cat-neon',
                'role' => ($artistId !== null && $rp->player_id === $artistId) ? 'artist' : 'saboteur',
                'is_host' => $rp->player_id === $room->host_player_id,
                'is_ready' => (bool) $rp->is_ready,
                'has_guessed' => in_array($rp->player_id, $correctGuesserIds, true),
                'sabotages' => (int) ($sabotageCountsByPlayer[$rp->player_id] ?? 0),
                'score' => $score,
                'level' => max(1, (int) floor($score / 200) + 1),
                'chaos_energy' => (int) ($gp->chaos_energy ?? 100),
                'turn_order' => (int) ($gp->turn_order ?? ($index + 1)),
            ];
        })->all();

        $ratings = is_array($settings['ratings'] ?? null) ? $settings['ratings'] : [];
        $avgRating = count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : 0.0;

        $strokesPayload = $round
            ? $round->strokes()->orderBy('sequence')->get()->map(fn (Stroke $s): array => [
                'id' => $s->id,
                'stroke_type' => 'path',
                'color' => $s->color,
                'size' => $s->brush_size,
                'brush_size' => $s->brush_size,
                'points' => $s->points,
                'sequence' => $s->sequence,
            ])->all()
            : [];

        $latestCurseUse = $round
            ? $round->curseUses()->reorder()->with(['curse', 'player'])->latest('activated_at')->first()
            : null;

        $recentCurseUses = $round
            ? $round->curseUses()
                ->reorder()
                ->with(['curse', 'player'])
                ->where('activated_at', '>=', now()->subSeconds(10))
                ->orderBy('id')
                ->get()
            : collect();

        $activeCursesPayload = [];
        foreach ($recentCurseUses as $cu) {
            $durSec = max(1, (int) round(($cu->curse?->duration_ms ?? 4000) / 1000));
            $elapsedCurseSec = $cu->activated_at ? max(0, now()->getTimestamp() - $cu->activated_at->getTimestamp()) : 0;
            $remSec = max(0, $durSec - $elapsedCurseSec);
            if ($remSec > 0) {
                $activeCursesPayload[] = [
                    'id' => $cu->id,
                    'curse_type' => $cu->curse?->slug ?? 'earthquake',
                    'curse_name' => $cu->curse?->name ?? 'Gempa Bumi',
                    'triggered_by_name' => $cu->player?->nickname ?? 'Saboteur',
                    'duration_seconds' => $durSec,
                    'remaining_seconds' => $remSec,
                    'payload' => $cu->payload,
                ];
            }
        }

        $latestCursePayload = null;
        if ($latestCurseUse) {
            $latestDurSec = max(1, (int) round(($latestCurseUse->curse?->duration_ms ?? 4000) / 1000));
            $latestElapsedSec = $latestCurseUse->activated_at ? max(0, now()->getTimestamp() - $latestCurseUse->activated_at->getTimestamp()) : 0;
            $latestCursePayload = [
                'id' => $latestCurseUse->id,
                'curse_type' => $latestCurseUse->curse?->slug ?? 'earthquake',
                'curse_name' => $latestCurseUse->curse?->name ?? 'Gempa Bumi',
                'triggered_by_name' => $latestCurseUse->player?->nickname ?? 'Saboteur',
                'duration_seconds' => $latestDurSec,
                'remaining_seconds' => max(0, $latestDurSec - $latestElapsedSec),
                'payload' => $latestCurseUse->payload,
            ];
        }

        $roundRemainingSeconds = ($round && $round->started_at && $round->status === 'drawing')
            ? max(0, $roundDuration - max(0, now()->getTimestamp() - $round->started_at->getTimestamp()))
            : $roundDuration;

        $curseSlugs = $round
            ? $round->curseUses()->with('curse')->get()->map(fn (CurseUse $u): string => $u->curse?->slug ?? 'earthquake')->all()
            : [];

        $chaosPercent = min(100, count($curseSlugs) * 25);
        $sabotageSummary = $this->sabotageDeck->buildSabotageSummary($curseSlugs);

        $recentGuesses = $round
            ? $round->guesses()->reorder()->with('player')->latest('id')->limit(30)->get()->reverse()->values()->map(function (Guess $g) use ($round): array {
                $isExact = (bool) $g->is_correct;
                $isArtist = (int) $g->player_id === (int) $round->artist_player_id;

                return [
                    'id' => $g->id,
                    'player_name' => $g->player?->nickname ?? 'Player',
                    'public_display_text' => $isExact ? '🎉 [BERHASIL MENEBAK KATA RAHASIA!]' : $g->content,
                    'guess_text' => $isExact ? '***' : $g->content,
                    'status' => $isArtist ? 'artist_chat' : ($isExact ? 'exact' : 'miss'),
                    'is_artist' => $isArtist,
                    'is_correct' => $isExact,
                    'guesser_points' => 350,
                ];
            })->all()
            : [];

        $isArtistRequester = $requesterPlayerId === null
            || $artistId === null
            || $requesterPlayerId === $artistId
            || count($playersPayload) <= 1
            || in_array($room->status, ['finished', 'results'], true)
            || $round?->status === 'finished';

        $hasLobby = Lobby::where('code', $room->code)->exists();

        return [
            'id' => $room->id,
            'game_id' => $game?->id,
            'code' => $room->code,
            'status' => $room->status,
            'lobby_url' => $hasLobby ? route('lobby.room', ['code' => $room->code]) : null,
            'last_round_transition' => $settings['last_round_transition'] ?? null,
            'room_title' => (string) ($settings['room_title'] ?? "Room [{$room->code}]"),
            'is_public' => (bool) ($settings['is_public'] ?? true),
            'current_round' => $round?->round_number ?? 1,
            'max_rounds' => (int) ($settings['max_rounds'] ?? 5),
            'round_duration' => (int) ($settings['round_duration'] ?? 60),
            'max_players' => (int) ($settings['max_players'] ?? 8),
            'chaos_enabled' => (bool) ($settings['chaos_enabled'] ?? true),
            'chaos_mode' => (string) ($settings['chaos_mode'] ?? (($settings['chaos_enabled'] ?? true) ? 'classic' : 'chill')),
            'word_category' => (string) ($settings['word_category'] ?? 'all'),
            'host_player_id' => $room->host_player_id,
            'host_player_name' => $room->host?->nickname ?? 'Host',
            'ready_count' => collect($playersPayload)->where('is_ready', true)->count(),
            'players' => $playersPayload,
            'lobby_chat' => array_values(is_array($settings['lobby_chat'] ?? null) ? $settings['lobby_chat'] : []),
            'strokes' => $strokesPayload,
            'recent_guesses' => $recentGuesses,
            'avg_rating' => $avgRating,
            'votes_count' => count($ratings),
            'latest_curse' => $latestCursePayload,
            'active_curses' => $activeCursesPayload,
            'round' => [
                'id' => $round?->id ?? 1,
                'round_number' => $round?->round_number ?? 1,
                'artist_id' => $artistId,
                'artist_name' => $artistName,
                'secret_word' => $isArtistRequester ? $secretWord : null,
                'masked_word' => $maskedWord,
                'word_count' => $wordCount,
                'revealed_hints' => $hintData['revealed_count'],
                'max_hints' => $hintData['max_reveals'],
                'hint_interval_seconds' => $hintData['interval_seconds'],
                'next_hint_in_seconds' => $hintData['next_hint_in_seconds'],
                'remaining_seconds' => $roundRemainingSeconds,
                'word_category' => $round?->word?->category ?? 'Pilihan Pelukis',
                'status' => $round?->status ?? 'drawing',
                'chaos_percentage' => $chaosPercent,
                'sabotage_summary' => $sabotageSummary,
            ],
        ];
    }

    /**
     * Minimal fallback snapshot when DB is unreachable (contains ONLY the current real player, 0 bots).
     *
     * @return array<string, mixed>
     */
    public function buildFallbackRoomSnapshot(string $code, string $playerName, string $secretPhrase): array
    {
        $words = preg_split('/\s+/', trim($secretPhrase)) ?: [$secretPhrase];
        $maskedParts = array_map(
            fn (string $w): string => implode(' ', array_fill(0, mb_strlen($w), '_')),
            $words
        );

        return [
            'id' => 1,
            'code' => substr($code, 0, 4),
            'status' => 'lobby',
            'current_round' => 1,
            'max_rounds' => 5,
            'round_duration' => 60,
            'host_player_id' => 1,
            'host_player_name' => $playerName,
            'ready_count' => 1,
            'players' => [
                [
                    'id' => 1,
                    'name' => $playerName,
                    'avatar' => 'cat-neon',
                    'role' => 'artist',
                    'is_host' => true,
                    'is_ready' => true,
                    'score' => 0,
                    'chaos_energy' => 100,
                    'turn_order' => 1,
                ],
            ],
            'strokes' => [],
            'avg_rating' => 0.0,
            'votes_count' => 0,
            'latest_curse' => null,
            'active_curses' => [],
            'round' => [
                'id' => 1,
                'round_number' => 1,
                'artist_id' => 1,
                'artist_name' => $playerName,
                'secret_word' => $secretPhrase,
                'masked_word' => implode('   /   ', $maskedParts),
                'word_count' => count($words),
                'word_category' => 'Pilihan Pelukis',
                'status' => 'drawing',
                'chaos_percentage' => 0,
                'sabotage_summary' => 'Tanpa Kutukan (Damai Sentosa)',
            ],
        ];
    }

    /**
     * Load active public lobbies from DB (zero fake lobbies).
     *
     * @return array<int, array<string, mixed>>
     */
    public function loadPublicRoomsSummary(): array
    {
        try {
            $rooms = Room::with('host')
                ->where('status', 'lobby')
                ->latest('updated_at')
                ->limit(12)
                ->get();

            $result = [];
            foreach ($rooms as $room) {
                $settings = is_array($room->settings) ? $room->settings : [];
                $isPublic = (bool) ($settings['is_public'] ?? true);
                if (! $isPublic) {
                    continue;
                }
                $activeCount = $room->roomPlayers()->whereNull('left_at')->count();
                if ($activeCount <= 0) {
                    continue;
                }
                $maxPlayers = max(2, min(16, (int) ($settings['max_players'] ?? 8)));
                $result[] = [
                    'code' => $room->code,
                    'room_title' => (string) ($settings['room_title'] ?? "Room [{$room->code}]"),
                    'host_name' => $room->host?->nickname ?? 'Host',
                    'player_count' => $activeCount,
                    'max_players' => $maxPlayers,
                    'round_duration' => (int) ($settings['round_duration'] ?? 60),
                    'chaos_mode' => (string) ($settings['chaos_mode'] ?? (($settings['chaos_enabled'] ?? true) ? 'classic' : 'chill')),
                    'word_category' => (string) ($settings['word_category'] ?? 'all'),
                ];
            }

            return $result;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Delete rooms that have been empty (0 active players) for more than 5 minutes.
     */
    public function cleanupExpiredEmptyRooms(): void
    {
        try {
            $rooms = Room::where('status', 'lobby')->get();
            $now = time();
            foreach ($rooms as $room) {
                $activeCount = $room->roomPlayers()->whereNull('left_at')->count();
                $emptySince = $room->settings['empty_since_ts'] ?? null;
                if ($activeCount === 0 && is_int($emptySince) && ($now - $emptySince) >= 300) {
                    $room->delete();
                }
            }
        } catch (Throwable) {
            // Ignore cleanup errors
        }
    }

    /**
     * Generate a unique 4-character room code matching `rooms.code char(4)`.
     */
    public function generateUniqueRoomCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            try {
                if (! Room::where('code', $code)->exists()) {
                    return $code;
                }
            } catch (Throwable) {
                return $code;
            }
        }

        return 'CHAO';
    }
}
