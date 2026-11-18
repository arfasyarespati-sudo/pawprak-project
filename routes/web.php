<?php

use App\Http\Controllers\CoretChaosController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CoretChaosController::class, 'index'])->name('game.index');

Route::post('/broadcasting/auth', [CoretChaosController::class, 'authenticateBroadcast'])
    ->name('broadcasting.auth');

Route::prefix('api')->group(function (): void {
    Route::get('/ping', [CoretChaosController::class, 'ping'])->name('game.ping');
    Route::post('/rooms', [CoretChaosController::class, 'createRoom'])->name('rooms.store');
    Route::post('/rooms/quick-play', [CoretChaosController::class, 'quickPlay'])->name('rooms.quick-play');
    Route::post('/rooms/join', [CoretChaosController::class, 'joinRoom'])->name('rooms.join');
    Route::get('/rooms/{code}/state', [CoretChaosController::class, 'roomState'])->name('rooms.state');
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
