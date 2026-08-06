<?php

return [
    // Token bersama untuk API print-calc → POS (lihat .env PRINT_LOG_TOKEN)
    'token' => env('PRINT_LOG_TOKEN', ''),

    // Akun default untuk income/print order otomatis dari print-calc
    'default_account' => env('PRINT_LOG_ACCOUNT', 'Cash'),
];
