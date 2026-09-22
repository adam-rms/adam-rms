<?php
// Test-only: writes the config values that the first-run setup form would otherwise ask for,
// so the app goes straight to the login page. Never run this against a real database.
$db = new PDO(
    "mysql:host=" . getenv('DB_HOSTNAME') . ";port=" . (getenv('DB_PORT') ?: 3306) . ";dbname=" . getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD')
);
$values = [
    "ROOTURL" => getenv('CONFIG_ROOTURL'),
    "AUTH_JWTKey" => str_repeat("e2eTestJwtKey000", 4),
    "TELEMETRY_NANOID" => "e2eTestInstallation00",
];
$statement = $db->prepare("REPLACE INTO config (config_key, config_value) VALUES (?, ?)");
foreach ($values as $key => $value) $statement->execute([$key, $value]);
echo "AdamRMS e2e - config seeded\n";
