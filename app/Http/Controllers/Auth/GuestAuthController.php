<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GuestAuthController extends Controller
{
    /**
     * Handle guest login by creating a guest player with a unique token.
     */
    public function login(Request $request): JsonResponse
    {
        $guestToken = Str::random(64);
        $guestTokenHash = hash('sha256', $guestToken);

        $guestNumber = Player::whereNotNull('guest_token_hash')->count() + 1;
        $nickname = 'Tamu_'.$guestNumber.'_'.Str::random(4);

        $avatars = ['kucing', 'dino', 'raccoon', 'ghost', 'blob', 'robot'];
        $avatar = $avatars[array_rand($avatars)];

        /** @var array{player: Player, user: User} $result */
        $result = DB::transaction(function () use ($nickname, $avatar, $guestTokenHash): array {
            $player = Player::create([
                'nickname' => $nickname,
                'avatar' => $avatar,
                'guest_token_hash' => $guestTokenHash,
            ]);

            $user = User::create([
                'name' => $nickname,
                'player_id' => $player->id,
                'email' => 'guest_'.strtolower(Str::random(12)).'@guest.coretchaos.local',
                'password' => Str::random(32),
                'avatar' => $avatar,
                'player_title' => 'Tamu Rusuh',
            ]);

            return [
                'player' => $player,
                'user' => $user,
            ];
        });

        Auth::login($result['user']);
        $request->session()->regenerate();

        $request->session()->put('guest_player_id', $result['player']->id);
        $request->session()->put('guest_token', $guestToken);
        $request->session()->put('is_guest', true);

        return response()->json([
            'message' => 'Login sebagai tamu berhasil!',
            'player' => [
                'id' => $result['player']->id,
                'nickname' => $result['player']->nickname,
            ],
            'guest_token' => $guestToken,
            'redirect' => redirect()->intended(route('lobby.hub'))->getTargetUrl(),
        ]);
    }

    /**
     * Validate a guest token and return the guest player info.
     */
    public function validate(Request $request): JsonResponse
    {
        $guestToken = $request->session()->get('guest_token')
            ?? $request->header('X-Guest-Token');

        if (! $guestToken) {
            return response()->json(['message' => 'Token tamu tidak ditemukan.'], 401);
        }

        $guestTokenHash = hash('sha256', $guestToken);
        $player = Player::where('guest_token_hash', $guestTokenHash)->first();

        if (! $player) {
            return response()->json(['message' => 'Token tamu tidak valid.'], 401);
        }

        return response()->json([
            'player' => [
                'id' => $player->id,
                'nickname' => $player->nickname,
                'avatar' => $player->avatar,
                'is_guest' => true,
            ],
        ]);
    }

    /**
     * Logout a guest by clearing session data.
     */
    public function logout(Request $request): JsonResponse
    {
        if (Auth::check()) {
            Auth::logout();
        }

        $request->session()->forget(['guest_player_id', 'guest_token', 'is_guest']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout tamu berhasil.',
            'redirect' => '/login',
        ]);
    }
}
