<?php
if (isset($_GET['pdf'])) ini_set('max_execution_time', 300); //seconds
require_once __DIR__ . '/../common/headSecure.php';
use Money\Currency;
use Money\Money;

if (!$AUTH->instancePermissionCheck(20) or !isset($_GET['id'])) die($TWIG->render('404.twig', $PAGEDATA));

//The project itself
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_GET['id']);
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->join("users", "projects.projects_manager=users.users_userid", "LEFT");
$DBLIB->join("locations","locations.locations_id=projects.locations_id","LEFT");
$PAGEDATA['project'] = $DBLIB->getone("projects", ["projects.*", "clients.*", "users.users_userid", "users.users_name1", "users.users_name2", "users.users_email","locations.locations_name","locations.locations_address"]);
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
    global $DBLIB,$AUTH;
    $projectFinanceHelper = new projectFinance();
    $return = [];

    $DBLIB->where("payments.payments_deleted", 0);
    $DBLIB->orderBy("payments.payments_date", "ASC");
    $DBLIB->where("payments.projects_id", $project['projects_id']);
    $payments = $DBLIB->get("payments");
    $return['payments'] = ["received" => ["ledger" => [], "total" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']))], "sales" => ["ledger" => [], "total" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']))], "subHire" => ["ledger" => [], "total" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']))], "staff" => ["ledger" => [], "total" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']))]];
    foreach ($payments as $payment) {
        $key = false;
        switch ($payment['payments_type']) {
            case 1:
                $key = "received";
                break;
            case 2:
                $key = 'sales';
                break;
            case 3:
                $key = 'subHire';
                break;
            case 4:
                $key = 'staff';
                break;
        }
        if ($key) {
            $payment['payments_amount'] = new Money($payment['payments_amount'], new Currency($AUTH->data['instance']['instances_config_currency']));
            $payment['payments_amountTotal'] = $payment['payments_amount']->multiply($payment['payments_quantity']);
            $return['payments'][$key]['total'] = $payment['payments_amountTotal']->add($return['payments'][$key]['total']);
            $return['payments'][$key]['ledger'][] = $payment;
        } else throw new Exception("Unknown payment type found");
    }

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
    $return['mass'] = 0.0; //TODO evaluate whether using floats for mass is a good idea....
    $return['value'] = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
    $return['prices'] = ["subTotal" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])), "discounts" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])), "total" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']))];

    $return['priceMaths'] = $projectFinanceHelper->durationMaths($project['projects_dates_deliver_start'],$project['projects_dates_deliver_end']);
    $return['assetTypesCounter'] = [];
    foreach ($assets as $asset) {
        $return['mass'] += ($asset['assets_mass'] == null ? $asset['assetTypes_mass'] : $asset['assets_mass']);
        $asset['value'] = new Money(($asset['assets_value'] != null ? $asset['assets_value'] : $asset['assetTypes_value']), new Currency($AUTH->data['instance']['instances_config_currency']));
        $return['value'] = $return['value']->add($asset['value']);

        if (isset($return['assetTypesCounter'][$asset['assetTypes_id']])) $return['assetTypesCounter'][$asset['assetTypes_id']] += 1;
        else $return['assetTypesCounter'][$asset['assetTypes_id']] = 1;

        if ($asset['assetsAssignments_customPrice'] == null) {
            //The actual pricing calculator
            $asset['price'] = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $asset['price'] = $asset['price']->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($return['priceMaths']['days']));
            $asset['price'] = $asset['price']->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($return['priceMaths']['weeks']));
        } else $asset['price'] = new Money($asset['assetsAssignments_customPrice'],new Currency($AUTH->data['instance']['instances_config_currency']));

        $return['prices']['subTotal'] = $asset['price']->add($return['prices']['subTotal']);

        if ($asset['assetsAssignments_discount'] > 0) $asset['discountPrice'] = $asset['price']->multiply(1 - ($asset['assetsAssignments_discount'] / 100));
        else $asset['discountPrice'] = $asset['price'];

        $return['prices']['discounts'] = $return['prices']['discounts']->add($asset['price']->subtract($asset['discountPrice']));
        $return['prices']['total'] = $return['prices']['total']->add($asset['discountPrice']);

        $asset['flagsblocks'] = assetFlagsAndBlocks($asset['assets_id']);

        $asset['assetTypes_definableFields_ARRAY'] = array_filter(explode(",", $asset['assetTypes_definableFields']));

        if ($asset['instances_id'] != $project['instances_id']) $return['assetsAssignedSUB'][] = $asset;
        else $return['assetsAssigned'][] = $asset;
    }

    $return['payments']['subTotal'] = $return['prices']['total']->add($return['payments']['sales']['total'],$return['payments']['subHire']['total'],$return['payments']['staff']['total']);
    $return['payments']['total'] = $return['payments']['subTotal']->subtract($return['payments']['received']['total']);
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
        "projectsFinanceCache_equipmentSubTotal" =>$PAGEDATA['FINANCIALS']['prices']['subTotal']->getAmount(),
        "projectsFinanceCache_equiptmentDiscounts" =>$PAGEDATA['FINANCIALS']['prices']['discounts']->getAmount(),
        "projectsFinanceCache_equiptmentTotal" =>$PAGEDATA['FINANCIALS']['prices']['total']->getAmount(),
        "projectsFinanceCache_salesTotal" =>$PAGEDATA['FINANCIALS']['payments']['sales']['total']->getAmount(),
        "projectsFinanceCache_staffTotal" =>$PAGEDATA['FINANCIALS']['payments']['staff']['total']->getAmount(),
        "projectsFinanceCache_externalHiresTotal" => $PAGEDATA['FINANCIALS']['payments']['subHire']['total']->getAmount(),
        "projectsFinanceCache_paymentsReceived" =>$PAGEDATA['FINANCIALS']['payments']['received']['total']->getAmount(),
        "projectsFinanceCache_grandTotal" =>$PAGEDATA['FINANCIALS']['payments']['total']->getAmount(),
        "projectsFinanceCache_mass"=>$PAGEDATA['FINANCIALS']['mass'],
        "projectsFinanceCache_value"=>$PAGEDATA['FINANCIALS']['value']->getAmount(),
    ];
    $DBLIB->insert("projectsFinanceCache", $projectFinanceCacheInsert); //Add a cache for the finance of the project
//Just check the cache while we're here - shouldn't ever be thrown!
} elseif ($projectFinanceCache["projectsFinanceCache_equipmentSubTotal"] != $PAGEDATA['FINANCIALS']['prices']['subTotal']->getAmount()) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif ($projectFinanceCache["projectsFinanceCache_equiptmentDiscounts"] != $PAGEDATA['FINANCIALS']['prices']['discounts']->getAmount()) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif ($projectFinanceCache["projectsFinanceCache_equiptmentTotal"] != $PAGEDATA['FINANCIALS']['prices']['total']->getAmount()) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif ($projectFinanceCache["projectsFinanceCache_salesTotal"] != $PAGEDATA['FINANCIALS']['payments']['sales']['total']->getAmount()) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif ($projectFinanceCache["projectsFinanceCache_staffTotal"] != $PAGEDATA['FINANCIALS']['payments']['staff']['total']->getAmount()) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif ($projectFinanceCache["projectsFinanceCache_externalHiresTotal"] !=  $PAGEDATA['FINANCIALS']['payments']['subHire']['total']->getAmount()) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif ($projectFinanceCache["projectsFinanceCache_paymentsReceived"] != $PAGEDATA['FINANCIALS']['payments']['received']['total']->getAmount()) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif ($projectFinanceCache["projectsFinanceCache_grandTotal"] != $PAGEDATA['FINANCIALS']['payments']['total']->getAmount()) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif ($projectFinanceCache["projectsFinanceCache_value"] != $PAGEDATA['FINANCIALS']['value']->getAmount()) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);
elseif (round($projectFinanceCache["projectsFinanceCache_mass"]*100000) != round($PAGEDATA['FINANCIALS']['mass']*100000)) throw new Exception('Project finances don\'t match - on cache ' . $projectFinanceCache['projectsFinanceCache_id']);


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

//Locations
$DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->orderBy("locations.locations_name", "ASC");
$DBLIB->where("locations.locations_deleted", 0);
$PAGEDATA['locations'] = $DBLIB->get("locations",null,["locations.locations_name","locations.locations_id"]);

//Files
$PAGEDATA['files'] = $bCMS->s3List(7, $PAGEDATA['project']['projects_id']);

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
} elseif (isset($_GET['list']) and count($PAGEDATA['FINANCIALS']['assetsAssigned'])>0) echo $TWIG->render('project/project_assets.twig', $PAGEDATA);
elseif (isset($_GET['files'])) echo $TWIG->render('project/project_files.twig', $PAGEDATA);
else echo $TWIG->render('project/project_index.twig', $PAGEDATA);
?>
