<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Player;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('lobby.hub');
        }

        return view('auth.login');
    }

    /**
     * Show the register form.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('lobby.hub');
        }

        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /** @var array{player: Player, user: User} $result */
        $result = DB::transaction(function () use ($validated): array {
            $player = Player::create([
                'nickname' => $validated['nickname'],
            ]);

            $user = User::create([
                'name' => $validated['nickname'],
                'player_id' => $player->id,
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            return ['player' => $player, 'user' => $user];
        });

        Auth::login($result['user']);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Registrasi berhasil!',
            'redirect' => route('lobby.hub'),
        ], 201);
    }

    /**
     * Handle a login request.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $remember = $validated['remember'] ?? false;

        if (! Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']], $remember)) {
            return response()->json([
                'message' => 'Email atau password salah.',
                'errors' => ['email' => ['Email atau password salah.']],
            ], 422);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login berhasil!',
            'redirect' => route('lobby.hub'),
        ]);
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Logout berhasil.',
                'redirect' => '/login',
            ]);
        }

        return redirect('/login');
    }

    /**
     * Get the authenticated user's information.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user->load('player');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'player' => $user->player ? [
                    'id' => $user->player->id,
                    'nickname' => $user->player->nickname,
                    'avatar' => $user->player->avatar,
                ] : null,
            ],
        ]);
    }

    /**
     * Handle a forgot password request.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Link reset password telah dikirim ke email Anda.',
            ]);
        }

        return response()->json([
            'message' => 'Gagal mengirim link reset password.',
            'errors' => ['email' => [__($status)]],
        ], 422);
    }

    /**
     * Handle a password reset request.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password berhasil direset.',
            ]);
        }

        return response()->json([
            'message' => 'Gagal mereset password.',
            'errors' => ['email' => [__($status)]],
        ], 422);
    }
}
