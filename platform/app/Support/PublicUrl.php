<?php

namespace App\Support;

class PublicUrl
{
    /**
     * @param  array<string, mixed>|mixed  $parameters
     */
    public static function route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return self::canonicalize(route($name, $parameters, $absolute));
    }

    public static function canonicalize(string $url): string
    {
        $fragment = '';
        $fragmentPosition = strpos($url, '#');

        if ($fragmentPosition !== false) {
            $fragment = substr($url, $fragmentPosition);
            $url = substr($url, 0, $fragmentPosition);
        }

        $query = '';
        $queryPosition = strpos($url, '?');

        if ($queryPosition !== false) {
            $query = substr($url, $queryPosition);
            $url = substr($url, 0, $queryPosition);
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $basename = basename($path);

        if ($path !== '' && $path !== '/' && ! str_contains($basename, '.') && ! str_ends_with($url, '/')) {
            $url .= '/';
        }

        return $url.$query.$fragment;
    }
}
