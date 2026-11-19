<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LobbyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('lobby.hub');
    }

    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Auth Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
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
| Lobby & Multiplayer Game Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // 1. Main Hub
    Route::get('/lobby', [LobbyController::class, 'hub'])->name('lobby.hub');

    // 2. Create Lobby
    Route::post('/lobby/create', [LobbyController::class, 'store'])->name('lobby.store');

    // 3. Join Lobby (Form & Link)
    Route::post('/lobby/join', [LobbyController::class, 'join'])->name('lobby.join');
    Route::get('/join/{code}', [LobbyController::class, 'joinByLink'])->name('lobby.join.link');

    // 4. Waiting Room
    Route::get('/lobby/{code}', [LobbyController::class, 'room'])->name('lobby.room');

    // 5. Live Sync (Polling JSON)
    Route::get('/lobby/{code}/sync', [LobbyController::class, 'sync'])->name('lobby.sync');

    // 6. Ready Toggle
    Route::post('/lobby/{code}/ready', [LobbyController::class, 'toggleReady'])->name('lobby.ready');

    // 7. Settings Update (Host-only)
    Route::put('/lobby/{code}/settings', [LobbyController::class, 'updateSettings'])->name('lobby.settings');

    // 8. Live Chat (AJAX)
    Route::post('/lobby/{code}/chat', [LobbyController::class, 'sendChat'])->name('lobby.chat');

    // 9. Start Game (Host-only)
    Route::post('/lobby/{code}/start', [LobbyController::class, 'start'])->name('lobby.start');

    // 10. Leave Lobby
    Route::post('/lobby/{code}/leave', [LobbyController::class, 'leave'])->name('lobby.leave');

    // 11. Gameplay Arena (Destination when started)
    Route::get('/game/{code}', [LobbyController::class, 'gameplay'])->name('game.play');
});
