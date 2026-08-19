<?php

namespace App\Services\Seo;

use App\Models\SiteSetting;
use Illuminate\Support\Str;

class PublicTitleFormatter
{
    private const MAX_TITLE_LENGTH = 70;

    public function format(string $title, SiteSetting $settings): string
    {
        $siteName = trim((string) $settings->site_name);
        $suffix = trim((string) $settings->seo_title_suffix);
        $brandParts = array_filter([$siteName, 'Japan Trip Tools', $suffix, 'Japan Travel Guide']);

        $parts = collect(explode(' | ', $title))
            ->map(fn (string $part): string => trim($part))
            ->filter()
            ->reject(fn (string $part): bool => $this->matchesBrandPart($part, $brandParts))
            ->values();

        $baseTitle = $parts->isNotEmpty() ? $parts->implode(' | ') : trim($title);
        $baseTitle = $this->trimToLength($baseTitle, self::MAX_TITLE_LENGTH);

        if ($suffix === '' || $this->matchesBrandPart($baseTitle, [$suffix])) {
            return $baseTitle;
        }

        $withSuffix = $baseTitle.' | '.$suffix;

        if (mb_strlen($withSuffix) <= self::MAX_TITLE_LENGTH) {
            return $withSuffix;
        }

        return $baseTitle;
    }

    /**
     * @param  array<int, string>  $brandParts
     */
    private function matchesBrandPart(string $value, array $brandParts): bool
    {
        $normalized = Str::of($value)->lower()->squish()->toString();

        foreach ($brandParts as $brandPart) {
            if ($normalized === Str::of($brandPart)->lower()->squish()->toString()) {
                return true;
            }
        }

        return false;
    }

    private function trimToLength(string $value, int $maxLength): string
    {
        $value = trim($value);

        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        $candidate = rtrim(mb_substr($value, 0, $maxLength));
        $wordBoundary = mb_strrpos($candidate, ' ');

        if ($wordBoundary !== false && $wordBoundary >= 42) {
            return rtrim(mb_substr($candidate, 0, $wordBoundary), ' ,:-');
        }

        return rtrim($candidate, ' ,:-');
    }
}
