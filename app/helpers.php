<?php

use Illuminate\Support\Str;

if (! function_exists('str_slug')) {
    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param  string  $title
     * @param  string  $separator
     * @param  string|null  $language
     * @return string
     */
    function str_slug($title, $separator = '-', $language = 'en')
    {
        return Str::slug((string) $title, $separator, $language);
    }
}

if (! function_exists('factory')) {
    /**
     * Backward-compatible factory helper for Eloquent models using HasFactory.
     *
     * @param  string  $class
     * @param  int|null  $amount
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    function factory(string $class, ?int $amount = null)
    {
        $factory = $class::factory();

        return $amount !== null ? $factory->count($amount) : $factory;
    }
}
