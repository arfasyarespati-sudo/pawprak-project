<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CoretChaosController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\ProfileController;
use App\Services\SabotageDeckService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Team Landing Entry (origin/main)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome', [
        'curses' => app(SabotageDeckService::class)->deckCatalog(),
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| CORET! CHAOS Main Real-Time Game Arena & API Routes
|--------------------------------------------------------------------------
*/

Route::get('/coret', [CoretChaosController::class, 'index'])->name('coret.index');

Route::post('/broadcasting/auth', [CoretChaosController::class, 'authenticateBroadcast'])
    ->name('broadcasting.auth');

Route::prefix('api')->group(function (): void {
    Route::get('/ping', [CoretChaosController::class, 'ping'])->name('game.ping');

    // CORET! CHAOS SPA Profile & Arena Auth endpoints (separate from /api/auth/* team routes)
    Route::post('/arena-auth/register', [CoretChaosController::class, 'registerPlayer'])->name('arena-auth.register');
    Route::post('/arena-auth/login', [CoretChaosController::class, 'loginPlayer'])->name('arena-auth.login');
    Route::post('/arena-auth/logout', [CoretChaosController::class, 'logoutPlayer'])->name('arena-auth.logout');
    Route::post('/arena-auth/profile', [CoretChaosController::class, 'updatePlayerProfile'])->name('arena-auth.profile');
    Route::get('/arena-auth/me', [CoretChaosController::class, 'currentPlayerProfile'])->name('arena-auth.me');

    Route::get('/rooms/public', [CoretChaosController::class, 'listPublicRooms'])->name('rooms.public');
    Route::post('/rooms', [CoretChaosController::class, 'createRoom'])->name('rooms.store');
    Route::post('/rooms/quick-play', [CoretChaosController::class, 'quickPlay'])->name('rooms.quick-play');
    Route::post('/rooms/join', [CoretChaosController::class, 'joinRoom'])->name('rooms.join');
    Route::get('/rooms/{code}/state', [CoretChaosController::class, 'roomState'])->name('rooms.state');
    Route::post('/rooms/{code}/lobby-chat', [CoretChaosController::class, 'submitLobbyChat'])->name('rooms.lobby-chat');
    Route::post('/rooms/{code}/settings', [CoretChaosController::class, 'updateSettings'])->name('rooms.settings');
    Route::post('/rooms/{code}/ready', [CoretChaosController::class, 'toggleReady'])->name('rooms.ready');
    Route::post('/rooms/{code}/start', [CoretChaosController::class, 'startGame'])->name('rooms.start');
    Route::post('/rooms/{code}/set-word', [CoretChaosController::class, 'setSecretWord'])->name('rooms.set-word');
    Route::post('/rooms/{code}/finish-round', [CoretChaosController::class, 'finishRound'])->name('rooms.finish-round');
    Route::post('/rooms/{code}/leave', [CoretChaosController::class, 'leaveRoom'])->name('rooms.leave');
    Route::post('/rooms/{code}/rotate-round', [CoretChaosController::class, 'rotateRound'])->name('rooms.rotate');
    Route::post('/rooms/{code}/strokes', [CoretChaosController::class, 'recordStroke'])->name('rooms.strokes');
    Route::post('/rooms/{code}/sabotage', [CoretChaosController::class, 'triggerSabotage'])->name('rooms.sabotage');
    Route::post('/rooms/{code}/guess', [CoretChaosController::class, 'submitGuess'])->name('rooms.guess');
    Route::post('/rooms/{code}/rate', [CoretChaosController::class, 'rateArtwork'])->name('rooms.rate');
    Route::post('/rooms/{code}/export-gif', [CoretChaosController::class, 'exportPanicReplayGif'])->name('rooms.export-gif');
    Route::get('/museum', [CoretChaosController::class, 'listMuseum'])->name('museum.index');
    Route::get('/hall-of-shame/{id}/gif', [CoretChaosController::class, 'downloadHallOfShameGif'])->name('hall-of-shame.gif');
});

/*
|--------------------------------------------------------------------------
| Team Auth Web Routes (feat_Habibie)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/reset-password/{token}', function (string $token) {
        return redirect()->route('login', ['token' => $token]);
    })->name('password.reset');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Team Lobby & Room Management Routes (feat/arfa)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/lobby', [LobbyController::class, 'hub'])->name('lobby.hub');
    Route::post('/lobby/create', [LobbyController::class, 'store'])->name('lobby.store');
    Route::post('/lobby/join', [LobbyController::class, 'join'])->name('lobby.join');
    Route::get('/join/{code}', [LobbyController::class, 'joinByLink'])->name('lobby.join.link');
    Route::get('/lobby/{code}', [LobbyController::class, 'room'])->name('lobby.room');
    Route::get('/lobby/{code}/sync', [LobbyController::class, 'sync'])->name('lobby.sync');
    Route::post('/lobby/{code}/ready', [LobbyController::class, 'toggleReady'])->name('lobby.ready');
    Route::put('/lobby/{code}/settings', [LobbyController::class, 'updateSettings'])->name('lobby.settings');
    Route::post('/lobby/{code}/chat', [LobbyController::class, 'sendChat'])->name('lobby.chat');
    Route::post('/lobby/{code}/start', [LobbyController::class, 'start'])->name('lobby.start');
    Route::post('/lobby/{code}/leave', [LobbyController::class, 'leave'])->name('lobby.leave');
    Route::get('/game/{code}', [CoretChaosController::class, 'playFromLobby'])->name('game.play');
});
