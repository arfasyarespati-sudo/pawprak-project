<?php

namespace App\Http\Controllers;

use App\Events\BrushStrokeBroadcasted;
use App\Events\CurseTriggeredEvent;
use App\Events\GuessSubmitted;
use App\Events\GuessSubmittedEvent;
use App\Events\RoundStateChanged;
use App\Events\SabotageTriggered;
use App\Events\StrokeDrawnEvent;
use App\Models\Curse;
use App\Models\CurseUse;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Guess;
use App\Models\Room;
use App\Models\Round;
use App\Models\RoundPoint;
use App\Models\Stroke;
use App\Models\Word;
use App\Services\FuzzyGuessMatcherService;
use App\Services\GameRoundService;
use App\Services\PlayerIdentityService;
use App\Services\RoomSnapshotService;
use App\Services\SabotageDeckService;
use App\Services\WordBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class GameplayActionController extends Controller
{
    public function __construct(
        public FuzzyGuessMatcherService $fuzzyMatcher,
        public SabotageDeckService $sabotageDeck,
        public WordBankService $wordBank,
        public PlayerIdentityService $playerIdentity,
        public RoomSnapshotService $roomSnapshot,
        public GameRoundService $gameRound,
    ) {}

    /**
     * Allow the active Artist to type a custom secret word (2-40 chars, profanity filtered).
     */
    public function setSecretWord(Request $request, string $code): JsonResponse
    {
        $validated = $request->validate([
            'secret_word' => ['required', 'string', 'min:2', 'max:40'],
            'player_name' => ['nullable', 'string', 'max:50'],
        ]);

        $cleanCode = substr(strtoupper(trim($code)), 0, 4);
        $cleanWord = $this->wordBank->sanitizeSecretWord($validated['secret_word']);

        if (mb_strlen($cleanWord) < 2 || mb_strlen($cleanWord) > 40) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kata rahasia harus berisi 2 hingga 40 karakter!',
            ], 422);
        }

        if ($this->wordBank->containsProfanity($cleanWord)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kata rahasia mengandung kata kasar yang dilarang! Gunakan kata lain.',
            ], 422);
        }

        $room = Room::where('code', $cleanCode)->firstOrFail();
        $player = $this->playerIdentity->resolveRequestPlayer($request, (string) ($validated['player_name'] ?? 'Artist'));
        $game = $room->activeGame;
        $round = $game?->activeRound;

        if ($round && $round->artist_player_id !== $player->id && $room->roomPlayers()->whereNull('left_at')->count() > 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya Pelukis giliran saat ini yang boleh menentukan kata rahasia!',
            ], 403);
        }

        $word = Word::firstOrCreate(
            ['phrase' => $cleanWord],
            ['category' => 'Pilihan Pelukis', 'difficulty' => 'medium']
        );

        if ($round) {
            $round->update([
                'word_id' => $word->id,
                'status' => 'drawing',
                'started_at' => now(),
            ]);
            Stroke::where('round_id', $round->id)->delete();
            CurseUse::where('round_id', $round->id)->delete();
        }

        $snapshot = $this->roomSnapshot->formatRoomSnapshot($room, $game, $round?->fresh(['artist', 'word']), $player->id);
        event(new RoundStateChanged($cleanCode, $snapshot));

        return response()->json([
            'status' => 'ok',
            'room' => $snapshot,
        ]);
    }

    /**
     * Finish current round, either advancing to next round or ending the game if max rounds reached.
     */
    public function finishRound(Request $request, string $code): JsonResponse
    {
        $cleanCode = substr(strtoupper(trim($code)), 0, 4);

        try {
            $room = Room::where('code', $cleanCode)->firstOrFail();
            $game = $room->activeGame;
            $round = $game?->activeRound()->with(['artist', 'word'])->first();
            $guestUser = $request->attributes->get('guest_player');
            $requesterId = isset($guestUser->id) ? (int) $guestUser->id : 0;

            if ($room->status === 'finished') {
                $snapshot = $this->roomSnapshot->formatRoomSnapshot($room, $game, $round, $requesterId);

                return response()->json([
                    'status' => 'ok',
                    'game_finished' => true,
                    'round_rotated' => false,
                    'room' => $snapshot,
                ]);
            }

            if ($game && $round && $round->status === 'drawing') {
                $previousWord = $round->word?->phrase ?? 'Apel';
                $maxRounds = (int) ($room->settings['max_rounds'] ?? 5);

                if ($round->round_number >= $maxRounds) {
                    $this->gameRound->finishGameSession($room, $game, $round);
                    $freshRoom = $room->fresh();
                    $freshGame = $game->fresh();
                    $freshRound = $round->fresh();
                    $snapshot = $this->roomSnapshot->formatRoomSnapshot($freshRoom, $freshGame, $freshRound, $requesterId);
                    event(new RoundStateChanged($cleanCode, $this->roomSnapshot->formatRoomSnapshot($freshRoom, $freshGame, $freshRound, 0)));

                    return response()->json([
                        'status' => 'ok',
                        'game_finished' => true,
                        'round_rotated' => false,
                        'previous_word' => $previousWord,
                        'room' => $snapshot,
                    ]);
                }

                $nextRound = $this->gameRound->advanceToNextRound($room, $game, $round);
                $settings = is_array($room->settings) ? $room->settings : [];
                $settings['last_round_transition'] = [
                    'round_id' => $nextRound->id,
                    'previous_word' => $previousWord,
                    'reason' => 'timeout',
                    'next_round_number' => $nextRound->round_number,
                    'max_rounds' => $maxRounds,
                    'next_artist_id' => $nextRound->artist_player_id,
                    'next_artist_name' => $nextRound->artist?->nickname ?? 'Pelukis',
                    'at' => time(),
                ];
                $room->update(['settings' => $settings]);

                $freshRoom = $room->fresh();
                event(new RoundStateChanged($cleanCode, $this->roomSnapshot->formatRoomSnapshot($freshRoom, $game, $nextRound, 0)));
                $requesterSnapshot = $this->roomSnapshot->formatRoomSnapshot($freshRoom, $game, $nextRound, $requesterId);

                return response()->json([
                    'status' => 'ok',
                    'game_finished' => false,
                    'round_rotated' => true,
                    'previous_word' => $previousWord,
                    'next_round_number' => $nextRound->round_number,
                    'next_artist_name' => $nextRound->artist?->nickname ?? 'Pelukis',
                    'room' => $requesterSnapshot,
                ]);
            }

            $room->update(['status' => 'results']);
            $snapshot = $this->roomSnapshot->formatRoomSnapshot($room->fresh(), $game, $round, $requesterId);
            event(new RoundStateChanged($cleanCode, $snapshot));

            return response()->json([
                'status' => 'ok',
                'game_finished' => false,
                'round_rotated' => false,
                'room' => $snapshot,
            ]);
        } catch (Throwable) {
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Rotate roles between Artist (`rounds.artist_player_id`) and Saboteurs (`game_players.turn_order`) for the next round.
     */
    public function rotateRound(Request $request, string $code): JsonResponse
    {
        $validated = $request->validate([
            'secret_word' => ['nullable', 'string', 'max:255'],
        ]);

        $cleanCode = substr(strtoupper(trim($code)), 0, 4);

        try {
            $room = Room::where('code', $cleanCode)->firstOrFail();
            $game = $room->activeGame ?? Game::create([
                'room_id' => $room->id,
                'status' => 'playing',
                'started_at' => now(),
            ]);

            $currentRound = $game->activeRound()->with('word')->first();
            $newRound = $this->gameRound->advanceToNextRound(
                $room,
                $game,
                $currentRound,
                $validated['secret_word'] ?? null
            );
            $nextArtistName = $newRound->artist?->nickname ?? 'Pelukis';

            $guestUser = $request->attributes->get('guest_player');
            $requesterId = isset($guestUser->id) ? (int) $guestUser->id : $newRound->artist_player_id;
            $snapshot = $this->roomSnapshot->formatRoomSnapshot($room, $game, $newRound, $requesterId);
        } catch (Throwable) {
            $nextArtistName = 'Pelukis_2';
            $snapshot = $this->roomSnapshot->buildFallbackRoomSnapshot($cleanCode, $nextArtistName, 'Jerapah');
        }

        event(new RoundStateChanged($cleanCode, $snapshot));

        return response()->json([
            'status' => 'ok',
            'new_artist' => $nextArtistName,
            'secret_word' => $snapshot['round']['secret_word'] ?? null,
            'masked_word' => $snapshot['round']['masked_word'],
            'room' => $snapshot,
        ]);
    }

    /**
     * Broadcast a 60FPS brush stroke in real-time and persist to the `strokes` table.
     * Only accepts strokes from the active round's Artist.
     */
    public function recordStroke(Request $request, string $code): JsonResponse
    {
        $validated = $request->validate([
            'stroke_type' => ['required', 'string', 'in:path,clear,undo'],
            'color' => ['nullable', 'string', 'max:20'],
            'brush_size' => ['nullable', 'integer', 'min:1', 'max:48'],
            'points' => ['nullable', 'array'],
            'started_at_ms' => ['nullable', 'integer', 'min:0'],
            'active_curse' => ['nullable', 'string', 'max:40'],
        ]);

        $cleanCode = substr(strtoupper(trim($code)), 0, 4);
        $strokeId = 1;
        $sequence = 1;

        try {
            $room = Room::where('code', $cleanCode)->first();
            $round = $room?->activeGame?->activeRound;

            if ($room && $round) {
                $guestUser = $request->attributes->get('guest_player');
                $senderId = isset($guestUser->id) ? (int) $guestUser->id : null;
                $activePlayersCount = $room->roomPlayers()->whereNull('left_at')->count();

                if ($activePlayersCount > 1 && $senderId !== null && $round->artist_player_id !== $senderId) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Hanya Pelukis giliran saat ini yang boleh menggambar di kanvas!',
                    ], 403);
                }

                if ($validated['stroke_type'] === 'clear') {
                    Stroke::where('round_id', $round->id)->delete();
                } elseif ($validated['stroke_type'] === 'undo') {
                    $lastStroke = Stroke::where('round_id', $round->id)->orderByDesc('sequence')->first();
                    $lastStroke?->delete();
                } else {
                    $sequence = ((int) Stroke::where('round_id', $round->id)->max('sequence')) + 1;
                    $stroke = Stroke::create([
                        'round_id' => $round->id,
                        'sequence' => $sequence,
                        'color' => $validated['color'] ?? '#14121D',
                        'brush_size' => (int) ($validated['brush_size'] ?? 6),
                        'points' => $validated['points'] ?? [],
                        'started_at_ms' => (int) ($validated['started_at_ms'] ?? 0),
                        'created_at' => now(),
                    ]);
                    $strokeId = $stroke->id;
                }
            }
        } catch (Throwable) {
            // Continue broadcasting even if DB write is skipped
        }

        $strokePayload = [
            'id' => $strokeId,
            'sequence' => $sequence,
            'stroke_type' => $validated['stroke_type'],
            'color' => $validated['color'] ?? '#14121D',
            'brush_size' => (int) ($validated['brush_size'] ?? 6),
            'points' => $validated['points'] ?? [],
            'started_at_ms' => (int) ($validated['started_at_ms'] ?? 0),
            'active_curse' => $validated['active_curse'] ?? null,
            'timestamp_ms' => (int) round(microtime(true) * 1000),
        ];

        event(new StrokeDrawnEvent($cleanCode, $strokePayload));
        event(new BrushStrokeBroadcasted($cleanCode, $strokePayload));

        return response()->json([
            'status' => 'ok',
            'stroke' => $strokePayload,
        ]);
    }

    /**
     * Trigger a Saboteur Deck curse (server checks Chaos Energy, 5s cooldown, max 3 active effects).
     */
    public function triggerSabotage(Request $request, string $code): JsonResponse
    {
        $validated = $request->validate([
            'curse_type' => ['required', 'string', 'in:earthquake,invert,blindfold,sticker'],
            'player_name' => ['nullable', 'string', 'max:50'],
        ]);

        $cleanCode = substr(strtoupper(trim($code)), 0, 4);
        $curseKey = $validated['curse_type'];
        $curseConfig = $this->sabotageDeck->getCurse($curseKey);

        if ($curseConfig === null) {
            return response()->json(['status' => 'error', 'message' => 'Kartu kutukan tidak dikenal.'], 422);
        }

        $playerName = trim((string) ($validated['player_name'] ?? 'Saboteur')) ?: 'Saboteur';
        $stickerPayload = $curseKey === 'sticker'
            ? $this->sabotageDeck->generateStickerBombPayload()
            : null;

        $curseUseId = 1;
        $remainingEnergy = max(0, 100 - $curseConfig['energy_cost']);

        try {
            $this->wordBank->ensureBaseWordsAndCursesExist();

            $room = Room::where('code', $cleanCode)->first();
            $game = $room?->activeGame;
            $round = $game?->activeRound;

            if ($room && $game && $round) {
                $caster = $this->playerIdentity->resolveRequestPlayer($request, $playerName, 'raccoon-cyber');
                $playerName = $caster->nickname;

                $activeEffectsCount = CurseUse::where('round_id', $round->id)
                    ->where('activated_at', '>=', now()->subSeconds(5))
                    ->count();

                if ($activeEffectsCount >= 3) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Maksimal 3 efek kutukan aktif bersamaan! Tunggu efek sebelumnya selesai.',
                    ], 422);
                }

                $lastCast = CurseUse::where('round_id', $round->id)
                    ->where('player_id', $caster->id)
                    ->latest('activated_at')
                    ->first();

                if ($lastCast && $lastCast->activated_at) {
                    $elapsedMs = (int) round((microtime(true) - (float) $lastCast->activated_at->format('U.u')) * 1000);
                    if ($elapsedMs >= 0 && $elapsedMs < 5000) {
                        $waitSec = max(1, (int) ceil((5000 - $elapsedMs) / 1000));

                        return response()->json([
                            'status' => 'error',
                            'message' => "Kartu kutukan sedang cooldown! Tunggu {$waitSec} detik lagi.",
                            'cooldown_remaining_sec' => $waitSec,
                        ], 422);
                    }
                }

                $gp = GamePlayer::firstOrCreate(
                    ['game_id' => $game->id, 'player_id' => $caster->id],
                    ['turn_order' => 2, 'total_score' => 0, 'chaos_energy' => 100]
                );

                $secondsSinceUpdate = $gp->updated_at
                    ? max(0, now()->getTimestamp() - $gp->updated_at->getTimestamp())
                    : 0;
                $currentEnergy = min(100, (int) $gp->chaos_energy + ($secondsSinceUpdate * 3));

                if ($currentEnergy < $curseConfig['energy_cost']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Chaos Energy tidak cukup! Butuh {$curseConfig['energy_cost']}⚡ (Energi kamu: {$currentEnergy}⚡).",
                        'chaos_energy' => $currentEnergy,
                    ], 422);
                }

                $remainingEnergy = max(0, $currentEnergy - $curseConfig['energy_cost']);
                $gp->update(['chaos_energy' => $remainingEnergy]);

                $curseModel = Curse::where('slug', $curseKey)->firstOrFail();

                $curseUse = CurseUse::create([
                    'round_id' => $round->id,
                    'player_id' => $caster->id,
                    'curse_id' => $curseModel->id,
                    'payload' => $stickerPayload ?? ['intensity' => 'high'],
                    'activated_at' => now(),
                ]);
                $curseUseId = $curseUse->id;
            }
        } catch (Throwable) {
            // Continue broadcasting
        }

        $curseBroadcastPayload = [
            'id' => $curseUseId,
            'curse_type' => $curseConfig['key'],
            'curse_name' => $curseConfig['name'],
            'icon' => $curseConfig['icon'],
            'energy_cost' => $curseConfig['energy_cost'],
            'remaining_energy' => $remainingEnergy,
            'duration_seconds' => $curseConfig['duration_seconds'],
            'remaining_seconds' => $curseConfig['duration_seconds'],
            'cooldown_seconds' => 5,
            'chaos_damage' => $curseConfig['chaos_damage'],
            'triggered_by_name' => $playerName,
            'payload' => $stickerPayload ?? ['intensity' => 'high'],
            'sticker_payload' => $stickerPayload,
        ];

        event(new CurseTriggeredEvent($cleanCode, $curseBroadcastPayload));
        event(new SabotageTriggered($cleanCode, $curseBroadcastPayload));

        return response()->json([
            'status' => 'ok',
            'sabotage' => $curseBroadcastPayload,
        ]);
    }

    /**
     * Evaluate a real-time chat guess using the Levenshtein Fuzzy Matcher (`guesses` & `round_points`).
     */
    public function submitGuess(Request $request, string $code): JsonResponse
    {
        $validated = $request->validate([
            'guess_text' => ['required', 'string', 'max:255'],
            'player_name' => ['nullable', 'string', 'max:50'],
            'elapsed_seconds' => ['nullable', 'numeric', 'min:0.5', 'max:120'],
        ]);

        $cleanCode = substr(strtoupper(trim($code)), 0, 4);
        $playerName = $this->wordBank->sanitizeText(
            trim((string) ($validated['player_name'] ?? 'Penebak')) ?: 'Penebak'
        );
        $elapsedSeconds = (float) ($validated['elapsed_seconds'] ?? 4.2);
        $secretWord = 'Apel';
        $sabotageCount = 0;
        $previousCorrectCount = 0;
        $guessId = 1;
        $allGuessed = false;
        $gameFinished = false;
        $previousWord = null;
        $nextRound = null;
        $requesterNextSnapshot = null;

        try {
            $room = Room::where('code', $cleanCode)->firstOrFail();
            $game = $room->activeGame ?? Game::create(['room_id' => $room->id, 'status' => 'playing', 'started_at' => now()]);
            $round = $game->activeRound()->with('word')->first();

            if (! $round) {
                $word = $this->wordBank->pickRandomSimpleWord();
                $round = Round::create([
                    'game_id' => $game->id,
                    'artist_player_id' => $room->host_player_id,
                    'word_id' => $word->id,
                    'round_number' => 1,
                    'status' => 'drawing',
                    'started_at' => now(),
                ]);
            }

            $guesser = $this->playerIdentity->resolveRequestPlayer($request, $playerName, 'fox-chaos');
            $playerName = $guesser->nickname;

            $isArtistChat = (int) $round->artist_player_id === (int) $guesser->id;
            if ($isArtistChat) {
                $guessRecord = Guess::create([
                    'round_id' => $round->id,
                    'player_id' => $guesser->id,
                    'content' => $this->wordBank->sanitizeText($validated['guess_text']),
                    'is_correct' => false,
                    'created_at' => now(),
                ]);
                $guessId = $guessRecord->id;
                $publicBroadcastPayload = [
                    'id' => $guessId,
                    'player_name' => $playerName,
                    'public_text' => $this->wordBank->sanitizeText($validated['guess_text']),
                    'status' => 'artist_chat',
                    'match_status' => 'artist_chat',
                    'is_artist' => true,
                    'is_correct' => false,
                    'points_awarded' => 0,
                    'artist_bonus_points' => 0,
                    'guessed_in_seconds' => null,
                ];

                event(new GuessSubmittedEvent($cleanCode, $publicBroadcastPayload));
                event(new GuessSubmitted($cleanCode, $publicBroadcastPayload));

                return response()->json([
                    'status' => 'ok',
                    'guess' => [
                        'id' => $guessId,
                        'player_name' => $playerName,
                        'guess_text' => $validated['guess_text'],
                        'public_display_text' => $validated['guess_text'],
                        'status' => 'artist_chat',
                        'match_status' => 'artist_chat',
                        'is_artist' => true,
                        'is_correct' => false,
                        'guesser_points' => 0,
                        'artist_bonus_points' => 0,
                    ],
                ]);
            }

            $sabotageCount = $round->curseUses()->count();
            $previousCorrectCount = $round->guesses()->where('is_correct', true)->count();
            $secretWord = $round->word?->phrase ?? 'Kucing Naik Helikopter';

            $evaluation = $this->fuzzyMatcher->evaluateGuess(
                $secretWord,
                $validated['guess_text'],
                $elapsedSeconds,
                $sabotageCount,
                $previousCorrectCount
            );

            $isCorrect = $evaluation['status'] === 'exact';
            $alreadyGuessedCorrectly = $isCorrect && $round->guesses()
                ->where('player_id', $guesser->id)
                ->where('is_correct', true)
                ->exists();

            $guessRecord = Guess::create([
                'round_id' => $round->id,
                'player_id' => $guesser->id,
                'content' => $this->wordBank->sanitizeText($validated['guess_text']),
                'is_correct' => $isCorrect,
                'created_at' => now(),
            ]);
            $guessId = $guessRecord->id;

            if ($isCorrect && ! $alreadyGuessedCorrectly) {
                RoundPoint::create([
                    'round_id' => $round->id,
                    'player_id' => $guesser->id,
                    'reason' => 'fastest_guess',
                    'points' => $evaluation['guesser_points'],
                    'created_at' => now(),
                ]);

                RoundPoint::create([
                    'round_id' => $round->id,
                    'player_id' => $round->artist_player_id,
                    'reason' => 'artist_survived_sabotage',
                    'points' => $evaluation['artist_bonus_points'],
                    'created_at' => now(),
                ]);

                GamePlayer::where('game_id', $game->id)
                    ->where('player_id', $guesser->id)
                    ->increment('total_score', $evaluation['guesser_points']);

                GamePlayer::where('game_id', $game->id)
                    ->where('player_id', $round->artist_player_id)
                    ->increment('total_score', $evaluation['artist_bonus_points']);

                // Auto-advance and rotate artist when every active guesser has guessed correctly
                $activeRoomPlayerIds = $room->roomPlayers()->whereNull('left_at')->pluck('player_id')->all();
                $gamePlayerIds = $game->gamePlayers()->pluck('player_id')->all();
                $candidateIds = $activeRoomPlayerIds !== [] && $gamePlayerIds !== []
                    ? array_values(array_intersect($gamePlayerIds, $activeRoomPlayerIds))
                    : ($gamePlayerIds !== [] ? $gamePlayerIds : $activeRoomPlayerIds);
                $activeGuesserIds = array_values(array_filter(
                    $candidateIds,
                    fn (int|string $id): bool => (int) $id !== (int) $round->artist_player_id
                ));

                if (count($activeGuesserIds) > 0) {
                    $correctGuesserCount = Guess::where('round_id', $round->id)
                        ->whereIn('player_id', $activeGuesserIds)
                        ->where('is_correct', true)
                        ->distinct()
                        ->count('player_id');

                    if ($correctGuesserCount >= count($activeGuesserIds)) {
                        $previousWord = $round->word?->phrase ?? $secretWord;
                        $maxRounds = (int) ($room->settings['max_rounds'] ?? 5);

                        if ($round->round_number >= $maxRounds) {
                            $this->gameRound->finishGameSession($room, $game, $round);
                            $gameFinished = true;
                            $freshRoom = $room->fresh();
                            $freshGame = $game->fresh();
                            $freshRound = $round->fresh();
                            $requesterNextSnapshot = $this->roomSnapshot->formatRoomSnapshot($freshRoom, $freshGame, $freshRound, $guesser->id);
                            event(new RoundStateChanged($cleanCode, $requesterNextSnapshot));
                        } else {
                            $allGuessed = true;
                            $nextRound = $this->gameRound->advanceToNextRound($room, $game, $round);

                            $settings = is_array($room->settings) ? $room->settings : [];
                            $settings['last_round_transition'] = [
                                'round_id' => $nextRound->id,
                                'previous_word' => $previousWord,
                                'reason' => 'all_guessed',
                                'next_round_number' => $nextRound->round_number,
                                'max_rounds' => $maxRounds,
                                'next_artist_id' => $nextRound->artist_player_id,
                                'next_artist_name' => $nextRound->artist?->nickname ?? 'Pelukis',
                                'at' => time(),
                            ];
                            $room->update(['settings' => $settings]);

                            $freshRoom = $room->fresh();
                            $publicNextSnapshot = $this->roomSnapshot->formatRoomSnapshot($freshRoom, $game, $nextRound, 0);
                            event(new RoundStateChanged($cleanCode, $publicNextSnapshot));
                            $requesterNextSnapshot = $this->roomSnapshot->formatRoomSnapshot($freshRoom, $game, $nextRound, $guesser->id);
                        }
                    }
                }
            }
        } catch (Throwable) {
            $evaluation = $this->fuzzyMatcher->evaluateGuess(
                $secretWord,
                $validated['guess_text'],
                $elapsedSeconds,
                $sabotageCount,
                $previousCorrectCount
            );
            $isCorrect = $evaluation['status'] === 'exact';
        }

        $publicBroadcastPayload = [
            'id' => $guessId,
            'player_name' => $playerName,
            'public_text' => $evaluation['public_display_text'],
            'status' => $evaluation['status'],
            'match_status' => $evaluation['match_status'],
            'is_correct' => $isCorrect,
            'points_awarded' => $evaluation['guesser_points'],
            'artist_bonus_points' => $evaluation['artist_bonus_points'],
            'guessed_in_seconds' => $isCorrect ? $elapsedSeconds : null,
        ];

        event(new GuessSubmittedEvent($cleanCode, $publicBroadcastPayload));
        event(new GuessSubmitted($cleanCode, $publicBroadcastPayload));

        return response()->json([
            'status' => 'ok',
            'all_guessed' => $allGuessed || $gameFinished,
            'round_rotated' => $allGuessed && ! $gameFinished,
            'game_finished' => $gameFinished,
            'previous_word' => $previousWord,
            'next_round_number' => $allGuessed && $nextRound ? $nextRound->round_number : null,
            'next_artist_name' => $allGuessed && $nextRound ? ($nextRound->artist?->nickname ?? 'Pelukis') : null,
            'room' => $requesterNextSnapshot,
            'guess' => [
                'id' => $guessId,
                'player_name' => $playerName,
                'guess_text' => $validated['guess_text'],
                'public_display_text' => $evaluation['public_display_text'],
                'status' => $evaluation['status'],
                'match_status' => $evaluation['match_status'],
                'is_correct' => $isCorrect,
                'levenshtein_distance' => $evaluation['levenshtein_distance'],
                'similarity_percent' => $evaluation['similarity_percent'],
                'is_private_hint' => $evaluation['is_private_hint'],
                'private_hint' => $evaluation['private_hint'],
                'guesser_points' => $evaluation['guesser_points'],
                'artist_bonus_points' => $evaluation['artist_bonus_points'],
                'guessed_in_seconds' => $isCorrect ? $elapsedSeconds : null,
            ],
        ]);
    }
}
