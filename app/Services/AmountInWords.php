<?php

namespace App\Services;

/**
 * Spells a taka amount out for an invoice: "Four Thousand Two Hundred Taka Only".
 *
 * Counts in the South Asian scale — thousand, lakh, crore — because that is how
 * a figure is read aloud here and how the shop's customers will check it against
 * the digits beside it.
 */
class AmountInWords
{
    private const ONES = [
        1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
        15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
        19 => 'Nineteen',
    ];

    private const TENS = [
        2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety',
    ];

    /** Largest first: each takes its slice of the number and hands on the rest. */
    private const SCALES = [
        10000000 => 'Crore',
        100000 => 'Lakh',
        1000 => 'Thousand',
        100 => 'Hundred',
    ];

    public function format(float|int|string $amount): string
    {
        // Counted in paisa throughout, so 454.40 cannot arrive as 454.39 by way
        // of a float that never held four-fifty-four-forty in the first place.
        $paisa = (int) round((float) $amount * 100);
        $negative = $paisa < 0;
        $paisa = abs($paisa);

        $words = $this->spell(intdiv($paisa, 100)) . ' Taka';

        if ($remainder = $paisa % 100) {
            $words .= ' and ' . $this->spell($remainder) . ' Paisa';
        }

        return ($negative ? 'Minus ' : '') . $words . ' Only';
    }

    private function spell(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $parts = [];

        foreach (self::SCALES as $size => $name) {
            if ($number >= $size) {
                $parts[] = $this->spell(intdiv($number, $size)) . ' ' . $name;
                $number %= $size;
            }
        }

        if ($number >= 20) {
            $tens = self::TENS[intdiv($number, 10)];
            $parts[] = $number % 10 ? $tens . ' ' . self::ONES[$number % 10] : $tens;
        } elseif ($number > 0) {
            $parts[] = self::ONES[$number];
        }

        return implode(' ', $parts);
    }
}
