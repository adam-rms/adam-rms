<?php
// Test-only DB access for the e2e suite, so specs can check what a request did to the database.
//   php db.php snapshot <instances_id>  - every row belonging to a business, as JSON
//   php db.php query <sql> [<params json>] - runs one statement, prints the rows (SELECT) as JSON
$db = new PDO(
    "mysql:host=" . getenv('DB_HOSTNAME') . ";port=" . (getenv('DB_PORT') ?: 3306) . ";dbname=" . getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);
function rows(string $sql, array $params = []): array {
    global $db;
    $statement = $db->prepare($sql);
    $statement->execute($params);
    return $statement->columnCount() ? $statement->fetchAll() : [];
}

if ($argv[1] === "query") {
    echo json_encode(rows($argv[2], json_decode($argv[3] ?? "[]", true)));
    exit;
}
if ($argv[1] !== "snapshot") exit("Unknown command\n");

$instance = (int) $argv[2];
$byInstance = ["instancePositions", "clients", "locations", "manufacturers", "assetCategoriesGroups", "assetCategories", "assetTypes",
    "assetGroups", "assets", "projectsTypes", "projectsStatuses", "assetsAssignmentsStatus", "projects", "maintenanceJobs",
    "maintenanceJobsStatuses", "cmsPages", "modules", "signupCodes", "s3files"];
$children = [ // table => SQL picking the rows that belong to the business through a parent row
    "assetsBarcodes" => "assets_id IN (SELECT assets_id FROM assets WHERE instances_id = ?)",
    "assetsAssignments" => "projects_id IN (SELECT projects_id FROM projects WHERE instances_id = ?)",
    "projectsNotes" => "projects_id IN (SELECT projects_id FROM projects WHERE instances_id = ?)",
    "payments" => "projects_id IN (SELECT projects_id FROM projects WHERE instances_id = ?)",
    "crewAssignments" => "projects_id IN (SELECT projects_id FROM projects WHERE instances_id = ?)",
    "projectsVacantRoles" => "projects_id IN (SELECT projects_id FROM projects WHERE instances_id = ?)",
    "projectsVacantRolesApplications" => "projectsVacantRoles_id IN (SELECT projectsVacantRoles_id FROM projectsVacantRoles JOIN projects USING (projects_id) WHERE instances_id = ?)",
    "maintenanceJobsMessages" => "maintenanceJobs_id IN (SELECT maintenanceJobs_id FROM maintenanceJobs WHERE instances_id = ?)",
    "cmsPagesDrafts" => "cmsPages_id IN (SELECT cmsPages_id FROM cmsPages WHERE instances_id = ?)",
    "modulesSteps" => "modules_id IN (SELECT modules_id FROM modules WHERE instances_id = ?)",
    "userModules" => "modules_id IN (SELECT modules_id FROM modules WHERE instances_id = ?)",
    "userModulesCertifications" => "modules_id IN (SELECT modules_id FROM modules WHERE instances_id = ?)",
    "locationsBarcodes" => "locations_id IN (SELECT locations_id FROM locations WHERE instances_id = ?)",
    "userInstances" => "instancePositions_id IN (SELECT instancePositions_id FROM instancePositions WHERE instances_id = ?)",
    // The business's users, minus the columns that change when they (or anyone) logs in
    "users" => "users_userid IN (SELECT users_userid FROM userInstances JOIN instancePositions USING (instancePositions_id) WHERE instances_id = ?)",
];
$snapshot = ["data" => ["instances" => rows("SELECT * FROM instances WHERE instances_id = ?", [$instance])]];
foreach ($byInstance as $table) $snapshot["data"][$table] = rows("SELECT * FROM `$table` WHERE instances_id = ?", [$instance]);
foreach ($children as $table => $where) $snapshot["data"][$table] = rows("SELECT * FROM `$table` WHERE $where", [$instance]);
foreach ($snapshot["data"]["users"] as &$user) unset($user["users_selectedInstanceIDLast"]);
// Project history: shown on the project page, so an entry written by another business's user is visible to this one
$snapshot["history"] = rows("SELECT auditLog_id, auditLog_actionType, auditLog_actionData, users_userid, projects_id FROM auditLog WHERE projects_id IN (SELECT projects_id FROM projects WHERE instances_id = ?) ORDER BY auditLog_id", [$instance]);
echo json_encode($snapshot);
