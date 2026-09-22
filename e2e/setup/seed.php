<?php
// Test-only: puts the data the e2e suite relies on into a known state. Never run this against a real database.
$db = new PDO(
    "mysql:host=" . getenv('DB_HOSTNAME') . ";port=" . (getenv('DB_PORT') ?: 3306) . ";dbname=" . getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Config the first-run setup form would otherwise ask for, so the app goes straight to the login page
$values = [
    "ROOTURL" => getenv('CONFIG_ROOTURL'),
    "AUTH_JWTKey" => str_repeat("e2eTestJwtKey000", 4),
    "TELEMETRY_NANOID" => "e2eTestInstallation00",
];
$statement = $db->prepare("REPLACE INTO config (config_key, config_value) VALUES (?, ?)");
foreach ($values as $key => $value) $statement->execute([$key, $value]);

// The super admin the tests log in as (test@example.com / password!). DefaultUserSeeder skips databases
// that already have users, and the password may have been changed since, so make sure it's as expected.
$credentials = [
    "users_salty1" => "8smqAFD9",
    "users_password" => "fa5a51baef12914c7f2e0e1176a030bf086d26edae298c25d5f84c90bc72ecd7",
    "users_salty2" => "uOhfrOCW",
    "users_hash" => "sha256",
];
$statement = $db->prepare("SELECT users_userid FROM users WHERE users_email = 'test@example.com'");
$statement->execute();
$userId = $statement->fetchColumn();
if ($userId === false) {
    $db->prepare("INSERT INTO users (users_username, users_name1, users_name2, users_email, users_created, users_salty1, users_password, users_salty2, users_hash)
        VALUES ('e2e-test-user', 'E2E', 'Test', 'test@example.com', NOW(), :users_salty1, :users_password, :users_salty2, :users_hash)")
        ->execute($credentials);
    $userId = $db->lastInsertId();
} else {
    $db->prepare("UPDATE users SET users_salty1 = :users_salty1, users_password = :users_password, users_salty2 = :users_salty2, users_hash = :users_hash WHERE users_userid = :users_userid")
        ->execute($credentials + ["users_userid" => $userId]);
}
$db->prepare("UPDATE users SET users_changepass = 0, users_suspended = 0, users_deleted = 0, users_emailVerified = 1 WHERE users_userid = ?")
    ->execute([$userId]);

// Position 1 is the super administrator position created by PositionsSeeder
$statement = $db->prepare("SELECT COUNT(*) FROM userPositions WHERE users_userid = ? AND positions_id = 1 AND userPositions_start <= NOW() AND (userPositions_end IS NULL OR userPositions_end > NOW())");
$statement->execute([$userId]);
if ($statement->fetchColumn() == 0) {
    $db->prepare("INSERT INTO userPositions (users_userid, userPositions_start, positions_id, userPositions_show) VALUES (?, NOW(), 1, 1)")
        ->execute([$userId]);
}

echo "AdamRMS e2e - database seeded\n";
