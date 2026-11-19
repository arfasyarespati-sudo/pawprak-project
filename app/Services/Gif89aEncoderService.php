<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class Gif89aEncoderService
{
    /**
     * Generate a sarcastic museum auction title and plaque for the Hall of Shame.
     *
     * @param  array<int, string>  $cursesApplied
     * @return array{sarcastic_title: string, museum_plaque: string}
     */
    public function generateMuseumMetadata(string $artistName, string $secretWord, array $cursesApplied = []): array
    {
        $cleanArtist = ltrim(trim($artistName), '@') ?: 'BocilMaut';
        $year = '2026';

        $templates = [
            "Tragedi {$secretWord} di Tengah Gempa",
            "Abstraksi Panik: {$secretWord} Edisi Tersesat",
            "Misteri {$secretWord} yang Hilang Arah",
            "Simfoni Garis Miring: {$secretWord}",
            "Monumen Kegagalan {$secretWord}",
        ];

        $hash = abs(crc32($cleanArtist.$secretWord.implode(',', $cursesApplied)));
        $sarcasticTitle = $templates[$hash % count($templates)];
        $museumPlaque = "Karya: Kehancuran Mental @{$cleanArtist}, Est. {$year}";

        return [
            'sarcastic_title' => $sarcasticTitle,
            'museum_plaque' => $museumPlaque,
        ];
    }

    /**
     * Encode and save a valid animated .gif file to the public storage disk.
     *
     * @param  array<int, array<string, mixed>>  $strokes
     * @param  array<int, array<string, mixed>>  $sabotageEvents
     */
    public function saveAnimatedGif(
        string $relativePath,
        array $strokes,
        array $sabotageEvents = [],
        int $width = 128,
        int $height = 96,
    ): string {
        $binary = $this->encodePanicReplayGif($strokes, $sabotageEvents, $width, $height);
        Storage::disk('public')->put($relativePath, $binary);

        return $relativePath;
    }

    /**
     * Render a multi-frame animated GIF89a binary from recorded canvas strokes and sabotage events.
     * Pure-PHP implementation requiring zero external C extensions (works without ext-gd or ext-imagick).
     *
     * @param  array<int, array<string, mixed>>  $strokes
     * @param  array<int, array<string, mixed>>  $sabotageEvents
     */
    public function encodePanicReplayGif(array $strokes, array $sabotageEvents = [], int $width = 128, int $height = 96): string
    {
        /**
         * 16-color indexed palette (4-bit color table):
         * 0: Cream Canvas (#FAF4E8)
         * 1: Dark Frame / Ink (#14121D)
         * 2: Museum Gold Frame (#D4AF37)
         * 3: Sabotage Crimson (#FF4D6D)
         * 4: Neon Lime (#B8FF3B)
         * 5: Chaos Purple (#A855F7)
         * 6: Sky Cyan (#38BDF8)
         * 7: Orange Flame (#FB923C)
         * 8: Pitch Blackout (#07060B)
         * 9: Pure White (#FFFFFF)
         * 10: Slate Gray (#64748B)
         * 11: Emerald (#10B981)
         * 12: Yellow Sticker (#FACC15)
         * 13: Pink Glitch (#F472B6)
         * 14: Deep Brown (#78350F)
         * 15: Soft Cream Border (#E7DEC8)
         */
        $palette = [
            [250, 244, 232],
            [20, 18, 29],
            [212, 175, 55],
            [255, 77, 109],
            [184, 255, 59],
            [168, 85, 247],
            [56, 189, 248],
            [251, 146, 60],
            [7, 6, 11],
            [255, 255, 255],
            [100, 116, 139],
            [16, 185, 129],
            [250, 204, 21],
            [244, 114, 182],
            [120, 53, 15],
            [231, 222, 200],
        ];

        $totalFrames = 6;
        $strokeCount = count($strokes);
        $framesBinary = '';

        for ($frameIdx = 0; $frameIdx < $totalFrames; $frameIdx++) {
            $progressRatio = ($frameIdx + 1) / $totalFrames;
            $visibleStrokes = (int) ceil($strokeCount * $progressRatio);
            $isPanicFrame = ($frameIdx >= 2 && $frameIdx <= 4 && $sabotageEvents !== []);
            $shakeOffsetX = ($isPanicFrame && $frameIdx % 2 === 1) ? 3 : ($isPanicFrame ? -3 : 0);
            $shakeOffsetY = ($isPanicFrame && $frameIdx % 2 === 0) ? 2 : 0;

            $pixels = array_fill(0, $width * $height, 0);

            // Draw Museum Auction Outer Frame (gold & dark gallery mat)
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    if ($x < 4 || $x >= $width - 4 || $y < 4 || $y >= $height - 4) {
                        $pixels[$y * $width + $x] = 2; // Gold museum frame
                    } elseif ($x < 7 || $x >= $width - 7 || $y < 7 || $y >= $height - 14) {
                        $pixels[$y * $width + $x] = 1; // Dark gallery mat + bottom plaque bar
                    }
                }
            }

            // Draw progressive strokes inside canvas area
            $slice = array_slice($strokes, 0, max(1, $visibleStrokes));
            foreach ($slice as $stroke) {
                $colorIndex = $this->mapHexToPaletteIndex((string) ($stroke['color'] ?? '#14121D'));
                $points = is_array($stroke['points'] ?? null) ? $stroke['points'] : [];
                $prevPoint = null;

                foreach ($points as $pt) {
                    if (! is_array($pt) || ! isset($pt['x'], $pt['y'])) {
                        continue;
                    }

                    $normX = (float) $pt['x'];
                    $normY = (float) $pt['y'];

                    // Support both normalized (0.0..1.0) and pixel coordinate points
                    if ($normX > 1.0 || $normY > 1.0) {
                        $normX = min(1.0, max(0.0, $normX / 800.0));
                        $normY = min(1.0, max(0.0, $normY / 500.0));
                    }

                    $px = (int) round($normX * ($width - 16) + 8) + $shakeOffsetX;
                    $py = (int) round($normY * ($height - 24) + 8) + $shakeOffsetY;

                    if ($prevPoint !== null) {
                        $this->drawLine($pixels, $width, $height, $prevPoint[0], $prevPoint[1], $px, $py, $colorIndex);
                    } else {
                        $this->plotPoint($pixels, $width, $height, $px, $py, $colorIndex);
                    }

                    $prevPoint = [$px, $py];
                }
            }

            // If panic frame, overlay chaotic glitch accents / sticker bomb marker
            if ($isPanicFrame) {
                for ($gx = 14; $gx < $width - 14; $gx += 6) {
                    $pixels[10 * $width + $gx] = 3; // Crimson alert bar
                    $pixels[11 * $width + $gx] = 4; // Lime spark
                }
            }

            // Final frame: stamp crimson SABOTAGED badge at top-right & gold plaque dots at bottom
            if ($frameIdx === $totalFrames - 1) {
                for ($by = 9; $by < 17; $by++) {
                    for ($bx = $width - 38; $bx < $width - 10; $bx++) {
                        $pixels[$by * $width + $bx] = 3;
                    }
                }
            }

            // Plaque indicator bar at bottom of museum frame
            for ($px = 28; $px < $width - 28; $px += 2) {
                $pixels[($height - 8) * $width + $px] = 2;
            }

            $delayCs = ($frameIdx === $totalFrames - 1) ? 80 : 18;
            $framesBinary .= $this->buildGifFrameBlock($pixels, $width, $height, $delayCs);
        }

        return $this->buildGifHeader($width, $height, $palette)
            .$this->buildNetscapeLoopExtension()
            .$framesBinary
            ."\x3B"; // GIF Trailer (0x3B)
    }

    /**
     * Map a hex color string to one of our 16 palette indices.
     */
    private function mapHexToPaletteIndex(string $hex): int
    {
        $clean = strtoupper(ltrim($hex, '#'));

        return match ($clean) {
            'FF4D6D', 'EF4444', 'F43F5E' => 3,
            'B8FF3B', '84CC16', '22C55E' => 4,
            'A855F7', 'C8B6FF', '8B5CF6' => 5,
            '38BDF8', '0EA5E9', '3B82F6' => 6,
            'FB923C', 'F97316' => 7,
            'FFFFFF', 'FAF4E8' => 0,
            'FACC15', 'EAB308' => 12,
            default => 1,
        };
    }

    /**
     * Draw a thick line between two coordinates on the pixel buffer using Bresenham's line algorithm.
     *
     * @param  array<int, int>  $pixels
     */
    private function drawLine(array &$pixels, int $width, int $height, int $x0, int $y0, int $x1, int $y1, int $color): void
    {
        $dx = abs($x1 - $x0);
        $sx = $x0 < $x1 ? 1 : -1;
        $dy = -abs($y1 - $y0);
        $sy = $y0 < $y1 ? 1 : -1;
        $err = $dx + $dy;

        $steps = 0;
        while ($steps < 300) {
            $this->plotPoint($pixels, $width, $height, $x0, $y0, $color);
            if ($x0 === $x1 && $y0 === $y1) {
                break;
            }
            $e2 = 2 * $err;
            if ($e2 >= $dy) {
                $err += $dy;
                $x0 += $sx;
            }
            if ($e2 <= $dx) {
                $err += $dx;
                $y0 += $sy;
            }
            $steps++;
        }
    }

    /**
     * Plot a 2x2 brush point inside the safe canvas viewport.
     *
     * @param  array<int, int>  $pixels
     */
    private function plotPoint(array &$pixels, int $width, int $height, int $x, int $y, int $color): void
    {
        for ($oy = 0; $oy <= 1; $oy++) {
            for ($ox = 0; $ox <= 1; $ox++) {
                $px = $x + $ox;
                $py = $y + $oy;
                if ($px >= 8 && $px < $width - 8 && $py >= 8 && $py < $height - 15) {
                    $pixels[$py * $width + $px] = $color;
                }
            }
        }
    }

    /**
     * Build the GIF89a header + Logical Screen Descriptor + 16-color Global Color Table.
     *
     * @param  array<int, array{0: int, 1: int, 2: int}>  $palette
     */
    private function buildGifHeader(int $width, int $height, array $palette): string
    {
        // Packed byte: Global Color Table Flag (1) | Color Resolution (3) | Sort (0) | Size of GCT (3 -> 16 colors) = 0xB3
        $header = 'GIF89a'.pack('vvCCC', $width, $height, 0xB3, 0, 0);

        $gct = '';
        for ($i = 0; $i < 16; $i++) {
            $rgb = $palette[$i] ?? [0, 0, 0];
            $gct .= pack('CCC', $rgb[0], $rgb[1], $rgb[2]);
        }

        return $header.$gct;
    }

    /**
     * Build the Netscape 2.0 infinite loop application extension block.
     */
    private function buildNetscapeLoopExtension(): string
    {
        return "\x21\xFF\x0BNETSCAPE2.0\x03\x01\x00\x00\x00";
    }

    /**
     * Build a single frame's Graphic Control Extension + Image Descriptor + Uncompressed LZW Data stream.
     *
     * @param  array<int, int>  $pixels
     */
    private function buildGifFrameBlock(array $pixels, int $width, int $height, int $delayCs): string
    {
        // Graphic Control Extension: disposal method 1 (do not dispose), no transparency
        $gce = "\x21\xF9\x04\x04".pack('v', $delayCs)."\x00\x00";

        // Image Descriptor (0x2C)
        $descriptor = "\x2C".pack('vvvvC', 0, 0, $width, $height, 0x00);

        // Use 7-bit LZW minimum code size (Clear Code = 128, End of Information = 129)
        // Emitting a Clear Code every 120 pixels keeps LZW codes strictly 8-bit (1 byte per pixel).
        $minCodeSize = 7;
        $clearCode = 128;
        $eoiCode = 129;

        $codeBytes = [$clearCode];
        $chunkCounter = 0;

        foreach ($pixels as $colorIndex) {
            $codeBytes[] = $colorIndex & 0x0F;
            $chunkCounter++;
            if ($chunkCounter >= 120) {
                $codeBytes[] = $clearCode;
                $chunkCounter = 0;
            }
        }

        $codeBytes[] = $eoiCode;
        $rawStream = pack('C*', ...$codeBytes);

        // Split into GIF sub-blocks of max 255 bytes
        $subBlocks = '';
        $streamLength = strlen($rawStream);
        for ($offset = 0; $offset < $streamLength; $offset += 255) {
            $chunk = substr($rawStream, $offset, 255);
            $subBlocks .= chr(strlen($chunk)).$chunk;
        }

        return $gce.$descriptor.chr($minCodeSize).$subBlocks."\x00";
    }
}
