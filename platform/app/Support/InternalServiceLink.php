<?php

namespace App\Support;

use App\Models\ServiceLink;
use Illuminate\Support\Str;

class InternalServiceLink
{
    /**
     * @return array{label: string, url: string, tracking_key: string, external: false}
     */
    public static function resolve(ServiceLink $link): array
    {
        $key = self::key($link);
        $target = self::targets()[$key] ?? ['label' => $link->label, 'url' => PublicUrl::route('tools.index')];

        return [
            'label' => $target['label'],
            'url' => $target['url'],
            'tracking_key' => $key,
            'external' => false,
        ];
    }

    private static function key(ServiceLink $link): string
    {
        $raw = $link->tracking_key ?: $link->type ?: $link->label;
        $key = Str::slug((string) $raw);

        return match ($key) {
            'rail', 'rail-pass', 'jr-pass', 'jr-pass-checker' => 'rail-tickets',
            'exchange-rate', 'exchange-rate-converter', 'currency', 'currency-converter' => 'exchange-rate',
            'tax-free', 'souvenir-shopping' => 'shop',
            'advertise', 'advertising' => 'advertising',
            default => $key,
        };
    }

    /**
     * @return array<string, array{label: string, url: string}>
     */
    private static function targets(): array
    {
        return [
            'activities' => ['label' => 'Activities', 'url' => PublicUrl::route('categories.show', 'things-to-do')],
            'hotels' => ['label' => 'Hotels', 'url' => PublicUrl::route('categories.show', 'lodging')],
            'flights' => ['label' => 'Flights', 'url' => PublicUrl::route('tools.show', 'airport-transfer')],
            'rail-tickets' => ['label' => 'Rail Tickets', 'url' => PublicUrl::route('tools.show', 'jr-pass-calculator')],
            'shop' => ['label' => 'Shop', 'url' => PublicUrl::route('tools.show', 'tax-free-calculator')],
            'exchange-rate' => ['label' => 'Exchange Rate', 'url' => PublicUrl::route('tools.show', 'budget-calculator')],
            'advertising' => ['label' => 'Advertise', 'url' => PublicUrl::route('pages.contact')],
        ];
    }
}
