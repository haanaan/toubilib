<?php
declare(strict_types=1);

return [
    'db_rdv' => [
        'host' => getenv('DB_HOST') ?: 'toubirdv.db',
        'dbname' => getenv('DB_NAME') ?: 'toubirdv',
        'user' => getenv('DB_USER') ?: 'toubirdv',
        'password' => getenv('DB_PASSWORD') ?: 'toubirdv',
        'port' => 5432
    ],
    'jwt' => [
        'secret' => getenv('JWT_SECRET') ?: 'votre-secret-jwt-super-securise-ici'
    ]
];
