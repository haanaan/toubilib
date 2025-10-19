<?php
declare(strict_types=1);

return [
    'settings' => [
        'displayErrorDetails' => true,

        'db_prat' => [
            'driver' => $_ENV['DB_PRAT_DRIVER'] ?? 'pgsql',
            'host' => $_ENV['DB_PRAT_HOST'] ?? 'toubiprati.db',
            'port' => (int) ($_ENV['DB_PRAT_PORT'] ?? 5432),
            'name' => $_ENV['DB_PRAT_NAME'] ?? 'toubiprat',
            'user' => $_ENV['DB_PRAT_USER'] ?? 'toubiprat',
            'pass' => $_ENV['DB_PRAT_PASS'] ?? 'toubiprat',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],

        'db_rdv' => [
            'driver' => $_ENV['DB_RDV_DRIVER'] ?? 'pgsql',
            'host' => $_ENV['DB_RDV_HOST'] ?? 'toubirdv.db',
            'port' => (int) ($_ENV['DB_RDV_PORT'] ?? 5432),
            'name' => $_ENV['DB_RDV_NAME'] ?? 'toubirdv',
            'user' => $_ENV['DB_RDV_USER'] ?? 'toubirdv',
            'pass' => $_ENV['DB_RDV_PASS'] ?? 'toubirdv',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],

        'db_pat' => [
            'driver' => $_ENV['DB_PAT_DRIVER'] ?? 'pgsql',
            'host' => $_ENV['DB_PAT_HOST'] ?? 'toubipat.db',
            'port' => (int) ($_ENV['DB_PAT_PORT'] ?? 5432),
            'name' => $_ENV['DB_PAT_NAME'] ?? 'toubipat',
            'user' => $_ENV['DB_PAT_USER'] ?? 'toubipat',
            'pass' => $_ENV['DB_PAT_PASS'] ?? 'toubipat',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],

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
    ],
];
