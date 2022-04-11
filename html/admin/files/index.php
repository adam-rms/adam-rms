<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(129)) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "File Browser", "BREADCRUMB" => false];

$PAGEDATA['folders'] = [];
$PAGEDATA['files'] = [];
$PAGEDATA['currentLocation'] = [
  "title" => "",
  "canUpload" => false,
  "upload" => [
    "type" => "",
    "typeId" => null,
    "subTypeId" => null,
    "imagesOnly" => false,
    "paste" => true
  ],
  "foldersAndLinks" => true
];
$PAGEDATA['backButton'] = true;
switch ($_GET['t']) { // t = type
  case "projects":
      if (!$AUTH->instancePermissionCheck(20)) die($TWIG->render('404.twig', $PAGEDATA));

      if (!isset($_GET['id']) and !isset($_GET['archived'])) { //Show option to view archived projects
        $PAGEDATA['folders'][] = [
          "name" => "Archived Projects",
          "slug" => "?t=projects&archived",
          "icon" => "fas fa-folder",
        ];
      }

      // Projects to select
      $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
      $DBLIB->where("projects.projects_deleted", 0);
      if (isset($_GET['archived'])) $DBLIB->where("projects.projects_archived", "1");
      elseif (!isset($_GET['id']))  $DBLIB->where("projects.projects_archived", "0"); //Hide archived projects, unless we're browsing a sub project

      if (isset($_GET['id'])) $DBLIB->where("projects.projects_parent_project_id",$_GET['projectId']); //Show a specific sub-projects, otherwise show all
      else $DBLIB->where("projects.projects_parent_project_id IS NULL");

      $DBLIB->orderBy("projects.projects_name", "ASC");
      $DBLIB->orderBy("projects.projects_created", "ASC");
      $projectList = $DBLIB->get("projects", null, ["projects_id", "projects_name"]);
      
      foreach ($projectList as $project) {
        $PAGEDATA['folders'][] = [
          "name" => $project['projects_name'],
          "slug" => "?t=projects&id=".$project['projects_id'],
        ];
      }
      
      // Files to select
      if (isset($_GET['id'])) {
        $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->where("projects.projects_id", $_GET['id']);
        $currentProject = $DBLIB->get("projects", null, ["projects_id", "projects_name"]);
        //Allow files to be uploaded to projects
        $PAGEDATA['currentLocation'] = [
          "title" => $currentProject['projects_name'],
          "canUpload" => $AUTH->instancePermissionCheck(102),
          "upload" => [
            "type" => "PROJECT-FILE",
            "typeId" => 7,
            "subTypeId" => $currentProject['projects_id'],
            "imagesOnly" => false,
            "paste" => true
          ],
          "foldersAndLinks" => true
        ];
        

        if (isset($_GET['cat'])) { //Category of file
          $PAGEDATA['currentLocation']['canUpload'] = false;
          if ($_GET['cat'] == "invoices") $PAGEDATA['files'] = $bCMS->s3List(20, $_GET['id'],'s3files_meta_uploaded', 'DESC');
          elseif ($_GET['cat'] == "quotes") $PAGEDATA['files'] = $bCMS->s3List(21, $_GET['id'],'s3files_meta_uploaded', 'DESC');
          else die($TWIG->render('404.twig', $PAGEDATA));
        } else {
          $PAGEDATA['files'] = $bCMS->s3List(7, $_GET['id']);
          if (count($bCMS->s3List(20, $_GET['id'])) > 0) {
            $PAGEDATA['folders'][] = [
              "name" => "Invoices",
              "slug" => "?t=projects&id=".$_GET['id']."&cat=invoices"
            ];
          }
          if (count($bCMS->s3List(21, $_GET['id'])) > 0) {
            $PAGEDATA['folders'][] = [
              "name" => "Quotes",
              "slug" => "?t=projects&id=".$_GET['id']."&cat=quotes"
            ];
          }          
        }


      }
      break;
  default:
      $PAGEDATA['backButton'] = false;
      if ($AUTH->instancePermissionCheck(20)) $PAGEDATA['folders'][] = ["name" => "Projects", "slug" => "?t=projects"];

      $PAGEDATA['currentLocation'] = [
        "title" => null,
        "canUpload" => $AUTH->instancePermissionCheck(130),
        "upload" => [
          "type" => "INSTANCE-FILE",
          "typeId" => 6,
          "subTypeId" => $AUTH->data['instance']['instances_id'], //TODO figure out how to make that work with folders
          "imagesOnly" => false,
          "paste" => true
        ],
        "foldersAndLinks" => true
      ];
      $PAGEDATA['files'] = $bCMS->s3List(6, 0,'s3files_meta_uploaded', 'DESC');
      break;
}


echo $TWIG->render('files/files_index.twig', $PAGEDATA);
?>
