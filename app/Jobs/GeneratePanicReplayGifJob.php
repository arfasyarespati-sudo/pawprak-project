<?php

namespace App\Jobs;

use App\Models\Artwork;
use App\Models\CurseUse;
use App\Models\Round;
use App\Models\Stroke;
use App\Services\Gif89aEncoderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GeneratePanicReplayGifJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $roundId,
        public ?string $customTitle = null,
    ) {}

    /**
     * Execute the job and store the 1-to-1 Artwork record for the Round.
     */
    public function handle(Gif89aEncoderService $gifEncoder): Artwork
    {
        $round = Round::with(['game.room', 'artist', 'word', 'strokes', 'curseUses.curse'])->findOrFail($this->roundId);

        $strokePayloads = $round->strokes->map(fn (Stroke $stroke): array => [
            'sequence' => $stroke->sequence,
            'color' => $stroke->color,
            'size' => $stroke->brush_size,
            'points' => $stroke->points,
            'started_at_ms' => $stroke->started_at_ms,
        ])->all();

        $sabotagePayloads = $round->curseUses->map(fn (CurseUse $use): array => [
            'triggered_by_name' => $use->player?->nickname ?? 'Saboteur',
            'curse_type' => $use->curse?->slug ?? 'earthquake',
            'energy_cost' => $use->curse?->energy_cost ?? 30,
            'duration_seconds' => (int) round(($use->curse?->duration_ms ?? 4000) / 1000),
            'payload' => $use->payload,
        ])->all();

        $curseSlugs = array_values(array_map(
            fn (array $event): string => (string) $event['curse_type'],
            $sabotagePayloads
        ));

        $artistNickname = $round->artist?->nickname ?? 'BocilMaut';
        $secretPhrase = $round->word?->phrase ?? 'Kucing Naik Helikopter';

        $metadata = $gifEncoder->generateMuseumMetadata(
            $artistNickname,
            $secretPhrase,
            $curseSlugs
        );

        $gifBinary = $gifEncoder->encodePanicReplayGif($strokePayloads, $sabotagePayloads);
        $roomCode = $round->game?->room?->code ?? 'CHAO';
        $gifRelativePath = "replays/panic-{$roomCode}-round-{$round->round_number}-{$round->id}.gif";
        $imageRelativePath = "artworks/frame-{$roomCode}-round-{$round->round_number}-{$round->id}.webp";

        Storage::disk('public')->put($gifRelativePath, $gifBinary);

        $title = $this->customTitle ?: $metadata['museum_plaque'];

        return Artwork::updateOrCreate(
            ['round_id' => $round->id],
            [
                'title' => $title,
                'image_path' => $imageRelativePath,
                'gif_path' => $gifRelativePath,
                'status' => 'completed',
            ]
        );
    }
}
