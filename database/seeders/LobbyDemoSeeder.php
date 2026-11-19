<?php

namespace Database\Seeders;

use App\Models\Lobby;
use App\Models\LobbyMessage;
use App\Models\LobbyPlayer;
use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LobbyDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or update test users
        $usersData = [
            [
                'name' => 'BocilMaut',
                'email' => 'bocil@chaos.test',
                'password' => Hash::make('password'),
                'level' => 14,
                'mmr' => 1240,
                'sabotage_accuracy' => 88,
                'player_title' => 'Master Spidol #1',
                'avatar' => 'kucing',
            ],
            [
                'name' => 'BangJago',
                'email' => 'bangjago@chaos.test',
                'password' => Hash::make('password'),
                'level' => 28,
                'mmr' => 1450,
                'sabotage_accuracy' => 92,
                'player_title' => 'Raja Sabotase',
                'avatar' => 'raccoon',
            ],
            [
                'name' => 'KuasRusak',
                'email' => 'kuasrusak@chaos.test',
                'password' => Hash::make('password'),
                'level' => 12,
                'mmr' => 980,
                'sabotage_accuracy' => 65,
                'player_title' => 'Spam Tebakan',
                'avatar' => 'ghost',
            ],
        ];

        $users = [];
        foreach ($usersData as $userData) {
            $player = Player::firstOrCreate(
                ['nickname' => $userData['name']],
                [
                    'avatar' => $userData['avatar'],
                    'guest_token_hash' => hash('sha256', $userData['name']),
                ]
            );

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'player_id' => $player->id,
                    'password' => $userData['password'],
                    'level' => $userData['level'],
                    'mmr' => $userData['mmr'],
                    'sabotage_accuracy' => $userData['sabotage_accuracy'],
                    'player_title' => $userData['player_title'],
                    'avatar' => $userData['avatar'],
                ]
            );

            $users[$userData['name']] = $user;
        }

        // 2. Create Demo Lobby 'CHAO' matching Mockup 3
        $lobby = Lobby::updateOrCreate(
            ['code' => 'CHAO'],
            [
                'host_id' => $users['BocilMaut']->id,
                'max_players' => 8,
                'round_duration' => 80,
                'game_mode' => 'classic',
                'word_packs' => ['Kultur Pop Indo', 'Istilah Game & Esports'],
                'is_public' => true,
                'status' => 'waiting',
            ]
        );

        // Clear existing demo players & messages for CHAO if re-running
        $lobby->players()->delete();
        $lobby->messages()->delete();

        // Slot 1: BocilMaut (Host)
        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $users['BocilMaut']->id,
            'slot_number' => 1,
            'is_ready' => true,
            'selected_avatar' => 'kucing',
        ]);

        // Slot 2: BangJago
        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $users['BangJago']->id,
            'slot_number' => 2,
            'is_ready' => true,
            'selected_avatar' => 'raccoon',
        ]);

        // Slot 3: KuasRusak
        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $users['KuasRusak']->id,
            'slot_number' => 3,
            'is_ready' => true,
            'selected_avatar' => 'ghost',
        ]);

        // 3. Demo Messages
        $now = now();
        LobbyMessage::create([
            'lobby_id' => $lobby->id,
            'user_id' => null,
            'sender_type' => 'system',
            'message' => 'Lobby #CHAO berhasil dibuat. Minimal 3 pemain sudah tercapai!',
            'created_at' => $now->copy()->subMinutes(3),
        ]);

        LobbyMessage::create([
            'lobby_id' => $lobby->id,
            'user_id' => $users['BangJago']->id,
            'sender_type' => 'player',
            'message' => 'Udah siap gue bantai lu pada wkwk, kartu sabotase gue udah gacor!',
            'created_at' => $now->copy()->subMinutes(2),
        ]);

        LobbyMessage::create([
            'lobby_id' => $lobby->id,
            'user_id' => $users['KuasRusak']->id,
            'sender_type' => 'player',
            'message' => 'Jangan gempa plis tangan gue kaku abis hujan 😭',
            'created_at' => $now->copy()->subMinute(),
        ]);

        LobbyMessage::create([
            'lobby_id' => $lobby->id,
            'user_id' => $users['BocilMaut']->id,
            'sender_type' => 'player',
            'message' => 'Santai, nunggu 1-2 orang lagi langsung gas start ya!',
            'created_at' => $now,
        ]);
    }
}
