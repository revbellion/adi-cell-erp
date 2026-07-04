<?php

use Carbon\Carbon;

if (!function_exists('rp')) {
    function rp($amount): string
    {
        return 'Rp ' . number_format($amount ?? 0, 0, ',', '.');
    }
}

if (!function_exists('tgl')) {
    function tgl($date): string
    {
        if (!$date) return '-';
        return Carbon::parse($date)->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm');
    }
}

if (!function_exists('normalizePhone')) {
    function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return $phone;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($digits) > 2 && str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        }

        return $digits;
    }
}
