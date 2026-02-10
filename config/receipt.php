<?php

return [
    'company' => [
        'name' => env('RECEIPT_COMPANY_NAME', 'Empresa'),
        'nit' => env('RECEIPT_COMPANY_NIT', 'NIT'),
        'owner' => env('RECEIPT_COMPANY_OWNER', 'Titular'),
        'address' => env('RECEIPT_COMPANY_ADDRESS', ''),
        'phone' => env('RECEIPT_COMPANY_PHONE', ''),
        'city' => env('RECEIPT_COMPANY_CITY', ''),
    ],

    'dian' => [
        'resolution' => env('DIAN_RESOLUTION', ''),
        'regime' => env('DIAN_REGIME', ''),
        'activity' => env('DIAN_ACTIVITY', ''),
        'email' => env('DIAN_EMAIL', ''),
    ],
];
