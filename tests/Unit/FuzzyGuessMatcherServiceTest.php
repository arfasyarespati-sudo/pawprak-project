<?php

namespace Tests\Unit;

use App\Services\FuzzyGuessMatcherService;
use PHPUnit\Framework\TestCase;

class FuzzyGuessMatcherServiceTest extends TestCase
{
    public function test_it_detects_levenshtein_typo_for_jerapah_without_leaking_answer(): void
    {
        $matcher = new FuzzyGuessMatcherService;

        $resultJrapah = $matcher->evaluateGuess('Jerapah', 'Jrapah');
        $this->assertSame('close', $resultJrapah['status']);
        $this->assertSame('close', $resultJrapah['match_status']);
        $this->assertSame(1, $resultJrapah['levenshtein_distance']);
        $this->assertTrue($resultJrapah['is_private_hint']);
        $this->assertStringContainsString('🔥 DIKIT LAGI BANG! Dikit lagi / Typo!', (string) $resultJrapah['private_hint']);
        $this->assertNotSame('Jrapah', $resultJrapah['public_display_text']);

        $resultJerapa = $matcher->evaluateGuess('Jerapah', 'Jerapa');
        $this->assertSame('close', $resultJerapa['status']);
        $this->assertSame('close', $resultJerapa['match_status']);
        $this->assertSame(1, $resultJerapa['levenshtein_distance']);
        $this->assertTrue($resultJerapa['is_private_hint']);
        $this->assertStringContainsString('🔥 DIKIT LAGI BANG! Dikit lagi / Typo!', (string) $resultJerapa['private_hint']);
    }

    public function test_it_detects_partial_word_match_in_multi_word_secret(): void
    {
        $matcher = new FuzzyGuessMatcherService;

        $result = $matcher->evaluateGuess('Kucing Naik Helikopter', 'kucing garong terbang');
        $this->assertSame('close', $result['status']);
        $this->assertSame('close', $result['match_status']);
        $this->assertTrue($result['is_private_hint']);
        $this->assertStringContainsString('🔥 DIKIT LAGI BANG! Dikit lagi / Typo!', (string) $result['private_hint']);
    }

    public function test_it_awards_speed_points_and_artist_sabotage_survival_bonus_on_exact_match(): void
    {
        $matcher = new FuzzyGuessMatcherService;

        $fastResult = $matcher->evaluateGuess(
            secretWord: 'Kucing Naik Helikopter',
            guessText: 'kucing naik helikopter',
            elapsedSeconds: 4.2,
            sabotageCount: 3,
            previousCorrectGuesses: 0
        );

        $this->assertSame('exact', $fastResult['status']);
        $this->assertSame('exact', $fastResult['match_status']);
        $this->assertSame(0, $fastResult['levenshtein_distance']);
        $this->assertSame(350, $fastResult['guesser_points']);
        $this->assertSame(195, $fastResult['artist_bonus_points']);
    }
}
