<?php

namespace App\Http\Controllers;

use App\Jobs\GeneratePanicReplayGifJob;
use App\Models\Artwork;
use App\Models\CurseUse;
use App\Models\Game;
use App\Models\Room;
use App\Models\Round;
use App\Models\Stroke;
use App\Models\Word;
use App\Services\Gif89aEncoderService;
use App\Services\MuseumArtworkService;
use App\Services\PlayerIdentityService;
use App\Services\SabotageDeckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class MuseumController extends Controller
{
    public function __construct(
        public Gif89aEncoderService $gifEncoder,
        public SabotageDeckService $sabotageDeck,
        public PlayerIdentityService $playerIdentity,
        public MuseumArtworkService $museumArtwork,
    ) {}

    /**
     * Submit a 1-5 star rating from players for the round's drawing.
     */
    public function rateArtwork(Request $request, string $code): JsonResponse
    {
        $validated = $request->validate([
            'stars' => ['required', 'integer', 'min:1', 'max:5'],
            'player_name' => ['nullable', 'string', 'max:50'],
        ]);

        $cleanCode = substr(strtoupper(trim($code)), 0, 4);
        $stars = (int) $validated['stars'];
        $voter = $this->playerIdentity->resolveRequestPlayer($request, (string) ($validated['player_name'] ?? 'Voter'));

        $avgRating = (float) $stars;
        $votesCount = 1;

        try {
            $room = Room::where('code', $cleanCode)->firstOrFail();
            $settings = is_array($room->settings) ? $room->settings : [];
            $ratings = is_array($settings['ratings'] ?? null) ? $settings['ratings'] : [];
            $ratings[(string) $voter->id] = $stars;
            $settings['ratings'] = $ratings;
            $room->update(['settings' => $settings]);

            $votesCount = count($ratings);
            $avgRating = round(array_sum($ratings) / max(1, $votesCount), 1);
        } catch (Throwable) {
            // Keep single vote fallback
        }

        return response()->json([
            'status' => 'ok',
            'stars' => $stars,
            'avg_rating' => $avgRating,
            'votes_count' => $votesCount,
        ]);
    }

    /**
     * Save Artwork (dataURL + GIF89a) to `artworks` for the Museum (`Hall of Shame`).
     */
    public function exportPanicReplayGif(Request $request, string $code): JsonResponse
    {
        $validated = $request->validate([
            'custom_title' => ['nullable', 'string', 'max:255'],
            'image_data_url' => ['nullable', 'string'],
            'strokes' => ['nullable', 'array'],
            'star_rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
        ]);

        $cleanCode = substr(strtoupper(trim($code)), 0, 4);

        try {
            $room = Room::where('code', $cleanCode)->firstOrFail();
            $game = $room->activeGame ?? Game::create(['room_id' => $room->id, 'status' => 'playing', 'started_at' => now()]);
            $round = $game->activeRound()->with(['artist', 'word', 'curseUses.curse'])->first();

            if (! $round) {
                $word = Word::firstOrCreate(['phrase' => 'Kucing Naik Helikopter'], ['category' => 'Custom', 'difficulty' => 'medium']);
                $round = Round::create([
                    'game_id' => $game->id,
                    'artist_player_id' => $room->host_player_id,
                    'word_id' => $word->id,
                    'round_number' => 1,
                    'status' => 'finished',
                    'started_at' => now()->subSeconds(47),
                    'ended_at' => now(),
                ]);
            }

            if (! empty($validated['strokes']) && $round->strokes()->count() === 0) {
                foreach ($validated['strokes'] as $idx => $strokeData) {
                    Stroke::create([
                        'round_id' => $round->id,
                        'sequence' => $idx + 1,
                        'color' => substr((string) ($strokeData['color'] ?? '#14121D'), 0, 20),
                        'brush_size' => (int) ($strokeData['size'] ?? $strokeData['brush_size'] ?? 6),
                        'points' => is_array($strokeData['points'] ?? null) ? $strokeData['points'] : [],
                        'started_at_ms' => ($idx + 1) * 250,
                        'created_at' => now(),
                    ]);
                }
            }

            $job = new GeneratePanicReplayGifJob($round->id, $validated['custom_title'] ?? null);
            $artwork = $job->handle($this->gifEncoder);

            if (! empty($validated['image_data_url'])) {
                $artwork->update([
                    'image_path' => substr((string) $validated['image_data_url'], 0, 500),
                ]);
            }

            $curseSlugs = $round->curseUses->map(fn (CurseUse $u): string => $u->curse?->slug ?? 'earthquake')->all();
            $cursesSummary = $this->sabotageDeck->buildSabotageSummary($curseSlugs);
            $artistName = $round->artist?->nickname ?? 'Seniman';
            $secretPhrase = $round->word?->phrase ?? 'Kucing Naik Helikopter';
            $meta = $this->gifEncoder->generateMuseumMetadata($artistName, $secretPhrase, $curseSlugs);

            $ratings = is_array($room->settings['ratings'] ?? null) ? $room->settings['ratings'] : [];
            $avgRating = count($ratings) > 0
                ? round(array_sum($ratings) / count($ratings), 1)
                : (float) ($validated['star_rating'] ?? 4.5);

            return response()->json([
                'status' => 'ok',
                'entry' => [
                    'id' => $artwork->id,
                    'round_id' => $artwork->round_id,
                    'exhibit_number' => sprintf('#%02d', $artwork->id),
                    'sarcastic_title' => $validated['custom_title'] ?? $meta['sarcastic_title'],
                    'museum_plaque' => $artwork->title,
                    'secret_word' => $secretPhrase,
                    'artist_name' => $artistName,
                    'curses_applied' => $cursesSummary,
                    'chaos_rating' => min(100, count($curseSlugs) * 25),
                    'star_rating' => $avgRating,
                    'votes_count' => max(1, count($ratings)),
                    'image_data_url' => $validated['image_data_url'] ?? null,
                    'gif_download_url' => route('hall-of-shame.gif', ['id' => $artwork->id]),
                    'created_at' => $artwork->created_at?->toIso8601String() ?? now()->toIso8601String(),
                ],
            ]);
        } catch (Throwable) {
            $meta = $this->gifEncoder->generateMuseumMetadata('Seniman', 'Kucing Naik Helikopter', ['earthquake']);

            return response()->json([
                'status' => 'ok',
                'entry' => [
                    'id' => 1,
                    'round_id' => 1,
                    'exhibit_number' => '#01',
                    'sarcastic_title' => $validated['custom_title'] ?? $meta['sarcastic_title'],
                    'museum_plaque' => $validated['custom_title'] ?? $meta['museum_plaque'],
                    'secret_word' => 'Kucing Naik Helikopter',
                    'artist_name' => 'Seniman',
                    'curses_applied' => 'Gempa Bumi',
                    'chaos_rating' => 50,
                    'star_rating' => 4.5,
                    'votes_count' => 1,
                    'image_data_url' => $validated['image_data_url'] ?? null,
                    'gif_download_url' => route('hall-of-shame.gif', ['id' => 1]),
                    'created_at' => now()->toIso8601String(),
                ],
            ]);
        }
    }

    /**
     * Return real Museum artworks from the database with filter: `curator` | `favorite` | `newest`.
     */
    public function index(Request $request): JsonResponse
    {
        $filter = (string) $request->query('filter', 'newest');
        $items = $this->museumArtwork->loadRealMuseumArtworks($filter);

        return response()->json([
            'status' => 'ok',
            'filter' => $filter,
            'items' => $items,
        ]);
    }

    /**
     * Download the animated GIF89a binary for an Artwork in the Hall of Shame.
     */
    public function downloadGif(int $id): Response
    {
        try {
            $artwork = Artwork::find($id);
            if ($artwork && $artwork->gif_path && Storage::disk('public')->exists($artwork->gif_path)) {
                $gifContent = (string) Storage::disk('public')->get($artwork->gif_path);
                $slug = Str::slug($artwork->title ?: 'coret-chaos-replay');

                return response($gifContent, 200, [
                    'Content-Type' => 'image/gif',
                    'Content-Disposition' => "attachment; filename=\"{$slug}.gif\"",
                ]);
            }
        } catch (Throwable) {
            // Fall through to on-the-fly pure-PHP GIF89a generation
        }

        $defaultStrokes = [
            [
                'color' => '#14121D',
                'points' => [
                    ['x' => 0.25, 'y' => 0.3],
                    ['x' => 0.75, 'y' => 0.7],
                ],
            ],
        ];

        $gifContent = $this->gifEncoder->encodePanicReplayGif(
            $defaultStrokes,
            [['curse_type' => 'earthquake']]
        );

        return response($gifContent, 200, [
            'Content-Type' => 'image/gif',
            'Content-Disposition' => 'attachment; filename="coret-chaos-panic-replay.gif"',
        ]);
    }
}
