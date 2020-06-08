<?php
if (isset($_GET['pdf'])) ini_set('max_execution_time', 300); //seconds
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(20) or !isset($_GET['id'])) die($TWIG->render('404.twig', $PAGEDATA));

//The project itself
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_GET['id']);
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->join("users", "projects.projects_manager=users.users_userid", "LEFT");
$PAGEDATA['project'] = $DBLIB->getone("projects", ["projects.*", "clients.*", "users.users_userid", "users.users_name1", "users.users_name2", "users.users_email"]);
if (!$PAGEDATA['project']) die("404");

//AuditLog
$DBLIB->where("auditLog.auditLog_deleted", 0);
$DBLIB->where("auditLog.projects_id", $PAGEDATA['project']['projects_id']);
$DBLIB->where("auditLog.auditLog_actionTable", "projects"); //TODO show more in the log but for now only project stuff
$DBLIB->join("users", "auditLog.users_userid=users.users_userid", "LEFT");
$DBLIB->orderBy("auditLog.auditLog_timestamp", "DESC");
$DBLIB->orderBy("auditLog.auditLog_id", "DESC");
$PAGEDATA['project']['auditLog'] = $DBLIB->get("auditLog",null, ["auditLog.*", "users.users_name1", "users.users_name2", "users.users_email"]);

$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA["project"]['projects_name'], "BREADCRUMB" => false];

//Edit Options
if ($AUTH->instancePermissionCheck(22)) {
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $PAGEDATA['clients'] = $DBLIB->get("clients", null, ["clients_id", "clients_name"]);
}
if ($AUTH->instancePermissionCheck(23)) {
    $DBLIB->orderBy("users.users_name1", "ASC");
    $DBLIB->orderBy("users.users_name2", "ASC");
    $DBLIB->orderBy("users.users_created", "ASC");
    $DBLIB->where("users_deleted", 0);
    $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
    $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
    $DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
    $DBLIB->where("userInstances.userInstances_deleted",  0);
    $PAGEDATA['potentialManagers'] = $DBLIB->get('users', null, ["users.users_name1", "users.users_name2", "users.users_userid"]);
}

//Payments and also
function projectFinancials($project) {
    global $DBLIB;
    $projectFinanceHelper = new projectFinance();
    $return = [];

    $DBLIB->where("payments.payments_deleted", 0);
    $DBLIB->orderBy("payments.payments_date", "ASC");
    $DBLIB->where("payments.projects_id", $project['projects_id']);
    $payments = $DBLIB->get("payments");
    $return['payments'] = ["received" => ["ledger" => [], "total" => 0.0], "sales" => ["ledger" => [], "total" => 0.0], "subHire" => ["ledger" => [], "total" => 0.0], "staff" => ["ledger" => [], "total" => 0.0]];
    foreach ($payments as $payment) {
        switch ($payment['payments_type']) {
            case 1:
                $return['payments']['received']['ledger'][] = $payment;
                $return['payments']['received']['total'] += $payment['payments_amount'];
                break;
            case 2:
                $return['payments']['sales']['ledger'][] = $payment;
                $return['payments']['sales']['total'] += ($payment['payments_amount']*$payment['payments_quantity']);
                break;
            case 3:
                $return['payments']['subHire']['ledger'][] = $payment;
                $return['payments']['subHire']['total'] += ($payment['payments_amount']*$payment['payments_quantity']);
                break;
            case 4:
                $return['payments']['staff']['ledger'][] = $payment;
                $return['payments']['staff']['total'] += ($payment['payments_amount']*$payment['payments_quantity']);
                break;
        }
    }
    $return['payments']['received']['total'] = round($return['payments']['received']['total'], 2, PHP_ROUND_HALF_UP);
    $return['payments']['sales']['total'] = round($return['payments']['sales']['total'], 2, PHP_ROUND_HALF_UP);
    $return['payments']['subHire']['total'] = round($return['payments']['subHire']['total'], 2, PHP_ROUND_HALF_UP);
    $return['payments']['staff']['total'] = round($return['payments']['staff']['total'], 2, PHP_ROUND_HALF_UP);

    //Assets
    $DBLIB->where("projects_id", $project['projects_id']);
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
    $DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id", "LEFT");
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->join("assetCategories", "assetTypes.assetCategories_id=assetCategories.assetCategories_id", "LEFT");
    $DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
    $DBLIB->join("instances", "assets.instances_id=instances.instances_id", "LEFT");
    $DBLIB->orderBy("instances.instances_name", "ASC");
    $DBLIB->orderBy("assetCategories.assetCategories_rank", "ASC");
    $DBLIB->orderBy("assetTypes.assetTypes_id", "ASC");
    $DBLIB->orderBy("assets.assets_tag", "ASC");
    $DBLIB->where("assets.assets_deleted", 0);
    $assets = $DBLIB->get("assetsAssignments", null, ["assetCategories.assetCategories_rank", "assetsAssignments.*", "manufacturers.manufacturers_name", "assetTypes.*", "assets.*", "assetCategories.assetCategories_name", "assetCategories.assetCategories_fontAwesome", "assetCategoriesGroups.assetCategoriesGroups_name", "instances.instances_name AS assetInstanceName", "instances.instances_id"]);


    $return['assetsAssigned'] = [];
    $return['assetsAssignedSUB'] = [];
    $return['mass'] = 0.0;
    $return['value'] = 0.0;
    $return['prices'] = ["subTotal" => 0.0, "discounts" => 0.0, "total" => 0.0];

    $return['priceMaths'] = $projectFinanceHelper->durationMaths($project['projects_dates_deliver_start'],$project['projects_dates_deliver_end']);

    $return['assetTypesCounter'] = [];
    foreach ($assets as $asset) {
        $return['mass'] += $asset['assetTypes_mass'];
        $return['value'] += $asset['assetTypes_value'];

        if (isset($return['assetTypesCounter'][$asset['assetTypes_id']])) $return['assetTypesCounter'][$asset['assetTypes_id']] += 1;
        else $return['assetTypesCounter'][$asset['assetTypes_id']] = 1;

        if ($asset['assetsAssignments_customPrice'] == null) {
            //The actual pricing calculator
            $asset['price'] = 0;
            $asset['price'] += $return['priceMaths']['days'] * ($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']);
            $asset['price'] += $return['priceMaths']['weeks'] * ($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']);
        } else $asset['price'] = $asset['assetsAssignments_customPrice'];
        $asset['price'] = round($asset['price'], 2, PHP_ROUND_HALF_UP);
        $return['prices']['subTotal'] += $asset['price'];

        if ($asset['assetsAssignments_discount'] > 0) {
            $asset['discountPrice'] = round(($asset['price'] * (1 - ($asset['assetsAssignments_discount'] / 100))), 2, PHP_ROUND_HALF_UP);
        } else $asset['discountPrice'] = $asset['price'];

        $return['prices']['discounts'] += ($asset['price'] - $asset['discountPrice']);
        $return['prices']['total'] += $asset['discountPrice'];

        $asset['flagsblocks'] = assetFlagsAndBlocks($asset['assets_id']);
        $asset['flagsblocks'] = assetFlagsAndBlocks($asset['assets_id']);

        $asset['assetTypes_definableFields_ARRAY'] = array_filter(explode(",", $asset['assetTypes_definableFields']));

        if ($asset['instances_id'] != $project['instances_id']) $return['assetsAssignedSUB'][] = $asset;
        else $return['assetsAssigned'][] = $asset;
    }

    $return['payments']['subTotal'] = $return['prices']['total'] + $return['payments']['sales']['total'] + $return['payments']['subHire']['total'] + $return['payments']['staff']['total'];
    $return['payments']['total'] = $return['payments']['subTotal'] - $return['payments']['received']['total'];
    $return['payments']['total'] = round($return['payments']['total'],2);
    return $return;
}
$PAGEDATA['FINANCIALS'] = projectFinancials($PAGEDATA['project']);

$DBLIB->where("projects_id",$PAGEDATA['project']['projects_id']);
$DBLIB->orderBy("projectsFinanceCache_timestamp", "DESC");
$projectFinanceCache = $DBLIB->getone("projectsFinanceCache");
if (!$projectFinanceCache) {
    //Insert a new project finance cache for this project as it doesn't seem to have one hmmm
    $projectFinanceCacheInsert = [
        "projects_id" => $PAGEDATA['project']['projects_id'],
        "projectsFinanceCache_timestamp" => date("Y-m-d H:i:s"),
        "projectsFinanceCache_equipmentSubTotal" =>$PAGEDATA['FINANCIALS']['prices']['subTotal'],
        "projectsFinanceCache_equiptmentDiscounts" =>$PAGEDATA['FINANCIALS']['prices']['discounts'],
        "projectsFinanceCache_equiptmentTotal" =>$PAGEDATA['FINANCIALS']['prices']['total'],
        "projectsFinanceCache_salesTotal" =>$PAGEDATA['FINANCIALS']['payments']['sales']['total'],
        "projectsFinanceCache_staffTotal" =>$PAGEDATA['FINANCIALS']['payments']['staff']['total'],
        "projectsFinanceCache_externalHiresTotal" => $PAGEDATA['FINANCIALS']['payments']['subHire']['total'],
        "projectsFinanceCache_paymentsReceived" =>$PAGEDATA['FINANCIALS']['payments']['received']['total'],
        "projectsFinanceCache_grandTotal" =>$PAGEDATA['FINANCIALS']['payments']['total'],
        "projectsFinanceCache_mass"=>$PAGEDATA['FINANCIALS']['mass'],
        "projectsFinanceCache_value"=>$PAGEDATA['FINANCIALS']['value'],
    ];
    $DBLIB->insert("projectsFinanceCache", $projectFinanceCacheInsert); //Add a cache for the finance of the project
//Just check the cache while we're here - shouldn't ever be thrown!
} elseif (compareFloats($projectFinanceCache["projectsFinanceCache_equipmentSubTotal"],$PAGEDATA['FINANCIALS']['prices']['subTotal']) !== true) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif (compareFloats($projectFinanceCache["projectsFinanceCache_equiptmentDiscounts"],$PAGEDATA['FINANCIALS']['prices']['discounts']) !== true) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif (compareFloats($projectFinanceCache["projectsFinanceCache_equiptmentTotal"],$PAGEDATA['FINANCIALS']['prices']['total']) !== true) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif (compareFloats($projectFinanceCache["projectsFinanceCache_salesTotal"],$PAGEDATA['FINANCIALS']['payments']['sales']['total']) !== true) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif (compareFloats($projectFinanceCache["projectsFinanceCache_staffTotal"],$PAGEDATA['FINANCIALS']['payments']['staff']['total']) !== true) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif (compareFloats($projectFinanceCache["projectsFinanceCache_externalHiresTotal"], $PAGEDATA['FINANCIALS']['payments']['subHire']['total']) !== true) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif (compareFloats($projectFinanceCache["projectsFinanceCache_paymentsReceived"],$PAGEDATA['FINANCIALS']['payments']['received']['total']) !== true) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif (compareFloats($projectFinanceCache["projectsFinanceCache_grandTotal"],$PAGEDATA['FINANCIALS']['payments']['total']) !== true) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif (compareFloats($projectFinanceCache["projectsFinanceCache_mass"],$PAGEDATA['FINANCIALS']['mass']) !== true) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif (compareFloats($projectFinanceCache["projectsFinanceCache_value"],$PAGEDATA['FINANCIALS']['value']) !== true) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);


usort($PAGEDATA['FINANCIALS']['payments']['subHire']['ledger'], function ($a, $b) {
    // Sort sub-hires in order of supplier so you can do supplier headings
    return $a['payments_supplier'] <=> $b['payments_supplier'];
});
usort($PAGEDATA['FINANCIALS']['payments']['sales']['ledger'], function ($a, $b) {
    // Sort sub-hires in order of supplier so you can do supplier headings
    return $a['payments_supplier'] <=> $b['payments_supplier'];
});
usort($PAGEDATA['FINANCIALS']['payments']['staff']['ledger'], function ($a, $b) {
    // Sort sub-hires in order of supplier so you can do supplier headings
    return $a['payments_supplier'] <=> $b['payments_supplier'];
});

//Notes
$DBLIB->where("projectsNotes_deleted", 0);
$DBLIB->where("projects_id", $PAGEDATA['project']['projects_id']);
$DBLIB->orderBy("projectsNotes_id", "ASC");
$PAGEDATA['project']['notes'] = $DBLIB->get("projectsNotes");

//Crew
$DBLIB->where("projects_id", $PAGEDATA['project']['projects_id']);
$DBLIB->where("crewAssignments.crewAssignments_deleted", 0);
$DBLIB->join("users", "crewAssignments.users_userid=users.users_userid", "LEFT");
$DBLIB->orderBy("crewAssignments.crewAssignments_rank", "ASC");
$DBLIB->orderBy("crewAssignments.crewAssignments_id", "ASC");
$PAGEDATA['project']['crewAssignments'] = $DBLIB->get("crewAssignments", null, ["crewAssignments.*", "users.users_name1", "users.users_name2", "users.users_email"]);

if (isset($_GET['loadingView'])) {
    $PAGEDATA['loadingView'] = true;
    $PAGEDATA['loadingViewStatusID'] = (isset($_GET['loadingViewStatus']) ? $_GET['loadingViewStatus'] : 5);
    $PAGEDATA['loadingViewStatus'] = $GLOBALS['ASSETASSIGNMENTSTATUSES'][$PAGEDATA['loadingViewStatusID']];
    $PAGEDATA['loadingViewStatusArray'] = $GLOBALS['ASSETASSIGNMENTSTATUSES'];
}
else $PAGEDATA['loadingView'] = false;

if (isset($_GET['pdf'])) {
    if (isset($_GET['finance'])) $PAGEDATA['showFinance'] = true;

    //die($TWIG->render('project/pdf.twig', $PAGEDATA));
    $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'mpdf','mode' => 'utf-8', 'format' => 'A4', 'setAutoTopMargin' => 'pad', "margin_top" => 5]);
    $mpdf->SetTitle($PAGEDATA['project']['projects_name'] . " - ". $PAGEDATA['project']['clients_name']);
    $mpdf->SetAuthor($PAGEDATA['USERDATA']['instance']['instances_name']);
    $mpdf->SetCreator("AdamRMS - the rental management system from Bithell Studios");
    $mpdf->SetSubject($PAGEDATA['project']['projects_name'] . " - ". $PAGEDATA['project']['clients_name'] . " | " . $PAGEDATA['USERDATA']['instance']['instances_name']);
    $mpdf->SetKeywords("quotation,AdamRMS");
    $mpdf->SetHTMLFooter('
                <table width="100%">
                    <tr>
                        <td width="45%">Generated {DATE j M Y h:i:sa}</td>
                        <td width="10%" align="center">{PAGENO}/{nbpg}</td>
                        <td width="45%" style="text-align: right;">AdamRMS | &copy;{DATE Y} Bithell Studios Ltd.</td>
                    </tr>
                </table>
             ');
    $mpdf->WriteHTML($TWIG->render('project/pdf.twig', $PAGEDATA));
    $mpdf->Output(mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', ($PAGEDATA['project']['projects_name'] . " - ". $PAGEDATA['project']['clients_name'] . " - " . $PAGEDATA['USERDATA']['instance']['instances_name'])). '.pdf', 'I');
} else echo $TWIG->render('project/project_index.twig', $PAGEDATA);
?>
