<?php

return [
    'cash_name' => env('ACCOUNT_CASH_NAME', 'Cash'),
    'bca_name' => env('ACCOUNT_BCA_NAME', 'BCA'),
    'in_transit_name' => env('ACCOUNT_IN_TRANSIT_NAME', 'Pending'),
    'low_balance_threshold' => env('LOW_BALANCE_THRESHOLD', 250000),
];
