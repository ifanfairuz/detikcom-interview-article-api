<?php

return [
    'cors' => [
        'allowed_methods' => ['GET', 'POST', 'PUT'],
        'allowed_origins' => ['*'],
        'allowed_headers' => ['*'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => false,
    ],
    'db' => [
        'db_connection' => env('db_connection', 'pgsql'),
        'db_host' => env('db_host', '127.0.0.1'),
        'db_port' => env('db_port', '5432'),
        'db_name' => env('db_name', ''),
        'db_user' => env('db_user', ''),
        'db_password' => env('db_password', ''),
        'db_socket' => env('db_socket', ''),
        'migration_table' => 'migrations'
    ]
];
