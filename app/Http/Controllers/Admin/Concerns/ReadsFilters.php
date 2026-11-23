<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;

/** Membaca query string filter dengan aman; nilai tidak valid diabaikan, bukan menyebabkan error. */
trait ReadsFilters
{
    protected function text(Request $request, string $key, int $max = 80): string
    {
        $value = $request->query($key);

        return is_string($value) ? mb_substr(trim($value), 0, $max) : '';
    }

    protected function choice(Request $request, string $key, array $allowed): string
    {
        $value = $request->query($key);

        return is_string($value) && in_array($value, $allowed, true) ? $value : '';
    }

    protected function id(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) && ctype_digit($value) ? $value : '';
    }

    protected function like(string $term): string
    {
        return '%'.addcslashes($term, '%_\\').'%';
    }

    protected function hasFilters(array $filters, array $keys): bool
    {
        return collect($filters)->only($keys)->contains(fn ($v) => $v !== '');
    }
}
