<?php
require_once __DIR__ . '/../../common/coreHead.php';

header("Content-Security-Policy: default-src 'none';" .
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://*.adam-rms.com https://cdnjs.cloudflare.com  https://static.cloudflareinsights.com https://*.statuspage.io  http://static.hotjar.com https://static.hotjar.com https://script.hotjar.com;".
    //          We have laods of inline JS                                  Libs                                Google webmaster tools
    "style-src 'unsafe-inline' 'self' https://*.adam-rms.com https://cdnjs.cloudflare.com https://fonts.googleapis.com;".
    //          We have loads of inline CSS                 Libs                        GFonts
    "font-src 'self' data: https://assets.adam-rms.com https://fonts.googleapis.com https://fonts.gstatic.com https://cdnjs.cloudflare.com   http://script.hotjar.com https://script.hotjar.com;" .
    //                                               Loading in google fonts     more gfonts               Fonts from libs like fontawesome
    "manifest-src 'self' https://*.adam-rms.com;" .
    //          Show images on mobile devices like favicons
    "img-src 'self' data: blob: https://assets.adam-rms.com https://cdnjs.cloudflare.com https://*.adam-rms.com https://cloudflareinsights.com https://*.backblazeb2.com  https://script.hotjar.com http://script.hotjar.com;".
    //                                                    Uploads    Images from libs                 Images                CF Analytics      
    "connect-src 'self' https://sentry.io https://cloudflareinsights.com https://*.adam-rms.com    http://*.hotjar.com:* https://*.hotjar.com:* https://vc.hotjar.io:* https://surveystats.hotjar.io wss://*.hotjar.com;".
    //                  Error reporting    CF Analytics                  Connecting to AdamRMS API
    "frame-src https://*.statuspage.io  https://vars.hotjar.com;".
    "object-src 'self' blob:;".
    //          Inline PDFs generated by the system
    "frame-ancestors 'self';");


$DBLIB->where("instances_deleted",0);
$DBLIB->where("instances_publicConfig",NULL, "IS NOT");
$instances = $DBLIB->get("instances");
$PAGEDATA['INSTANCE'] = false;
foreach ($instances as $instance) {
    $instance['publicData'] = json_decode($instance['instances_publicConfig'],true);
    if (!is_array($instance['publicData']['customDomains'])) continue;
    foreach ($instance['publicData']['customDomains'] as $domain) {
        if (trim($_SERVER['SERVER_NAME']) == $domain) {
            $PAGEDATA['INSTANCE'] = $instance;
            break;
        }
    }
}
if (!$PAGEDATA['INSTANCE'] or !isset($PAGEDATA['INSTANCE']['publicData']['enabled']) or !$PAGEDATA['INSTANCE']['publicData']['enabled']) die($TWIG->render('404Public.twig', $PAGEDATA));

$PAGEDATA['INSTANCE']['FILES'] = $bCMS->s3List(15, $PAGEDATA['INSTANCE']['instances_id']);


$DBLIB->where("instances_id",$PAGEDATA['INSTANCE']['instances_id']);
$DBLIB->where("cmsPages_deleted",0);
$DBLIB->where("cmsPages_archived",0);
$DBLIB->where("cmsPages_showPublic",1);
$DBLIB->where("cmsPages_showPublicNav",1);
$DBLIB->where("cmsPages_subOf",NULL,"IS");
$DBLIB->orderBy("cmsPages_navOrder","ASC");
$DBLIB->orderBy("cmsPages_id","ASC");
$PAGEDATA['NAVIGATIONCMSPages'] = [];
foreach ($DBLIB->get("cmsPages",null,["cmsPages.*"]) as $page) {
    $DBLIB->where("instances_id",$PAGEDATA['INSTANCE']['instances_id']);
    $DBLIB->where("cmsPages_deleted",0);
    $DBLIB->where("cmsPages_archived",0);
    $DBLIB->where("cmsPages_showPublic",1);
    $DBLIB->where("cmsPages_showPublicNav",1);
    $DBLIB->where("cmsPages_subOf",$page['cmsPages_id']);
    $DBLIB->orderBy("cmsPages_name","ASC");
    $page['SUBPAGES'] = $DBLIB->get("cmsPages");
    $PAGEDATA['NAVIGATIONCMSPages'][] = $page;
}