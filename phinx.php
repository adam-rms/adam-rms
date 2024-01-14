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
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => getenv('bCMS__DB_HOSTNAME'),
            'name' => getenv('bCMS__DB_DATABASE'),
            'user' => getenv('bCMS__DB_USERNAME'),
            'pass' => getenv('bCMS__DB_PASSWORD'),
            'port' => getenv('bCMS__DB_PORT') ?: 3306,
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => getenv('bCMS__DB_HOSTNAME'),
            'name' => getenv('bCMS__DB_DATABASE'),
            'user' => getenv('bCMS__DB_USERNAME'),
            'pass' => getenv('bCMS__DB_PASSWORD'),
            'port' => getenv('bCMS__DB_PORT') ?: 3306,
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];