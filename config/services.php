<?php

return [
    'glinche' => [
        'base_url' => env('GLINCHE_API_URL', 'https://marketplace-dev.glinche-automobiles.com/api'),
        'email' => env('GLINCHE_API_EMAIL', ''),
        'password' => env('GLINCHE_API_PASSWORD', ''),

        'timeout' => (int) env('GLINCHE_API_TIMEOUT', 15),

        'token_ttl' => (int) env('GLINCHE_TOKEN_TTL', 50),

        'sync_ttl' => (int) env('GLINCHE_SYNC_TTL', 15),
    ],

];
