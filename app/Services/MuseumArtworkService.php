<?php

namespace App\Services;

use App\Models\Artwork;
use App\Models\CurseUse;
use Throwable;

class MuseumArtworkService
{
    public function __construct(
        public SabotageDeckService $sabotageDeck,
    ) {}

    /**
     * Load real Museum artworks from the `artworks` table with filter (`curator` | `favorite` | `newest`).
     *
     * @return array<int, array<string, mixed>>
     */
    public function loadRealMuseumArtworks(string $filter = 'newest'): array
    {
        try {
            $artworks = Artwork::with(['round.artist', 'round.word', 'round.curseUses.curse'])
                ->latest()
                ->limit(24)
                ->get();

            $items = $artworks->map(function (Artwork $art): array {
                $artistName = $art->round?->artist?->nickname ?? 'Seniman';
                $secretWord = $art->round?->word?->phrase ?? 'Karya Misterius';
                $curseSlugs = $art->round?->curseUses
                    ? $art->round->curseUses->map(fn (CurseUse $u): string => $u->curse?->slug ?? 'earthquake')->all()
                    : [];
                $chaosPercent = min(100, count($curseSlugs) * 25);
                $cursesSummary = $this->sabotageDeck->buildSabotageSummary($curseSlugs);

                return [
                    'id' => $art->id,
                    'exhibit_number' => sprintf('#%02d', $art->id),
                    'sarcastic_title' => "Karya: {$secretWord}",
                    'museum_plaque' => $art->title,
                    'secret_word' => $secretWord,
                    'artist_name' => $artistName,
                    'curses_applied' => $cursesSummary,
                    'chaos_rating' => $chaosPercent,
                    'star_rating' => 4.5,
                    'votes_count' => 1,
                    'image_data_url' => str_starts_with((string) $art->image_path, 'data:image') ? $art->image_path : null,
                    'is_curator_pick' => $chaosPercent >= 50,
                    'gif_download_url' => route('hall-of-shame.gif', ['id' => $art->id]),
                    'created_at_ts' => $art->created_at?->timestamp ?? time(),
                ];
            })->all();

            if ($filter === 'curator') {
                $items = array_values(array_filter(
                    $items,
                    fn (array $item): bool => $item['is_curator_pick'] || $item['chaos_rating'] >= 50
                ));
            } elseif ($filter === 'favorite') {
                usort(
                    $items,
                    fn (array $a, array $b): int => ($b['star_rating'] <=> $a['star_rating']) ?: ($b['chaos_rating'] <=> $a['chaos_rating'])
                );
            }

            return $items;
        } catch (Throwable) {
            return [];
        }
    }
}
