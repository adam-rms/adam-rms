<?php
if(file_exists(__DIR__ . '/.env')) {
    //Load local env file
    $dotEnvLib = Dotenv\Dotenv::createMutable(__DIR__);
    $dotEnvLib->load();
}

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
            'host' => getenv('MYSQLHOST'),
            'name' => getenv('MYSQLDATABASE'),
            'user' => getenv('MYSQLUSER'),
            'pass' => getenv('MYSQLPASSWORD'),
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => getenv('MYSQLHOST'),
            'name' => getenv('MYSQLDATABASE'),
            'user' => getenv('MYSQLUSER'),
            'pass' => getenv('MYSQLPASSWORD'),
            'port' => '3306',
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];
