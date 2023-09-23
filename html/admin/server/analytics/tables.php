<?php
require_once __DIR__ . '/../../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Analytics - Database Tables", "BREADCRUMB" => false];
if (!$AUTH->serverPermissionCheck("VIEW-ANALYTICS")) die($TWIG->render('404.twig', $PAGEDATA));

$tables = ["analyticsEvents","assetCategories","assetCategoriesGroups","assetGroups","assetTypes","assets","assetsAssignments","assetsAssignmentsStatus","assetsBarcodes","assetsBarcodesScans","auditLog","authTokens","clients","cmsPages","cmsPagesDrafts","cmsPagesViews","crewAssignments","emailSent","emailVerificationCodes","instancePositions","instances","locations","locationsBarcodes","loginAttempts","maintenanceJobs","maintenanceJobsMessages","maintenanceJobsStatuses","manufacturers","modules","modulesSteps","passwordResetCodes","payments","positions","positionsGroups","projects","projectsFinanceCache","projectsNotes","projectsStatuses","projectsTypes","projectsVacantRoles","projectsVacantRolesApplications","s3files","signupCodes","userInstances","userModules","userModulesCertifications","userPositions","users"];

$PAGEDATA['tables'] = [];
$PAGEDATA['total'] = 0;
foreach ($tables as $table) {
  $count = $DBLIB->getValue ($table, "count(*)");
  $PAGEDATA['total'] += $count;
  $PAGEDATA['tables'][] = [
    "name" => $table,
    "count" => $count
  ];
}


echo $TWIG->render('server/analytics/tables.twig', $PAGEDATA);
?>




