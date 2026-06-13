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
