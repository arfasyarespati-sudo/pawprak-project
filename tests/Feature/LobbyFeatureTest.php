<?php

namespace Tests\Feature;

use App\Models\Lobby;
use App\Models\LobbyMessage;
use App\Models\LobbyPlayer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LobbyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_lobby_hub(): void
    {
        $response = $this->get(route('lobby.hub'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_lobby_hub(): void
    {
        $user = User::factory()->create([
            'name' => 'BocilMaut',
            'level' => 14,
            'mmr' => 1240,
            'sabotage_accuracy' => 88,
        ]);

        $response = $this->actingAs($user)->get(route('lobby.hub'));

        $response->assertOk();
        $response->assertSee('BocilMaut');
        $response->assertSee('Level 14');
        $response->assertSee('1,240');
        $response->assertSee('88%');
        $response->assertSee('BUAT LOBBY BARU');
        $response->assertSee('GABUNG DENGAN KODE');
    }

    public function test_user_can_create_a_lobby(): void
    {
        $user = User::factory()->create(['name' => 'HostMaster']);

        $response = $this->actingAs($user)->post(route('lobby.store'), [
            'max_players' => 8,
            'round_duration' => 80,
            'game_mode' => 'classic',
            'word_packs' => ['Kultur Pop Indo'],
            'is_public' => 1,
            'avatar' => 'kucing',
        ]);

        $lobby = Lobby::first();
        $this->assertNotNull($lobby);
        $this->assertEquals(4, strlen($lobby->code));
        $this->assertEquals($user->id, $lobby->host_id);
        $this->assertEquals(8, $lobby->max_players);
        $this->assertEquals(80, $lobby->round_duration);

        $response->assertRedirect(route('lobby.room', ['code' => $lobby->code]));

        // Check host was added in slot 1 with is_ready = true
        $hostPlayer = LobbyPlayer::where('lobby_id', $lobby->id)->where('user_id', $user->id)->first();
        $this->assertNotNull($hostPlayer);
        $this->assertEquals(1, $hostPlayer->slot_number);
        $this->assertTrue($hostPlayer->is_ready);

        // Check initial system message
        $systemMsg = LobbyMessage::where('lobby_id', $lobby->id)->where('sender_type', 'system')->first();
        $this->assertNotNull($systemMsg);
        $this->assertStringContainsString("Lobby #{$lobby->code} berhasil dibuat", $systemMsg->message);
    }

    public function test_user_can_join_lobby_with_valid_code(): void
    {
        $host = User::factory()->create(['name' => 'HostPlayer']);
        $guest = User::factory()->create(['name' => 'BangJago']);

        $lobby = Lobby::create([
            'code' => 'TEST',
            'host_id' => $host->id,
            'max_players' => 8,
            'round_duration' => 80,
            'game_mode' => 'classic',
            'word_packs' => ['Kultur Pop Indo'],
            'is_public' => true,
            'status' => 'waiting',
        ]);

        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $host->id,
            'slot_number' => 1,
            'is_ready' => true,
            'selected_avatar' => 'kucing',
        ]);

        $response = $this->actingAs($guest)->post(route('lobby.join'), [
            'code' => 'test',
            'avatar' => 'raccoon',
        ]);

        $response->assertRedirect(route('lobby.room', ['code' => 'TEST']));

        $guestPlayer = LobbyPlayer::where('lobby_id', $lobby->id)->where('user_id', $guest->id)->first();
        $this->assertNotNull($guestPlayer);
        $this->assertEquals(2, $guestPlayer->slot_number);
        $this->assertFalse($guestPlayer->is_ready);
    }

    public function test_user_can_join_lobby_via_direct_link(): void
    {
        $host = User::factory()->create(['name' => 'HostPlayer']);
        $guest = User::factory()->create(['name' => 'KuasRusak']);

        $lobby = Lobby::create([
            'code' => 'LINK',
            'host_id' => $host->id,
            'max_players' => 8,
            'round_duration' => 80,
            'game_mode' => 'classic',
            'word_packs' => ['Kultur Pop Indo'],
            'is_public' => true,
            'status' => 'waiting',
        ]);

        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $host->id,
            'slot_number' => 1,
            'is_ready' => true,
        ]);

        $response = $this->actingAs($guest)->get(route('lobby.join.link', ['code' => 'LINK']));
        $response->assertRedirect(route('lobby.room', ['code' => 'LINK']));

        $this->assertDatabaseHas('lobby_players', [
            'lobby_id' => $lobby->id,
            'user_id' => $guest->id,
            'slot_number' => 2,
        ]);
    }

    public function test_non_member_cannot_view_waiting_room(): void
    {
        $host = User::factory()->create();
        $stranger = User::factory()->create();

        $lobby = Lobby::create([
            'code' => 'PRIV',
            'host_id' => $host->id,
            'max_players' => 8,
            'round_duration' => 80,
            'game_mode' => 'classic',
            'word_packs' => ['Kultur Pop Indo'],
            'is_public' => true,
            'status' => 'waiting',
        ]);

        $response = $this->actingAs($stranger)->get(route('lobby.room', ['code' => 'PRIV']));
        $response->assertRedirect(route('lobby.hub'));
    }

    public function test_sync_endpoint_returns_json_state(): void
    {
        $host = User::factory()->create(['name' => 'HostOne']);
        $guest = User::factory()->create(['name' => 'GuestOne']);

        $lobby = Lobby::create([
            'code' => 'SYNC',
            'host_id' => $host->id,
            'max_players' => 4,
            'round_duration' => 80,
            'game_mode' => 'classic',
            'word_packs' => ['Kultur Pop Indo'],
            'is_public' => true,
            'status' => 'waiting',
        ]);

        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $host->id,
            'slot_number' => 1,
            'is_ready' => true,
        ]);

        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $guest->id,
            'slot_number' => 2,
            'is_ready' => false,
        ]);

        $response = $this->actingAs($guest)->getJson(route('lobby.sync', ['code' => 'SYNC']));

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'lobby' => ['code', 'host_id', 'max_players', 'total_players', 'ready_count', 'can_start'],
            'current_user' => ['id', 'is_host', 'is_ready', 'slot_number'],
            'slots',
            'messages',
        ]);

        $response->assertJson([
            'lobby' => [
                'code' => 'SYNC',
                'total_players' => 2,
                'can_start' => false, // Needs at least 3 players
            ],
            'current_user' => [
                'id' => $guest->id,
                'is_host' => false,
                'is_ready' => false,
                'slot_number' => 2,
            ],
        ]);
    }

    public function test_guest_can_toggle_ready_state(): void
    {
        $host = User::factory()->create();
        $guest = User::factory()->create();

        $lobby = Lobby::create([
            'code' => 'REDY',
            'host_id' => $host->id,
            'max_players' => 8,
            'round_duration' => 80,
            'game_mode' => 'classic',
            'word_packs' => ['Kultur Pop Indo'],
            'is_public' => true,
            'status' => 'waiting',
        ]);

        $guestPlayer = LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $guest->id,
            'slot_number' => 2,
            'is_ready' => false,
        ]);

        $response = $this->actingAs($guest)->postJson(route('lobby.ready', ['code' => 'REDY']));
        $response->assertOk();
        $response->assertJson(['success' => true, 'is_ready' => true]);

        $this->assertTrue($guestPlayer->fresh()->is_ready);

        // Toggle back
        $response2 = $this->actingAs($guest)->postJson(route('lobby.ready', ['code' => 'REDY']));
        $response2->assertOk();
        $response2->assertJson(['success' => true, 'is_ready' => false]);
    }

    public function test_host_can_update_settings(): void
    {
        $host = User::factory()->create();
        $guest = User::factory()->create();

        $lobby = Lobby::create([
            'code' => 'SETS',
            'host_id' => $host->id,
            'max_players' => 8,
            'round_duration' => 80,
            'game_mode' => 'classic',
            'word_packs' => ['Kultur Pop Indo'],
            'is_public' => true,
            'status' => 'waiting',
        ]);

        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $host->id,
            'slot_number' => 1,
            'is_ready' => true,
        ]);

        // Non-host cannot update
        $guestResponse = $this->actingAs($guest)->putJson(route('lobby.settings', ['code' => 'SETS']), [
            'round_duration' => 100,
        ]);
        $guestResponse->assertForbidden();

        // Host can update
        $hostResponse = $this->actingAs($host)->putJson(route('lobby.settings', ['code' => 'SETS']), [
            'round_duration' => 120,
            'game_mode' => 'extreme',
        ]);
        $hostResponse->assertOk();
        $this->assertEquals(120, $lobby->fresh()->round_duration);
        $this->assertEquals('extreme', $lobby->fresh()->game_mode);
    }

    public function test_user_can_send_chat_message(): void
    {
        $host = User::factory()->create(['name' => 'BocilMaut']);

        $lobby = Lobby::create([
            'code' => 'CHAT',
            'host_id' => $host->id,
            'max_players' => 8,
            'round_duration' => 80,
            'game_mode' => 'classic',
            'word_packs' => ['Kultur Pop Indo'],
            'is_public' => true,
            'status' => 'waiting',
        ]);

        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $host->id,
            'slot_number' => 1,
            'is_ready' => true,
        ]);

        $response = $this->actingAs($host)->postJson(route('lobby.chat', ['code' => 'CHAT']), [
            'message' => 'Santai, nunggu 1-2 orang lagi langsung gas start ya!',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => [
                'message' => 'Santai, nunggu 1-2 orang lagi langsung gas start ya!',
                'sender_type' => 'player',
            ],
        ]);

        $this->assertDatabaseHas('lobby_messages', [
            'lobby_id' => $lobby->id,
            'user_id' => $host->id,
            'message' => 'Santai, nunggu 1-2 orang lagi langsung gas start ya!',
        ]);
    }

    public function test_host_cannot_start_with_less_than_3_players(): void
    {
        $host = User::factory()->create();
        $lobby = Lobby::create([
            'code' => 'STRT',
            'host_id' => $host->id,
            'max_players' => 8,
            'round_duration' => 80,
            'game_mode' => 'classic',
            'word_packs' => ['Kultur Pop Indo'],
            'is_public' => true,
            'status' => 'waiting',
        ]);

        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $host->id,
            'slot_number' => 1,
            'is_ready' => true,
        ]);

        $response = $this->actingAs($host)->postJson(route('lobby.start', ['code' => 'STRT']));
        $response->assertStatus(422);
    }

    public function test_host_can_start_when_3_or_more_players_present(): void
    {
        $users = User::factory()->count(3)->create();
        $host = $users[0];

        $lobby = Lobby::create([
            'code' => 'GOOO',
            'host_id' => $host->id,
            'max_players' => 8,
            'round_duration' => 80,
            'game_mode' => 'classic',
            'word_packs' => ['Kultur Pop Indo'],
            'is_public' => true,
            'status' => 'waiting',
        ]);

        foreach ($users as $index => $u) {
            LobbyPlayer::create([
                'lobby_id' => $lobby->id,
                'user_id' => $u->id,
                'slot_number' => $index + 1,
                'is_ready' => true,
            ]);
        }

        $response = $this->actingAs($host)->postJson(route('lobby.start', ['code' => 'GOOO']));
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'redirect' => route('game.play', ['code' => 'GOOO']),
        ]);

        $this->assertEquals('playing', $lobby->fresh()->status);
    }

    public function test_host_leaving_promotes_lowest_slot_player(): void
    {
        $host = User::factory()->create(['name' => 'OldHost']);
        $guest1 = User::factory()->create(['name' => 'NextHost']);
        $guest2 = User::factory()->create(['name' => 'GuestTwo']);

        $lobby = Lobby::create([
            'code' => 'LEAV',
            'host_id' => $host->id,
            'max_players' => 8,
            'round_duration' => 80,
            'game_mode' => 'classic',
            'word_packs' => ['Kultur Pop Indo'],
            'is_public' => true,
            'status' => 'waiting',
        ]);

        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $host->id,
            'slot_number' => 1,
            'is_ready' => true,
        ]);

        $gp1 = LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $guest1->id,
            'slot_number' => 2,
            'is_ready' => false,
        ]);

        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $guest2->id,
            'slot_number' => 3,
            'is_ready' => false,
        ]);

        $response = $this->actingAs($host)->post(route('lobby.leave', ['code' => 'LEAV']));
        $response->assertRedirect(route('lobby.hub'));

        // Guest1 should now be host and ready
        $this->assertEquals($guest1->id, $lobby->fresh()->host_id);
        $this->assertTrue($gp1->fresh()->is_ready);
    }

    public function test_last_player_leaving_deletes_lobby(): void
    {
        $user = User::factory()->create();

        $lobby = Lobby::create([
            'code' => 'GONE',
            'host_id' => $user->id,
            'max_players' => 8,
            'round_duration' => 80,
            'game_mode' => 'classic',
            'word_packs' => ['Kultur Pop Indo'],
            'is_public' => true,
            'status' => 'waiting',
        ]);

        LobbyPlayer::create([
            'lobby_id' => $lobby->id,
            'user_id' => $user->id,
            'slot_number' => 1,
            'is_ready' => true,
        ]);

        $response = $this->actingAs($user)->post(route('lobby.leave', ['code' => 'GONE']));
        $response->assertRedirect(route('lobby.hub'));

        $this->assertNull(Lobby::find($lobby->id));
    }
}
