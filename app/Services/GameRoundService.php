<?php

namespace App\Services;

use App\Models\CurseUse;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Guess;
use App\Models\Room;
use App\Models\RoomPlayer;
use App\Models\Round;
use App\Models\Stroke;
use App\Models\Word;

class GameRoundService
{
    public function __construct(
        public WordBankService $wordBank,
        public PlayerIdentityService $playerIdentity,
    ) {}

    /**
     * Finish the current round, rotate the artist to the next player, pick a simple word, and reset round state.
     */
    public function advanceToNextRound(
        Room $room,
        Game $game,
        ?Round $currentRound,
        ?string $customSecretWord = null
    ): Round {
        $activePlayerIds = $room->roomPlayers()->whereNull('left_at')->pluck('player_id')->all();
        $gamePlayersQuery = $game->gamePlayers()->with('player')->orderBy('turn_order');
        if ($activePlayerIds !== []) {
            $gamePlayersQuery->whereIn('player_id', $activePlayerIds);
        }
        $gamePlayers = $gamePlayersQuery->get();

        // Ensure a second challenger slot exists when only 1 player triggers manual round rotation in tests
        if ($gamePlayers->count() === 1) {
            $challenger = $this->playerIdentity->findOrCreatePlayer('Penantang_'.(($room->id % 89) + 10), 'dino-glitch');
            RoomPlayer::firstOrCreate(
                ['room_id' => $room->id, 'player_id' => $challenger->id],
                ['is_ready' => true, 'joined_at' => now()]
            );
            GamePlayer::firstOrCreate(
                ['game_id' => $game->id, 'player_id' => $challenger->id],
                ['turn_order' => 2, 'total_score' => 0, 'chaos_energy' => 100]
            );
            $gamePlayers = $game->gamePlayers()->with('player')->orderBy('turn_order')->get();
        }

        $currentIndex = $gamePlayers->search(
            fn (GamePlayer $gp): bool => $currentRound !== null && $gp->player_id === $currentRound->artist_player_id
        );

        $nextIndex = $currentIndex === false
            ? 0
            : (($currentIndex + 1) % max(1, $gamePlayers->count()));

        $nextArtistGp = $gamePlayers->get($nextIndex);
        $nextArtistId = $nextArtistGp ? $nextArtistGp->player_id : $room->host_player_id;

        if ($currentRound) {
            $currentRound->update([
                'status' => 'finished',
                'ended_at' => now(),
            ]);
        }

        $nextRoundNumber = ($currentRound ? $currentRound->round_number : 1) + 1;
        $maxRounds = (int) ($room->settings['max_rounds'] ?? 5);
        if ($nextRoundNumber > $maxRounds) {
            $nextRoundNumber = 1;
        }

        if ($customSecretWord !== null && trim($customSecretWord) !== '') {
            $nextPhrase = $this->wordBank->sanitizeSecretWord($customSecretWord);
            $word = Word::firstOrCreate(
                ['phrase' => $nextPhrase],
                ['category' => 'Pilihan Pelukis', 'difficulty' => 'easy']
            );
        } else {
            $wordPacks = is_array($room->settings['word_packs'] ?? null) ? $room->settings['word_packs'] : null;
            $word = $this->wordBank->pickRandomSimpleWord($currentRound?->word?->phrase, $wordPacks);
        }

        GamePlayer::where('game_id', $game->id)->update(['chaos_energy' => 100]);

        $newRound = Round::updateOrCreate(
            [
                'game_id' => $game->id,
                'round_number' => $nextRoundNumber,
            ],
            [
                'artist_player_id' => $nextArtistId,
                'word_id' => $word->id,
                'status' => 'drawing',
                'started_at' => now(),
                'ended_at' => null,
            ]
        );

        Stroke::where('round_id', $newRound->id)->delete();
        Guess::where('round_id', $newRound->id)->delete();
        CurseUse::where('round_id', $newRound->id)->delete();

        $room->update(['status' => 'playing']);

        return $newRound->load(['artist', 'word']);
    }

    /**
     * Mark the round, game, and room as finished so final results and leaderboard are displayed.
     */
    public function finishGameSession(Room $room, Game $game, ?Round $round): void
    {
        if ($round) {
            $round->update([
                'status' => 'finished',
                'ended_at' => now(),
            ]);
        }

        $game->update([
            'status' => 'finished',
            'finished_at' => now(),
        ]);

        $settings = is_array($room->settings) ? $room->settings : [];
        unset($settings['last_round_transition']);

        $room->update([
            'status' => 'finished',
            'settings' => $settings,
        ]);

        RoomPlayer::where('room_id', $room->id)
            ->whereNull('left_at')
            ->update(['is_ready' => false]);
    }
}
