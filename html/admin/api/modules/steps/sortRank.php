<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("TRAINING:EDIT")) die("404");

foreach ($_POST['order'] as $count=>$item) {
    $DBLIB->where("modules.modules_deleted", 0);
    $DBLIB->where("modules.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->join("modules","modules.modules_id=modulesSteps.modules_id","LEFT");
    $DBLIB->where("modulesSteps.modulesSteps_id",$item);
    $DBLIB->where("modulesSteps_locked",0);
    $step = $DBLIB->getone("modulesSteps",["modulesSteps_id"]);
    if ($step) {
        $DBLIB->where("modulesSteps.modulesSteps_id",$step['modulesSteps_id']);
        if (!$DBLIB->update("modulesSteps", ["modulesSteps_order" => ($count+10)], 1)) finish(false);
    }

}
finish(true);
