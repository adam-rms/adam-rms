<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Files on Server", "BREADCRUMB" => false];

if (!$AUTH->serverPermissionCheck("FILES:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

// Total storage used
$DBLIB->where("(s3files_meta_deleteOn IS NULL)");
$DBLIB->where("s3files_meta_physicallyStored", 1);
$PAGEDATA['totalStorageUsed'] = $DBLIB->getValue("s3files", "SUM(s3files_meta_size)");

// Storage used by each instance
$instances = $DBLIB->get('instances', null, ['instances_id', 'instances_name']);
$PAGEDATA['instancesForStorage'] = [];
foreach ($instances as $instance) {
	$DBLIB->where("s3files.instances_id", $instance['instances_id']);
	$DBLIB->where("(s3files_meta_deleteOn IS NULL)");
	$DBLIB->where("s3files_meta_physicallyStored", 1);
	$instance['STORAGEUSED'] = $DBLIB->getValue("s3files", "SUM(s3files_meta_size)");
    $PAGEDATA['instancesForStorage'][] = $instance;
}
usort($PAGEDATA['instancesForStorage'], fn($b, $a) => $a['STORAGEUSED'] <=> $b['STORAGEUSED']);

// Storage used by each type of file
$PAGEDATA['fileTypesForStorage'] = [];
$PAGEDATA['fileTypeIDDescriptors'] = $bCMS->s3FileTypeDescriptors();
foreach ($PAGEDATA['fileTypeIDDescriptors'] as $fileTypeID => $fileTypeDescriptor) {
    $DBLIB->where("s3files_meta_type", $fileTypeID);
    $DBLIB->where("(s3files_meta_deleteOn IS NULL)");
    $DBLIB->where("s3files_meta_physicallyStored", 1);
    $fileTypeStorageUsed = $DBLIB->getValue("s3files", "SUM(s3files_meta_size)");
    $PAGEDATA['fileTypesForStorage'][] = [
        "fileTypeID" => $fileTypeID,
        "descriptor" => $fileTypeDescriptor,
        "STORAGEUSED" => $fileTypeStorageUsed,
    ];
}
usort($PAGEDATA['fileTypesForStorage'], fn($b, $a) => $a['STORAGEUSED'] <=> $b['STORAGEUSED']);

// Top 20 files
$DBLIB->where("(s3files_meta_deleteOn IS NULL)");
$DBLIB->where("s3files_meta_physicallyStored", 1);
$DBLIB->join("instances", "s3files.instances_id = instances.instances_id", "LEFT");
$DBLIB->orderBy("s3files_meta_size", "DESC");
$PAGEDATA['top20Files'] = $DBLIB->get('s3files', 20, ['s3files.s3files_id', 's3files.s3files_name', 's3files.s3files_extension', 'instances.instances_name', 's3files_meta_size', 's3files_meta_type']);

// Files where instances have been deleted
$DBLIB->where("s3files_meta_type", 1);

$fileTypeIDDeletionDetectors = [
    5 => [
        "table" => "instances",
        "column" => "instances_logo",
    ],
    8 => [
        "table" => "maintenanceJobsMessages",
        "column" => "maintenanceJobsMessages_file",
    ],
    9 => [
        "table" => "users",
        "column" => "users_thumbnail",
    ],
    10 => [
        "table" => "instances",
        "column" => "instances_emailHeader",
    ],
    12 => [
        "table" => "modules",
        "column" => "modules_thumbnail",
    ]
];



echo $TWIG->render('server/server-files.twig', $PAGEDATA);
?>
