<?php

namespace App\Http\Middleware;

use App\Models\Player;
use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureGuestPlayerToken
{
    /**
     * Resolve or create a Guest Token player for every request (Zero-Auth Onboarding)
     * so brand-new visitors automatically get a `guest_token` cookie, `guest_token_hash`
     * in the `players` table, and a random default nickname (`SenimanRusuh_XX`).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rawToken = trim((string) (
            $request->header('X-Guest-Token')
            ?? $request->cookie('guest_token')
            ?? $request->cookie('coret_guest_token')
            ?? ($request->hasSession() ? $request->session()->get('guest_token') : null)
            ?? ($request->hasSession() ? $request->session()->get('coret_guest_token') : null)
            ?? ''
        ));

        $isNewToken = false;
        if ($rawToken === '') {
            $rawToken = 'guest_'.Str::random(24);
            $isNewToken = true;
        }

        if ($request->hasSession()) {
            $request->session()->put('guest_token', $rawToken);
            $request->session()->put('coret_guest_token', $rawToken);
        }

        $tokenHash = hash('sha256', $rawToken);
        $requestedNickname = trim((string) (
            $request->input('player_name')
            ?? $request->input('host_name')
            ?? $request->header('X-Player-Nickname')
            ?? ''
        ));

        $defaultNumber = (abs(crc32($tokenHash)) % 90) + 10;
        $defaultNickname = "SenimanRusuh_{$defaultNumber}";
        $avatars = ['cat-neon', 'dino-glitch', 'raccoon-cyber', 'ghost-mint', 'blob-yellow', 'bot-pixel'];
        $defaultAvatar = $avatars[abs(crc32($rawToken)) % count($avatars)];

        $playerId = abs(crc32($tokenHash)) % 90000 + 1000;
        $nickname = $requestedNickname !== '' ? substr($requestedNickname, 0, 50) : $defaultNickname;
        $avatar = $defaultAvatar;

        try {
            $player = Player::firstOrCreate(
                ['guest_token_hash' => $tokenHash],
                [
                    'nickname' => $nickname,
                    'avatar' => $defaultAvatar,
                ]
            );

            if ($requestedNickname !== '' && $player->nickname !== $nickname) {
                $player->update(['nickname' => $nickname]);
            }

            $playerId = $player->id;
            $nickname = $player->nickname;
            $avatar = $player->avatar ?? $defaultAvatar;
        } catch (Throwable) {
            // Fallback when DB is not yet migrated
        }

        $guestUser = new GenericUser([
            'id' => $playerId,
            'nickname' => $nickname,
            'name' => $nickname,
            'avatar' => $avatar,
            'raw_token' => $rawToken,
            'guest_token_hash' => $tokenHash,
            'is_new_guest' => $isNewToken,
        ]);

        $request->attributes->set('guest_player', $guestUser);
        $request->setUserResolver(fn (): GenericUser => $guestUser);

        /** @var Response $response */
        $response = $next($request);

        $cookieLifetimeMinutes = 60 * 24 * 30;
        $response->headers->setCookie(new Cookie(
            'guest_token',
            $rawToken,
            now()->addMinutes($cookieLifetimeMinutes),
            '/',
            null,
            false,
            false,
            false,
            Cookie::SAMESITE_LAX
        ));

        return $response;
    }
}
