<?php

use App\Http\Controllers\ArenaProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CoretChaosController;
use App\Http\Controllers\GameplayActionController;
use App\Http\Controllers\GameRoomController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\MuseumController;
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

    // CORET! CHAOS SPA Profile & Arena Auth endpoints
    Route::post('/arena-auth/register', [ArenaProfileController::class, 'register'])->name('arena-auth.register');
    Route::post('/arena-auth/login', [ArenaProfileController::class, 'login'])->name('arena-auth.login');
    Route::post('/arena-auth/logout', [ArenaProfileController::class, 'logout'])->name('arena-auth.logout');
    Route::post('/arena-auth/profile', [ArenaProfileController::class, 'updateProfile'])->name('arena-auth.profile');
    Route::get('/arena-auth/me', [ArenaProfileController::class, 'me'])->name('arena-auth.me');

    // Room & Lobby Lifecycle
    Route::get('/rooms/public', [GameRoomController::class, 'publicRooms'])->name('rooms.public');
    Route::post('/rooms', [GameRoomController::class, 'store'])->name('rooms.store');
    Route::post('/rooms/quick-play', [GameRoomController::class, 'quickPlay'])->name('rooms.quick-play');
    Route::post('/rooms/join', [GameRoomController::class, 'join'])->name('rooms.join');
    Route::get('/rooms/{code}/state', [GameRoomController::class, 'state'])->name('rooms.state');
    Route::post('/rooms/{code}/lobby-chat', [GameRoomController::class, 'lobbyChat'])->name('rooms.lobby-chat');
    Route::post('/rooms/{code}/settings', [GameRoomController::class, 'updateSettings'])->name('rooms.settings');
    Route::post('/rooms/{code}/ready', [GameRoomController::class, 'toggleReady'])->name('rooms.ready');
    Route::post('/rooms/{code}/start', [GameRoomController::class, 'start'])->name('rooms.start');
    Route::post('/rooms/{code}/leave', [GameRoomController::class, 'leave'])->name('rooms.leave');
    Route::post('/rooms/{code}/play-again', [GameRoomController::class, 'playAgain'])->name('rooms.play-again');
    Route::post('/rooms/{code}/return-lobby', [GameRoomController::class, 'returnToLobby'])->name('rooms.return-lobby');

    // Gameplay Actions (Canvas, Secret Word, Sabotage, Guessing, Round Rotation)
    Route::post('/rooms/{code}/set-word', [GameplayActionController::class, 'setSecretWord'])->name('rooms.set-word');
    Route::post('/rooms/{code}/finish-round', [GameplayActionController::class, 'finishRound'])->name('rooms.finish-round');
    Route::post('/rooms/{code}/rotate-round', [GameplayActionController::class, 'rotateRound'])->name('rooms.rotate');
    Route::post('/rooms/{code}/strokes', [GameplayActionController::class, 'recordStroke'])->name('rooms.strokes');
    Route::post('/rooms/{code}/sabotage', [GameplayActionController::class, 'triggerSabotage'])->name('rooms.sabotage');
    Route::post('/rooms/{code}/guess', [GameplayActionController::class, 'submitGuess'])->name('rooms.guess');

    // Museum & Ratings
    Route::post('/rooms/{code}/rate', [MuseumController::class, 'rateArtwork'])->name('rooms.rate');
    Route::post('/rooms/{code}/export-gif', [MuseumController::class, 'exportPanicReplayGif'])->name('rooms.export-gif');
    Route::get('/museum', [MuseumController::class, 'index'])->name('museum.index');
    Route::get('/hall-of-shame/{id}/gif', [MuseumController::class, 'downloadGif'])->name('hall-of-shame.gif');
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
