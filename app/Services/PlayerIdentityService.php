<?php

namespace App\Services;

use App\Models\GamePlayer;
use App\Models\Player;
use App\Models\RoundPoint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;

class PlayerIdentityService
{
    /**
     * Canonical avatar keys used across the CORET! CHAOS arena.
     *
     * @var array<int, string>
     */
    private const ALLOWED_AVATARS = [
        'cat-neon',
        'alien-duck',
        'crayon-ninja',
        'robot-rusuh',
        'dino-glitch',
        'raccoon-cyber',
    ];

    /**
     * Legacy lobby avatar aliases mapped to canonical arena keys.
     *
     * @var array<string, string>
     */
    private const AVATAR_ALIASES = [
        'kucing' => 'cat-neon',
        'cat' => 'cat-neon',
        'bebek' => 'alien-duck',
        'alien' => 'alien-duck',
        'ninja' => 'crayon-ninja',
        'hantu' => 'crayon-ninja',
        'robot' => 'robot-rusuh',
        'dino' => 'dino-glitch',
        'rakun' => 'raccoon-cyber',
    ];

    public function __construct(
        public WordBankService $wordBank,
    ) {}

    /**
     * Return all supported avatar keys.
     *
     * @return array<int, string>
     */
    public function allowedAvatars(): array
    {
        return self::ALLOWED_AVATARS;
    }

    /**
     * Normalize either an arena key or a legacy lobby alias into a valid avatar key.
     */
    public function normalizeAvatar(?string $raw, string $default = 'cat-neon'): string
    {
        $clean = strtolower(trim((string) $raw));
        if (in_array($clean, self::ALLOWED_AVATARS, true)) {
            return $clean;
        }

        return self::AVATAR_ALIASES[$clean] ?? $default;
    }

    /**
     * Resolve or create the `Player` record from the current request's guest token or nickname.
     */
    public function resolveRequestPlayer(Request $request, string $nickname, string $avatar = 'cat-neon'): Player
    {
        $cleanName = $this->wordBank->sanitizeText(trim($nickname) ?: 'SenimanRusuh_89');
        $guestUser = $request->attributes->get('guest_player');
        $rawToken = trim((string) (
            $request->header('X-Guest-Token')
            ?? ($guestUser->raw_token ?? '')
        ));

        $validAvatar = in_array($avatar, self::ALLOWED_AVATARS, true) ? $avatar : 'cat-neon';

        if ($rawToken !== '') {
            if (preg_match('/^auth_user_(\d+)$/', $rawToken, $matches) === 1) {
                $authUser = User::with('player')->find((int) $matches[1]);
                if ($authUser?->player) {
                    return $authUser->player;
                }
            }

            $hash = hash('sha256', $rawToken);
            $player = Player::firstOrCreate(
                ['guest_token_hash' => $hash],
                ['nickname' => substr($cleanName, 0, 50), 'avatar' => $validAvatar]
            );
            $updates = [];
            if ($cleanName !== '' && $player->nickname !== $cleanName) {
                $updates['nickname'] = substr($cleanName, 0, 50);
            }
            if ($request->filled('avatar') && $player->avatar !== $validAvatar) {
                $updates['avatar'] = $validAvatar;
            }
            if ($updates !== []) {
                $player->update($updates);
            }

            return $player;
        }

        return $this->findOrCreatePlayer($cleanName, $validAvatar);
    }

    /**
     * Find or create a `players` record by nickname.
     */
    public function findOrCreatePlayer(string $nickname, string $avatar = 'cat-neon'): Player
    {
        $cleanName = $this->wordBank->sanitizeText(trim($nickname) ?: 'SenimanRusuh_89');
        $hash = hash('sha256', 'player_'.strtolower($cleanName));

        return Player::firstOrCreate(
            ['guest_token_hash' => $hash],
            [
                'nickname' => substr($cleanName, 0, 50),
                'avatar' => $avatar,
            ]
        );
    }

    /**
     * Resolve or create the linked `Player` record for an authenticated `User` joining from a lobby.
     */
    public function resolveLobbyUserPlayer(User $lobbyUser, ?string $selectedAvatar = null): Player
    {
        $avatar = $this->normalizeAvatar($selectedAvatar ?: ($lobbyUser->avatar ?? null));
        $tokenHash = hash('sha256', "auth_user_{$lobbyUser->id}");
        $player = $lobbyUser->player;

        if (! $player) {
            $player = Player::firstOrCreate(
                ['guest_token_hash' => $tokenHash],
                [
                    'nickname' => substr($lobbyUser->nickname ?: 'Seniman', 0, 50),
                    'avatar' => $avatar,
                ]
            );
            if (! $lobbyUser->player_id) {
                $lobbyUser->update(['player_id' => $player->id]);
            }
        }

        return $player;
    }

    /**
     * Compute real player score and match count from persisted tables.
     *
     * @return array{total_score: int, games_played: int, level: int}
     */
    public function computePlayerStats(int $playerId): array
    {
        try {
            $totalScore = (int) RoundPoint::where('player_id', $playerId)->sum('points');
            $gamesPlayed = (int) GamePlayer::where('player_id', $playerId)->count();
            $level = max(1, (int) floor($totalScore / 200) + 1);

            return [
                'total_score' => $totalScore,
                'games_played' => $gamesPlayed,
                'level' => $level,
            ];
        } catch (Throwable) {
            return [
                'total_score' => 0,
                'games_played' => 0,
                'level' => 1,
            ];
        }
    }

    /**
     * Build the full user/guest profile payload including real stats.
     *
     * @return array<string, mixed>
     */
    public function buildUserProfilePayload(mixed $guestUser): array
    {
        $playerId = (int) ($guestUser->id ?? 1001);
        $stats = $this->computePlayerStats($playerId);

        return [
            'id' => $playerId,
            'nickname' => (string) ($guestUser->nickname ?? 'SenimanRusuh_89'),
            'avatar' => (string) ($guestUser->avatar ?? 'cat-neon'),
            'email' => $guestUser->email ?? null,
            'is_authenticated' => (bool) ($guestUser->is_authenticated ?? false),
            'raw_token' => (string) ($guestUser->raw_token ?? ''),
            'total_score' => $stats['total_score'],
            'games_played' => $stats['games_played'],
            'level' => $stats['level'],
        ];
    }

    /**
     * Track active connected players in cache (25-second TTL heartbeat).
     */
    public function touchOnlineHeartbeat(string $token, string $nickname): int
    {
        try {
            $now = time();
            /** @var array<string, array{nickname: string, seen_at: int}> $online */
            $online = Cache::get('coret_online_players', []);
            $online[$token] = [
                'nickname' => $nickname,
                'seen_at' => $now,
            ];

            $online = array_filter(
                $online,
                fn (array $entry): bool => ($now - ($entry['seen_at'] ?? 0)) <= 25
            );

            Cache::put('coret_online_players', $online, now()->addMinutes(5));

            return max(1, count($online));
        } catch (Throwable) {
            return 1;
        }
    }

    /**
     * Build the persistent `guest_token` cookie.
     */
    public function buildGuestTokenCookie(string $rawToken): Cookie
    {
        return new Cookie(
            'guest_token',
            $rawToken,
            now()->addMinutes(60 * 24 * 30),
            '/',
            null,
            false,
            false,
            false,
            Cookie::SAMESITE_LAX
        );
    }

    /**
     * Load fallback file-based account credentials when `users` table is absent.
     *
     * @return array<string, array<string, string>>
     */
    public function loadFallbackAuthAccounts(): array
    {
        try {
            if (! Storage::disk('local')->exists('coret_accounts.json')) {
                return [];
            }
            $decoded = json_decode((string) Storage::disk('local')->get('coret_accounts.json'), true);

            return is_array($decoded) ? $decoded : [];
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Save fallback account credentials cleanly to local storage.
     *
     * @param  array<string, array<string, string>>  $accounts
     */
    public function saveFallbackAuthAccounts(array $accounts): void
    {
        try {
            Storage::disk('local')->put('coret_accounts.json', json_encode($accounts, JSON_PRETTY_PRINT));
        } catch (Throwable) {
            // Ignore write errors
        }
    }
}
