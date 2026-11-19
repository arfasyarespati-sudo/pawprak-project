<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use App\Models\LobbyMessage;
use App\Models\LobbyPlayer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LobbyController extends Controller
{
    /**
     * Render the Main Hub.
     */
    public function hub(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $activeLobbiesCount = Lobby::where('status', 'waiting')->count();
        $publicLobbies = Lobby::with(['host', 'players.user'])
            ->where('status', 'waiting')
            ->where('is_public', true)
            ->latest()
            ->take(6)
            ->get();

        // Dynamic online count for arcade feel
        $onlineCount = 14280 + (LobbyPlayer::count() * 7);

        return view('lobby.hub', [
            'user' => $user,
            'activeLobbiesCount' => $activeLobbiesCount,
            'publicLobbies' => $publicLobbies,
            'onlineCount' => $onlineCount,
        ]);
    }

    /**
     * Handle creating a new lobby.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'max_players' => ['required', 'integer', 'in:4,6,8,12'],
            'round_duration' => ['required', 'integer', 'in:60,80,100,120'],
            'game_mode' => ['required', 'string', 'in:classic,relaxed,extreme'],
            'word_packs' => ['required', 'array', 'min:1'],
            'word_packs.*' => ['string', 'max:50'],
            'is_public' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'string', 'max:50'],
        ]);

        $code = Lobby::generateUniqueCode();
        $isPublic = $request->boolean('is_public', true);

        /** @var Lobby $lobby */
        $lobby = DB::transaction(function () use ($user, $code, $validated, $isPublic): Lobby {
            // Leave any waiting lobbies the user is currently in to prevent stale slot locks
            LobbyPlayer::where('user_id', $user->id)
                ->whereHas('lobby', function ($query) {
                    $query->where('status', 'waiting');
                })
                ->delete();

            $avatar = $validated['avatar'] ?? $user->avatar ?? 'kucing';
            if (! empty($validated['avatar']) && $user->avatar !== $validated['avatar']) {
                $user->avatar = $validated['avatar'];
                $user->save();
            }

            $lobby = Lobby::create([
                'code' => $code,
                'host_id' => $user->id,
                'max_players' => (int) $validated['max_players'],
                'round_duration' => (int) $validated['round_duration'],
                'game_mode' => $validated['game_mode'],
                'word_packs' => array_values($validated['word_packs']),
                'is_public' => $isPublic,
                'status' => 'waiting',
            ]);

            LobbyPlayer::create([
                'lobby_id' => $lobby->id,
                'user_id' => $user->id,
                'slot_number' => 1,
                'is_ready' => true,
                'selected_avatar' => $avatar,
            ]);

            LobbyMessage::create([
                'lobby_id' => $lobby->id,
                'user_id' => null,
                'sender_type' => 'system',
                'message' => "Lobby #{$code} berhasil dibuat. Minimal 3 pemain untuk memulai!",
            ]);

            return $lobby;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'code' => $lobby->code,
                'redirect' => route('lobby.room', ['code' => $lobby->code]),
            ]);
        }

        return redirect()->route('lobby.room', ['code' => $lobby->code])
            ->with('success', "Lobby #{$lobby->code} berhasil dibuat!");
    }

    /**
     * Join a lobby via form submission or API.
     */
    public function join(Request $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:4'],
            'avatar' => ['nullable', 'string', 'max:50'],
        ]);

        $code = strtoupper(trim($validated['code']));
        $avatar = $validated['avatar'] ?? $user->avatar ?? 'kucing';

        $lobby = Lobby::where('code', $code)->first();

        if (! $lobby) {
            $error = 'Kode lobby tidak ditemukan. Periksa kembali 4 huruf kode ruangan.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $error], 404);
            }

            return back()->withInput()->withErrors(['code' => $error]);
        }

        if ($lobby->status === 'playing') {
            $error = 'Pertandingan sudah dimulai! Kamu tidak dapat bergabung ke ruangan ini.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }

            return back()->withInput()->withErrors(['code' => $error]);
        }

        if ($lobby->status === 'finished') {
            $error = 'Lobby ini sudah selesai.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }

            return back()->withInput()->withErrors(['code' => $error]);
        }

        // Check if user is already a member of this lobby
        $existingMembership = LobbyPlayer::where('lobby_id', $lobby->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingMembership) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'code' => $lobby->code,
                    'redirect' => route('lobby.room', ['code' => $lobby->code]),
                ]);
            }

            return redirect()->route('lobby.room', ['code' => $lobby->code]);
        }

        if ($lobby->isFull()) {
            $error = "Lobby #{$code} sudah penuh (maksimal {$lobby->max_players} pemain).";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }

            return back()->withInput()->withErrors(['code' => $error]);
        }

        $slot = $lobby->availableSlot();
        if ($slot === null) {
            $error = 'Semua slot di lobi ini sudah terisi.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }

            return back()->withInput()->withErrors(['code' => $error]);
        }

        DB::transaction(function () use ($lobby, $user, $slot, $avatar) {
            if ($avatar && $user->avatar !== $avatar) {
                $user->avatar = $avatar;
                $user->save();
            }

            LobbyPlayer::create([
                'lobby_id' => $lobby->id,
                'user_id' => $user->id,
                'slot_number' => $slot,
                'is_ready' => false,
                'selected_avatar' => $avatar,
            ]);

            LobbyMessage::create([
                'lobby_id' => $lobby->id,
                'user_id' => null,
                'sender_type' => 'system',
                'message' => "{$user->nickname} bergabung ke dalam lobby di Slot #{$slot}.",
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'code' => $lobby->code,
                'redirect' => route('lobby.room', ['code' => $lobby->code]),
            ]);
        }

        return redirect()->route('lobby.room', ['code' => $lobby->code]);
    }

    /**
     * Join a lobby directly via invite URL: /join/{code}.
     */
    public function joinByLink(Request $request, string $code): RedirectResponse
    {
        $request->merge(['code' => $code]);

        return $this->join($request);
    }

    /**
     * Render the Waiting Room.
     */
    public function room(Request $request, string $code): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $code = strtoupper(trim($code));

        $lobby = Lobby::where('code', $code)
            ->with(['host', 'players.user', 'messages.user'])
            ->first();

        if (! $lobby) {
            return redirect()->route('lobby.hub')
                ->with('error', "Lobby #{$code} tidak ditemukan.");
        }

        $isMember = $lobby->players->contains('user_id', $user->id);
        if (! $isMember) {
            return redirect()->route('lobby.hub')
                ->with('error', 'Kamu belum bergabung di lobby ini. Silakan masukkan kode untuk bergabung.');
        }

        if ($lobby->status === 'playing') {
            return redirect()->route('game.play', ['code' => $lobby->code]);
        }

        return view('lobby.room', [
            'lobby' => $lobby,
            'user' => $user,
            'isHost' => $lobby->isHost($user->id),
        ]);
    }

    /**
     * Live Sync API polled by the frontend every 2 seconds.
     */
    public function sync(Request $request, string $code): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $code = strtoupper(trim($code));

        $lobby = Lobby::where('code', $code)
            ->with(['host', 'players.user', 'messages' => function ($q) {
                $q->latest()->take(60);
            }, 'messages.user'])
            ->first();

        if (! $lobby) {
            return response()->json([
                'error' => 'Lobby not found',
                'redirect' => route('lobby.hub'),
            ], 404);
        }

        $currentPlayer = $lobby->players->firstWhere('user_id', $user->id);
        if (! $currentPlayer && $lobby->status === 'waiting') {
            return response()->json([
                'error' => 'Not in lobby',
                'redirect' => route('lobby.hub'),
            ], 403);
        }

        $isHost = $lobby->isHost($user->id);
        $totalPlayers = $lobby->players->count();
        $readyCount = $lobby->readyCount();
        $canStart = $lobby->canStart();

        // Build structured slots 1..max_players
        $slots = [];
        $playersBySlot = $lobby->players->keyBy('slot_number');

        for ($slotNum = 1; $slotNum <= $lobby->max_players; $slotNum++) {
            $player = $playersBySlot->get($slotNum);

            if ($player && $player->user) {
                $isPlayerHost = ((int) $player->user_id === (int) $lobby->host_id);
                $slots[] = [
                    'slot_number' => $slotNum,
                    'is_occupied' => true,
                    'user_id' => $player->user_id,
                    'name' => $player->user->nickname,
                    'level' => $player->user->level ?? 1,
                    'title' => $player->user->player_title ?? 'Pemula Spidol',
                    'avatar' => $player->selected_avatar ?? $player->user->avatar ?? 'kucing',
                    'is_host' => $isPlayerHost,
                    'is_ready' => $player->is_ready,
                    'is_current_user' => ((int) $player->user_id === (int) $user->id),
                ];
            } else {
                $slots[] = [
                    'slot_number' => $slotNum,
                    'is_occupied' => false,
                    'user_id' => null,
                    'name' => null,
                    'level' => null,
                    'title' => null,
                    'avatar' => null,
                    'is_host' => false,
                    'is_ready' => false,
                    'is_current_user' => false,
                ];
            }
        }

        // Format messages chronologically
        $messages = $lobby->messages
            ->sortBy('created_at')
            ->values()
            ->map(function (LobbyMessage $msg) use ($user, $lobby) {
                $isCurrentUser = ((int) $msg->user_id === (int) $user->id);
                $isHostSender = $msg->user_id ? $lobby->isHost($msg->user_id) : false;

                $senderName = 'SYSTEM';
                if ($msg->sender_type === 'player' && $msg->user) {
                    if ($isCurrentUser) {
                        $senderName = $isHostSender ? 'Kamu (Host)' : 'Kamu';
                    } else {
                        $senderName = $msg->user->nickname.($isHostSender ? ' (Host)' : '');
                    }
                }

                return [
                    'id' => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'user_id' => $msg->user_id,
                    'sender_name' => $senderName,
                    'is_current_user' => $isCurrentUser,
                    'message' => $msg->message,
                    'time' => $msg->created_at->format('H:i'),
                ];
            });

        return response()->json([
            'status' => $lobby->status,
            'game_url' => route('game.play', ['code' => $lobby->code]),
            'lobby' => [
                'code' => $lobby->code,
                'host_id' => $lobby->host_id,
                'max_players' => $lobby->max_players,
                'round_duration' => $lobby->round_duration,
                'game_mode' => $lobby->game_mode,
                'word_packs' => $lobby->word_packs ?? [],
                'is_public' => $lobby->is_public,
                'total_players' => $totalPlayers,
                'ready_count' => $readyCount,
                'can_start' => $canStart,
            ],
            'current_user' => [
                'id' => $user->id,
                'is_host' => $isHost,
                'is_ready' => $currentPlayer ? $currentPlayer->is_ready : false,
                'slot_number' => $currentPlayer ? $currentPlayer->slot_number : null,
            ],
            'slots' => $slots,
            'messages' => $messages,
        ]);
    }

    /**
     * Toggle player ready state.
     */
    public function toggleReady(Request $request, string $code): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $code = strtoupper(trim($code));

        $lobby = Lobby::where('code', $code)->firstOrFail();
        $player = LobbyPlayer::where('lobby_id', $lobby->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Host is always ready
        if ($lobby->isHost($user->id)) {
            $player->is_ready = true;
            $player->save();

            return response()->json([
                'success' => true,
                'is_ready' => true,
                'message' => 'Host selalu siap tempur!',
            ]);
        }

        $player->is_ready = ! $player->is_ready;
        $player->save();

        return response()->json([
            'success' => true,
            'is_ready' => $player->is_ready,
            'message' => $player->is_ready ? 'Kamu sekarang SIAP!' : 'Kamu membatalkan status SIAP.',
        ]);
    }

    /**
     * Update lobby settings (Host-only).
     */
    public function updateSettings(Request $request, string $code): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $code = strtoupper(trim($code));

        $lobby = Lobby::where('code', $code)->firstOrFail();

        if (! $lobby->isHost($user->id)) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Hanya host yang dapat mengubah pengaturan.'], 403);
            }
            abort(403, 'Hanya host yang dapat mengubah pengaturan.');
        }

        $validated = $request->validate([
            'max_players' => ['nullable', 'integer', 'in:4,6,8,12'],
            'round_duration' => ['nullable', 'integer', 'in:60,80,100,120'],
            'game_mode' => ['nullable', 'string', 'in:classic,relaxed,extreme'],
            'word_packs' => ['nullable', 'array', 'min:1'],
            'word_packs.*' => ['string', 'max:50'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $currentPlayersCount = $lobby->players()->count();
        if (isset($validated['max_players']) && $validated['max_players'] < $currentPlayersCount) {
            $error = "Tidak dapat mengurangi kapasitas ke {$validated['max_players']} karena sudah ada {$currentPlayersCount} pemain di lobi.";
            if ($request->wantsJson()) {
                return response()->json(['error' => $error], 422);
            }

            return back()->with('error', $error);
        }

        if (isset($validated['max_players'])) {
            $lobby->max_players = (int) $validated['max_players'];
        }
        if (isset($validated['round_duration'])) {
            $lobby->round_duration = (int) $validated['round_duration'];
        }
        if (isset($validated['game_mode'])) {
            $lobby->game_mode = $validated['game_mode'];
        }
        if (isset($validated['word_packs'])) {
            $lobby->word_packs = array_values($validated['word_packs']);
        }
        if ($request->has('is_public')) {
            $lobby->is_public = $request->boolean('is_public');
        }

        $lobby->save();

        LobbyMessage::create([
            'lobby_id' => $lobby->id,
            'user_id' => null,
            'sender_type' => 'system',
            'message' => "Host memperbarui aturan lobi (Mode: {$lobby->game_mode}, Durasi: {$lobby->round_duration}s, Kapasitas: {$lobby->max_players}).",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan lobi berhasil diperbarui!',
                'lobby' => $lobby,
            ]);
        }

        return back()->with('success', 'Pengaturan lobi berhasil diperbarui!');
    }

    /**
     * Send chat message.
     */
    public function sendChat(Request $request, string $code): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $code = strtoupper(trim($code));

        $lobby = Lobby::where('code', $code)->firstOrFail();

        // Check if user is in lobby
        $isMember = $lobby->players()->where('user_id', $user->id)->exists();
        if (! $isMember) {
            return response()->json(['error' => 'Kamu bukan anggota lobi ini.'], 403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:250'],
        ]);

        $chat = LobbyMessage::create([
            'lobby_id' => $lobby->id,
            'user_id' => $user->id,
            'sender_type' => 'player',
            'message' => trim($validated['message']),
        ]);

        $isHost = $lobby->isHost($user->id);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $chat->id,
                'sender_type' => 'player',
                'user_id' => $user->id,
                'sender_name' => $isHost ? 'Kamu (Host)' : 'Kamu',
                'is_current_user' => true,
                'message' => $chat->message,
                'time' => $chat->created_at->format('H:i'),
            ],
        ]);
    }

    /**
     * Host starts the game.
     */
    public function start(Request $request, string $code): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $code = strtoupper(trim($code));

        $lobby = Lobby::where('code', $code)->firstOrFail();

        if (! $lobby->isHost($user->id)) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Hanya host yang dapat memulai permainan.'], 403);
            }
            abort(403, 'Hanya host yang dapat memulai permainan.');
        }

        if (! $lobby->canStart()) {
            $error = 'Minimal 3 pemain untuk memulai permainan!';
            if ($request->wantsJson()) {
                return response()->json(['error' => $error], 422);
            }

            return back()->with('error', $error);
        }

        $lobby->status = 'playing';
        $lobby->save();

        LobbyMessage::create([
            'lobby_id' => $lobby->id,
            'user_id' => null,
            'sender_type' => 'system',
            'message' => 'Pertandingan dimulai! Bersiaplah meluncur ke kanvas!',
        ]);

        $gameUrl = route('game.play', ['code' => $lobby->code]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => $gameUrl,
            ]);
        }

        return redirect()->to($gameUrl);
    }

    /**
     * Leave the lobby.
     */
    public function leave(Request $request, string $code): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $code = strtoupper(trim($code));

        $lobby = Lobby::where('code', $code)->first();

        if (! $lobby) {
            return redirect()->route('lobby.hub');
        }

        $player = LobbyPlayer::where('lobby_id', $lobby->id)
            ->where('user_id', $user->id)
            ->first();

        if ($player) {
            $isHost = $lobby->isHost($user->id);
            $player->delete();

            $remainingPlayers = LobbyPlayer::where('lobby_id', $lobby->id)
                ->orderBy('slot_number')
                ->get();

            if ($remainingPlayers->isEmpty()) {
                // Delete lobby if empty
                $lobby->delete();
            } else {
                if ($isHost) {
                    // Promote lowest slot number to host
                    $newHostPlayer = $remainingPlayers->first();
                    $lobby->host_id = $newHostPlayer->user_id;
                    $lobby->save();

                    $newHostPlayer->is_ready = true;
                    $newHostPlayer->save();

                    $newHostUser = $newHostPlayer->user;
                    $newHostName = $newHostUser ? $newHostUser->nickname : 'Pemain';

                    LobbyMessage::create([
                        'lobby_id' => $lobby->id,
                        'user_id' => null,
                        'sender_type' => 'system',
                        'message' => "{$user->nickname} keluar. {$newHostName} sekarang menjadi Host!",
                    ]);
                } else {
                    LobbyMessage::create([
                        'lobby_id' => $lobby->id,
                        'user_id' => null,
                        'sender_type' => 'system',
                        'message' => "{$user->nickname} telah meninggalkan lobi.",
                    ]);
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('lobby.hub'),
            ]);
        }

        return redirect()->route('lobby.hub')
            ->with('info', 'Kamu telah keluar dari lobi.');
    }

    /**
     * Gameplay arena view when game starts.
     */
    public function gameplay(Request $request, string $code): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $code = strtoupper(trim($code));

        $lobby = Lobby::where('code', $code)->first();

        if (! $lobby) {
            return redirect()->route('lobby.hub')->with('error', 'Lobby tidak ditemukan.');
        }

        $isMember = $lobby->players()->where('user_id', $user->id)->exists();
        if (! $isMember) {
            return redirect()->route('lobby.hub')->with('error', 'Kamu bukan anggota room ini.');
        }

        return view('lobby.gameplay', [
            'lobby' => $lobby,
            'user' => $user,
        ]);
    }
}
