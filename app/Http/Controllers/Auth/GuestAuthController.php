<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $player = Player::create([
            'nickname' => $nickname,
            'guest_token_hash' => $guestTokenHash,
        ]);

        $request->session()->put('guest_player_id', $player->id);
        $request->session()->put('guest_token', $guestToken);
        $request->session()->put('is_guest', true);

        return response()->json([
            'message' => 'Login sebagai tamu berhasil!',
            'player' => [
                'id' => $player->id,
                'nickname' => $player->nickname,
            ],
            'guest_token' => $guestToken,
            'redirect' => '/',
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
        $request->session()->forget(['guest_player_id', 'guest_token', 'is_guest']);

        return response()->json([
            'message' => 'Logout tamu berhasil.',
            'redirect' => '/login',
        ]);
    }
}
