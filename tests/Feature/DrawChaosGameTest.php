<?php

namespace Tests\Feature;

use App\Events\CurseTriggeredEvent;
use App\Events\GuessSubmittedEvent;
use App\Events\RoundStateChanged;
use App\Events\StrokeDrawnEvent;
use App\Models\Lobby;
use App\Models\LobbyPlayer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DrawChaosGameTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_and_all_five_ui_views_render_without_fake_data(): void
    {
        $response = $this->get('/coret');

        $response->assertStatus(200);
        $response->assertSee('CORET! CHAOS');
        $response->assertSee('GAMBAR. TEBAK.');
        $response->assertSee('QUICK PLAY');
        $response->assertSee('KANVAS LANGSUNG (SYNCHED 60FPS)');
        $response->assertSee('CHAOS ENERGY');
        $response->assertSee('TEBAKAN LANGSUNG');
        $response->assertSee('RONDE SELESAI!');
        $response->assertSee('Hall of Shame');

        // Verify fake hardcoded data is completely removed
        $response->assertDontSee('14,820 SENIMAN');
        $response->assertDontSee('DinoCorett');
        $response->assertDontSee('Demo Lukis Live');
        $response->assertDontSee('Uji Jadi Pelukis');
    }

    public function test_player_can_create_room_with_four_char_code_and_rotate_roles(): void
    {
        Event::fake([RoundStateChanged::class]);

        $createResponse = $this->postJson('/api/rooms', [
            'host_name' => 'SenimanSatu',
            'custom_code' => 'KOCA',
            'secret_word' => 'Kucing Naik Helikopter',
        ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('room.code', 'KOCA')
            ->assertJsonCount(1, 'room.players')
            ->assertJsonPath('room.round.secret_word', 'Kucing Naik Helikopter');

        // Second real player joins the room
        $this->withHeaders(['X-Guest-Token' => 'guest_second_player_token'])
            ->postJson('/api/rooms/join', [
                'code' => 'KOCA',
                'player_name' => 'SenimanDua',
            ])
            ->assertStatus(200)
            ->assertJsonCount(2, 'room.players');

        $initialArtist = $createResponse->json('room.round.artist_name');

        $rotateResponse = $this->postJson('/api/rooms/KOCA/rotate-round');
        $rotateResponse->assertStatus(200);

        $newArtist = $rotateResponse->json('new_artist');
        $this->assertNotSame($initialArtist, $newArtist);

        Event::assertDispatched(RoundStateChanged::class);
    }

    public function test_canvas_strokes_and_saboteur_deck_curses_broadcast_in_real_time(): void
    {
        Event::fake([StrokeDrawnEvent::class, CurseTriggeredEvent::class]);

        $this->postJson('/api/rooms', [
            'host_name' => 'SenimanSatu',
            'custom_code' => 'CHAO',
        ]);

        $strokeResponse = $this->postJson('/api/rooms/CHAO/strokes', [
            'stroke_type' => 'path',
            'color' => '#14121D',
            'brush_size' => 6,
            'points' => [
                ['x' => 0.25, 'y' => 0.35],
                ['x' => 0.55, 'y' => 0.65],
            ],
            'started_at_ms' => 240,
            'active_curse' => 'earthquake',
        ]);

        $strokeResponse->assertStatus(200)
            ->assertJsonPath('stroke.brush_size', 6);

        Event::assertDispatched(StrokeDrawnEvent::class);

        $sabotageResponse = $this->withHeaders(['X-Guest-Token' => 'guest_saboteur_token'])
            ->postJson('/api/rooms/CHAO/sabotage', [
                'curse_type' => 'invert',
                'player_name' => 'SaboteurAsli',
            ]);

        $sabotageResponse->assertStatus(200)
            ->assertJsonPath('sabotage.curse_type', 'invert')
            ->assertJsonPath('sabotage.duration_seconds', 4)
            ->assertJsonPath('sabotage.cooldown_seconds', 5)
            ->assertJsonPath('sabotage.energy_cost', 45);

        Event::assertDispatched(CurseTriggeredEvent::class);
    }

    public function test_smart_guessing_chat_handles_fuzzy_typo_and_exact_match_scoring(): void
    {
        Event::fake([GuessSubmittedEvent::class]);

        $this->postJson('/api/rooms', [
            'host_name' => 'SenimanSatu',
            'custom_code' => 'KOCA',
            'secret_word' => 'Jerapah',
        ]);

        $typoResponse = $this->postJson('/api/rooms/KOCA/guess', [
            'guess_text' => 'Jrapah',
            'player_name' => 'PenebakAsli',
            'elapsed_seconds' => 6.0,
        ]);

        $typoResponse->assertStatus(200)
            ->assertJsonPath('guess.status', 'close')
            ->assertJsonPath('guess.match_status', 'close')
            ->assertJsonPath('guess.is_private_hint', true);

        $exactResponse = $this->postJson('/api/rooms/KOCA/guess', [
            'guess_text' => 'Jerapah',
            'player_name' => 'PenebakAsli',
            'elapsed_seconds' => 4.2,
        ]);

        $exactResponse->assertStatus(200)
            ->assertJsonPath('guess.status', 'exact')
            ->assertJsonPath('guess.match_status', 'exact')
            ->assertJsonPath('guess.is_correct', true);

        $this->assertSame(350, $exactResponse->json('guess.guesser_points'));
        $this->assertGreaterThanOrEqual(120, $exactResponse->json('guess.artist_bonus_points'));

        Event::assertDispatched(GuessSubmittedEvent::class);
    }

    public function test_artworks_background_job_exports_valid_animated_gif89a_with_sarcastic_plaque(): void
    {
        Storage::fake('public');

        $this->postJson('/api/rooms', [
            'host_name' => 'SenimanSatu',
            'custom_code' => 'CHAO',
            'secret_word' => 'Kucing Naik Helikopter',
        ]);

        $this->withHeaders(['X-Guest-Token' => 'guest_saboteur_token'])
            ->postJson('/api/rooms/CHAO/sabotage', [
                'curse_type' => 'earthquake',
                'player_name' => 'SaboteurAsli',
            ]);

        $exportResponse = $this->postJson('/api/rooms/CHAO/export-gif', [
            'strokes' => [
                [
                    'stroke_type' => 'path',
                    'color' => '#14121D',
                    'size' => 6,
                    'points' => [
                        ['x' => 0.2, 'y' => 0.3],
                        ['x' => 0.8, 'y' => 0.7],
                    ],
                ],
            ],
        ]);

        $exportResponse->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'entry' => [
                    'id',
                    'round_id',
                    'exhibit_number',
                    'sarcastic_title',
                    'museum_plaque',
                    'secret_word',
                    'artist_name',
                    'curses_applied',
                    'chaos_rating',
                    'gif_download_url',
                ],
            ]);

        $this->assertStringContainsString('Karya: Kehancuran Mental', (string) $exportResponse->json('entry.museum_plaque'));

        $entryId = $exportResponse->json('entry.id');
        $gifResponse = $this->get("/api/hall-of-shame/{$entryId}/gif");

        $gifResponse->assertStatus(200)
            ->assertHeader('Content-Type', 'image/gif');

        $this->assertStringStartsWith('GIF89a', (string) $gifResponse->getContent());
    }

    public function test_player_authentication_and_profile_lifecycle(): void
    {
        $registerResponse = $this->postJson('/api/arena-auth/register', [
            'nickname' => 'KrayonKilat',
            'email' => 'krayon@drawchaos.test',
            'password' => 'secret1234',
            'avatar' => 'crayon-ninja',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('user.nickname', 'KrayonKilat')
            ->assertJsonPath('user.avatar', 'crayon-ninja')
            ->assertJsonPath('user.is_authenticated', true);

        $registeredToken = (string) $registerResponse->json('guest_token');
        $this->assertNotEmpty($registeredToken);

        $loginResponse = $this->postJson('/api/arena-auth/login', [
            'email' => 'krayon@drawchaos.test',
            'password' => 'secret1234',
            'remember' => true,
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonPath('user.email', 'krayon@drawchaos.test')
            ->assertJsonPath('user.is_authenticated', true);

        $token = (string) $loginResponse->json('guest_token');

        $profileResponse = $this->withHeaders(['X-Guest-Token' => $token])
            ->postJson('/api/arena-auth/profile', [
                'nickname' => 'RobotKilat',
                'avatar' => 'robot-rusuh',
            ]);

        $profileResponse->assertStatus(200)
            ->assertJsonPath('user.nickname', 'RobotKilat')
            ->assertJsonPath('user.avatar', 'robot-rusuh');

        $logoutResponse = $this->withHeaders(['X-Guest-Token' => $token])
            ->postJson('/api/arena-auth/logout');

        $logoutResponse->assertStatus(200)
            ->assertJsonPath('user.is_authenticated', false);

        $this->assertNotSame($token, (string) $logoutResponse->json('guest_token'));
    }

    public function test_public_lobby_listing_custom_settings_and_lobby_chat(): void
    {
        Event::fake([RoundStateChanged::class]);

        $createResponse = $this->withHeaders(['X-Guest-Token' => 'host_hub4_token'])
            ->postJson('/api/rooms', [
                'host_name' => 'HostHubEmpat',
                'custom_code' => 'HUB4',
                'is_public' => true,
                'max_players' => 4,
                'round_duration' => 120,
                'chaos_mode' => 'extreme',
                'avatar' => 'alien-duck',
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('room.code', 'HUB4')
            ->assertJsonPath('room.max_players', 4)
            ->assertJsonPath('room.round_duration', 120)
            ->assertJsonPath('room.chaos_mode', 'extreme');

        $publicListResponse = $this->getJson('/api/rooms/public');
        $publicListResponse->assertStatus(200)
            ->assertJsonFragment([
                'code' => 'HUB4',
                'max_players' => 4,
                'chaos_mode' => 'extreme',
            ]);

        $chatResponse = $this->withHeaders(['X-Guest-Token' => 'host_hub4_token'])
            ->postJson('/api/rooms/HUB4/lobby-chat', [
                'message' => 'Ayo siap tempur semua!',
                'player_name' => 'HostHubEmpat',
            ]);

        $chatResponse->assertStatus(200)
            ->assertJsonPath('chat_message.message', 'Ayo siap tempur semua!')
            ->assertJsonPath('chat_message.player_name', 'HostHubEmpat');

        foreach ([2, 3, 4] as $slot) {
            $this->withHeaders(['X-Guest-Token' => "guest_hub4_player_{$slot}"])
                ->postJson('/api/rooms/join', [
                    'code' => 'HUB4',
                    'player_name' => "Pemain{$slot}",
                    'avatar' => 'cat-neon',
                ])
                ->assertStatus(200);
        }

        $fifthJoin = $this->withHeaders(['X-Guest-Token' => 'guest_hub4_player_5'])
            ->postJson('/api/rooms/join', [
                'code' => 'HUB4',
                'player_name' => 'PemainLima',
            ]);

        $fifthJoin->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_authenticated_lobby_player_launches_into_live_coret_gameplay(): void
    {
        $host = User::factory()->create(['name' => 'HostMainGame']);
        $lobby = Lobby::create([
            'code' => 'PLAY',
            'host_id' => $host->id,
            'max_players' => 6,
            'round_duration' => 60,
            'game_mode' => 'classic',
            'word_packs' => ['Campuran'],
            'is_public' => true,
            'status' => 'playing',
        ]);

        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $host->id,
            'slot_number' => 1,
            'is_ready' => true,
            'selected_avatar' => 'kucing',
        ]);

        $response = $this->actingAs($host)->get(route('game.play', ['code' => 'PLAY']));

        $response->assertOk()
            ->assertSee('KANVAS LANGSUNG (SYNCHED 60FPS)')
            ->assertSee('CHAOS ENERGY')
            ->assertSee('TEBAKAN LANGSUNG');
    }
}
