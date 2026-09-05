<?php

namespace Tests\Unit;

use App\Services\AmountInWords;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AmountInWordsTest extends TestCase
{
    private AmountInWords $words;

    protected function setUp(): void
    {
        parent::setUp();
        $this->words = new AmountInWords();
    }

    #[DataProvider('amounts')]
    public function test_it_spells_an_amount(float $amount, string $expected): void
    {
        $this->assertSame($expected, $this->words->format($amount));
    }

    public static function amounts(): array
    {
        return [
            'zero' => [0, 'Zero Taka Only'],
            'single digit' => [7, 'Seven Taka Only'],
            'teen' => [15, 'Fifteen Taka Only'],
            'round ten' => [40, 'Forty Taka Only'],
            'two digits' => [54, 'Fifty Four Taka Only'],
            'hundred' => [100, 'One Hundred Taka Only'],
            'hundreds with remainder' => [455, 'Four Hundred Fifty Five Taka Only'],
            'thousand' => [4200, 'Four Thousand Two Hundred Taka Only'],
            'thousands with remainder' => [12345, 'Twelve Thousand Three Hundred Forty Five Taka Only'],
            // The South Asian scale, not the international one: 1,50,000 reads
            // as one lakh fifty thousand, never "one hundred fifty thousand".
            'lakh' => [150000, 'One Lakh Fifty Thousand Taka Only'],
            'crore' => [25000000, 'Two Crore Fifty Lakh Taka Only'],
            'past a hundred crore' => [1010000000, 'One Hundred One Crore Taka Only'],

            'paisa' => [454.40, 'Four Hundred Fifty Four Taka and Forty Paisa Only'],
            'one paisa' => [1.01, 'One Taka and One Paisa Only'],
            'paisa rounded from a float' => [0.1 + 0.2, 'Zero Taka and Thirty Paisa Only'],
            'trailing zeros are not paisa' => [4200.00, 'Four Thousand Two Hundred Taka Only'],
            'half rounds up' => [99.995, 'One Hundred Taka Only'],

            'a refund reads as negative' => [-50.5, 'Minus Fifty Taka and Fifty Paisa Only'],
        ];
    }

    public function test_it_accepts_the_decimal_string_a_database_hands_back(): void
    {
        $this->assertSame('Four Thousand Two Hundred Taka Only', $this->words->format('4200.00'));
    }
}
