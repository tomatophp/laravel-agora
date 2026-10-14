<?php

return [
    "agora" => [
        "app_id" => env('AGORA_APP_ID', null),
        "app_certificate" => env('AGORA_APP_CERTIFICATE', null),
        // how long a generated token stays valid, in seconds (default 1 hour)
        "expire" => env('AGORA_TOKEN_EXPIRE', 3600),
    ]
];
