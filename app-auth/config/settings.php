<?php
declare(strict_types=1);

return [
    'settings' => [
        'displayErrorDetails' => true,

        'db_auth' => [
            'driver' => $_ENV['DB_AUTH_DRIVER'] ?? 'pgsql',
            'host' => $_ENV['DB_AUTH_HOST'] ?? 'toubiauth.db',
            'port' => (int) ($_ENV['DB_AUTH_PORT'] ?? 5432),
            'name' => $_ENV['DB_AUTH_NAME'] ?? 'toubiauth',
            'user' => $_ENV['DB_AUTH_USER'] ?? 'toubiauth',
            'pass' => $_ENV['DB_AUTH_PASS'] ?? 'toubiauth',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],

        'jwt' => [
            'secret' => $_ENV['JWT_SECRET'] ?? 'change-me-super-secret-key-longue',
            'algo' => 'HS256',
            'access_ttl' => 3600,
            'refresh_ttl' => 1209600
        ]
    ],
];
