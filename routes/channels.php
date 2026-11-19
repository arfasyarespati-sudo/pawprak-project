<?php

use App\Models\Room;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{code}', function ($user = null, string $code = 'CHAO'): array {
    $cleanCode = strtoupper(substr(trim($code), 0, 4));
    $roomExists = true;

    try {
        $roomExists = Room::where('code', $cleanCode)->exists();
    } catch (Throwable) {
        $roomExists = true;
    }

    $guestPlayer = request()->attributes->get('guest_player') ?? $user;

    return [
        'id' => $guestPlayer->id ?? abs(crc32((string) request()->ip())) % 90000 + 1000,
        'nickname' => $guestPlayer->nickname ?? 'KucingNgebut',
        'avatar' => $guestPlayer->avatar ?? 'cat-neon',
        'room_code' => $cleanCode,
        'exists' => $roomExists,
    ];
});
