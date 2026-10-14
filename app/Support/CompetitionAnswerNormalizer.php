<?php

namespace App\Support;

class CompetitionAnswerNormalizer
{
    public function normalize(string $answer): string
    {
        $normalized = mb_strtolower(trim($answer));
        $normalized = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $normalized) ?? $normalized;
        $normalized = strtr($normalized, [
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ٱ' => 'ا',
            'ى' => 'ي',
            'ؤ' => 'و',
            'ئ' => 'ي',
        ]);

        return preg_replace('/\s+/u', ' ', trim($normalized)) ?? trim($normalized);
    }
}
