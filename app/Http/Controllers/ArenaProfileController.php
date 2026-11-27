<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\User;
use App\Services\PlayerIdentityService;
use App\Services\WordBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ArenaProfileController extends Controller
{
    public function __construct(
        public PlayerIdentityService $playerIdentity,
        public WordBankService $wordBank,
    ) {}

    /**
     * Register a real player account (`users` table + linked `players` record).
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nickname' => ['required', 'string', 'min:2', 'max:30'],
            'email' => ['required', 'email', 'max:120'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
            'avatar' => ['nullable', 'string', 'max:50'],
        ]);

        if ($this->wordBank->containsProfanity($validated['nickname'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nickname mengandung kata kasar yang tidak diizinkan!',
            ], 422);
        }

        $cleanNickname = $this->wordBank->sanitizeText(trim($validated['nickname']));
        $email = strtolower(trim($validated['email']));
        $allowedAvatars = $this->playerIdentity->allowedAvatars();
        $avatar = in_array($validated['avatar'] ?? '', $allowedAvatars, true)
            ? (string) $validated['avatar']
            : 'cat-neon';

        $authRawToken = 'auth_user_'.substr(hash('sha256', $email), 0, 24);
        $hashedPassword = Hash::make($validated['password']);

        try {
            if (Schema::hasTable('users')) {
                if (User::where('email', $email)->exists()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Email sudah terdaftar! Silakan masuk menggunakan akun tersebut.',
                    ], 422);
                }

                $user = User::create([
                    'name' => $cleanNickname,
                    'email' => $email,
                    'password' => $hashedPassword,
                ]);
                $authRawToken = 'auth_user_'.$user->id;
            } else {
                $accounts = $this->playerIdentity->loadFallbackAuthAccounts();
                if (isset($accounts[$email])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Email sudah terdaftar! Silakan masuk menggunakan akun tersebut.',
                    ], 422);
                }
                $accounts[$email] = [
                    'nickname' => $cleanNickname,
                    'email' => $email,
                    'password_hash' => $hashedPassword,
                    'raw_token' => $authRawToken,
                ];
                $this->playerIdentity->saveFallbackAuthAccounts($accounts);
            }

            $tokenHash = hash('sha256', $authRawToken);
            $player = Player::updateOrCreate(
                ['guest_token_hash' => $tokenHash],
                [
                    'nickname' => substr($cleanNickname, 0, 50),
                    'avatar' => $avatar,
                ]
            );

            if ($request->hasSession()) {
                $request->session()->put('coret_auth_email', $email);
                $request->session()->put('guest_token', $authRawToken);
                $request->session()->put('coret_guest_token', $authRawToken);
            }

            $profile = [
                'id' => $player->id,
                'nickname' => $player->nickname,
                'avatar' => $player->avatar ?? $avatar,
                'email' => $email,
                'is_authenticated' => true,
                'raw_token' => $authRawToken,
                'total_score' => 0,
                'games_played' => 0,
                'level' => 1,
            ];

            return response()->json([
                'status' => 'ok',
                'message' => "Akun {$player->nickname} berhasil dibuat! Selamat datang di Arena.",
                'guest_token' => $authRawToken,
                'user' => $profile,
            ], 201)->withCookie($this->playerIdentity->buildGuestTokenCookie($authRawToken));
        } catch (Throwable) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat akun saat ini. Coba lagi.',
            ], 500);
        }
    }

    /**
     * Authenticate an existing registered player (`POST /api/arena-auth/login`).
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:120'],
            'password' => ['required', 'string', 'min:1', 'max:100'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $email = strtolower(trim($validated['email']));
        $authRawToken = null;
        $nickname = 'SenimanRusuh';

        try {
            if (Schema::hasTable('users')) {
                $user = User::where('email', $email)->first();
                if (! $user || ! Hash::check($validated['password'], (string) $user->password)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Email atau password salah. Periksa kembali kredensialmu!',
                    ], 422);
                }
                $authRawToken = 'auth_user_'.$user->id;
                $nickname = $user->name;
            } else {
                $accounts = $this->playerIdentity->loadFallbackAuthAccounts();
                $record = $accounts[$email] ?? null;
                if (! $record || ! Hash::check($validated['password'], (string) ($record['password_hash'] ?? ''))) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Email atau password salah. Periksa kembali kredensialmu!',
                    ], 422);
                }
                $authRawToken = (string) $record['raw_token'];
                $nickname = (string) $record['nickname'];
            }

            $tokenHash = hash('sha256', $authRawToken);
            $player = Player::firstOrCreate(
                ['guest_token_hash' => $tokenHash],
                [
                    'nickname' => substr($this->wordBank->sanitizeText($nickname), 0, 50),
                    'avatar' => 'cat-neon',
                ]
            );

            if ($request->hasSession()) {
                $request->session()->put('coret_auth_email', $email);
                $request->session()->put('guest_token', $authRawToken);
                $request->session()->put('coret_guest_token', $authRawToken);
            }

            $stats = $this->playerIdentity->computePlayerStats($player->id);
            $profile = [
                'id' => $player->id,
                'nickname' => $player->nickname,
                'avatar' => $player->avatar ?? 'cat-neon',
                'email' => $email,
                'is_authenticated' => true,
                'raw_token' => $authRawToken,
                'total_score' => $stats['total_score'],
                'games_played' => $stats['games_played'],
                'level' => $stats['level'],
            ];

            return response()->json([
                'status' => 'ok',
                'message' => "Selamat datang kembali, {$player->nickname}!",
                'guest_token' => $authRawToken,
                'user' => $profile,
            ])->withCookie($this->playerIdentity->buildGuestTokenCookie($authRawToken));
        } catch (Throwable) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi gangguan saat memverifikasi akun.',
            ], 500);
        }
    }

    /**
     * Log out the authenticated user and switch back to a fresh guest session (`POST /api/arena-auth/logout`).
     */
    public function logout(Request $request): JsonResponse
    {
        $newGuestToken = 'guest_'.Str::random(24);
        if ($request->hasSession()) {
            $request->session()->forget('coret_auth_email');
            $request->session()->put('guest_token', $newGuestToken);
            $request->session()->put('coret_guest_token', $newGuestToken);
        }

        $profile = [
            'id' => abs(crc32($newGuestToken)) % 90000 + 1000,
            'nickname' => 'SenimanRusuh_'.((abs(crc32($newGuestToken)) % 90) + 10),
            'avatar' => 'cat-neon',
            'email' => null,
            'is_authenticated' => false,
            'raw_token' => $newGuestToken,
            'total_score' => 0,
            'games_played' => 0,
            'level' => 1,
        ];

        return response()->json([
            'status' => 'ok',
            'message' => 'Kamu telah keluar dari akun dan kembali ke mode Tamu.',
            'guest_token' => $newGuestToken,
            'user' => $profile,
        ])->withCookie($this->playerIdentity->buildGuestTokenCookie($newGuestToken));
    }

    /**
     * Update current player's nickname & avatar (`POST /api/arena-auth/profile`).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nickname' => ['nullable', 'string', 'min:2', 'max:30'],
            'avatar' => ['nullable', 'string', 'max:50'],
        ]);

        if (isset($validated['nickname']) && $this->wordBank->containsProfanity($validated['nickname'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nickname mengandung kata kasar yang dilarang!',
            ], 422);
        }

        $guestUser = $request->attributes->get('guest_player');
        $allowedAvatars = $this->playerIdentity->allowedAvatars();
        $cleanName = isset($validated['nickname'])
            ? substr($this->wordBank->sanitizeText(trim($validated['nickname'])), 0, 30)
            : (string) ($guestUser->nickname ?? 'SenimanRusuh_89');
        $avatar = isset($validated['avatar']) && in_array($validated['avatar'], $allowedAvatars, true)
            ? (string) $validated['avatar']
            : (string) ($guestUser->avatar ?? 'cat-neon');

        try {
            $player = $this->playerIdentity->resolveRequestPlayer($request, $cleanName, $avatar);
            $player->update([
                'nickname' => $cleanName,
                'avatar' => $avatar,
            ]);

            if (($guestUser->is_authenticated ?? false) && Schema::hasTable('users') && ! empty($guestUser->email)) {
                User::where('email', $guestUser->email)->update(['name' => $cleanName]);
            }

            $stats = $this->playerIdentity->computePlayerStats($player->id);

            return response()->json([
                'status' => 'ok',
                'message' => 'Profil seniman diperbarui!',
                'user' => [
                    'id' => $player->id,
                    'nickname' => $player->nickname,
                    'avatar' => $player->avatar ?? $avatar,
                    'email' => $guestUser->email ?? null,
                    'is_authenticated' => (bool) ($guestUser->is_authenticated ?? false),
                    'raw_token' => $guestUser->raw_token ?? '',
                    'total_score' => $stats['total_score'],
                    'games_played' => $stats['games_played'],
                    'level' => $stats['level'],
                ],
            ]);
        } catch (Throwable) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Profil lokal diperbarui!',
                'user' => [
                    'id' => $guestUser->id ?? 1001,
                    'nickname' => $cleanName,
                    'avatar' => $avatar,
                    'email' => $guestUser->email ?? null,
                    'is_authenticated' => (bool) ($guestUser->is_authenticated ?? false),
                    'raw_token' => $guestUser->raw_token ?? '',
                    'total_score' => 0,
                    'games_played' => 0,
                    'level' => 1,
                ],
            ]);
        }
    }

    /**
     * Return current player's profile and accumulated statistics (`GET /api/arena-auth/me`).
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'user' => $this->playerIdentity->buildUserProfilePayload($request->attributes->get('guest_player')),
        ]);
    }
}
