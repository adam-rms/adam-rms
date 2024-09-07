<?php
return
    [
        'paths' => [
            'migrations' => __DIR__ . '/db/migrations',
            'seeds' => __DIR__ . '/db/seeds'
        ],
        'schema_file' => __DIR__ . '/db/schema.php',
        'foreign_keys' => true,
        'environments' => [
            'migration_table' => 'phinxlog',
            'default_environment' => 'production',
            'production' => [
                'adapter' => 'mysql',
                'host' => getenv('DB_HOSTNAME'),
                'name' => getenv('DB_DATABASE'),
                'user' => getenv('DB_USERNAME'),
                'pass' => getenv('DB_PASSWORD'),
                'port' => getenv('DB_PORT') ?: 3306,
                'charset' => 'utf8',
            ],
            'development' => [
                'adapter' => 'mysql',
                'host' => getenv('DB_HOSTNAME'),
                'name' => getenv('DB_DATABASE'),
                'user' => getenv('DB_USERNAME'),
                'pass' => getenv('DB_PASSWORD'),
                'port' => getenv('DB_PORT') ?: 3306,
                'charset' => 'utf8',
            ]
        ],
        'version_order' => 'creation'
    ];
