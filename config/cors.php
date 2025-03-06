<?php

return [
    'paths' => ['*', 'api/*', 'sanctum/csrf-cookie', 'api/documentation', 'api-docs.json', 'docs', 'api-docs'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
