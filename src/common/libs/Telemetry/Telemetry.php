<?php
class Telemetry
{
    const TELEMETRY_URL = "https://telemetry.bithell.studio/projects/adam-rms/upload.json";
    public function logTelemetry()
    {
        global $CONFIGCLASS, $bCMS, $DBLIB;
        $limitedMode = $CONFIGCLASS->get("TELEMETRY_MODE") == 'Limited';
        $data = [
            "rootUrl" => $CONFIGCLASS->get("ROOTURL"),
            "nanoid" => $CONFIGCLASS->get("TELEMETRY_NANOID"),
            "version" => $bCMS->getVersionNumber(),
            "devMode" => $CONFIGCLASS->get("DEV"),
            "hidden" => $CONFIGCLASS->get("TELEMETRY_SHOW_URL") == 'Disabled',
            "userDefinedString" => $CONFIGCLASS->get("TELEMETRY_NOTES"),
            "metaData" => [
                "instances" => false,
                "users" => false,
                "assetsCount" => false,
                "assetsValueUSD" => false,
                "assetsMassKg" => false,
            ],
        ];
        $DBLIB->where("instances_deleted", 0);
        $data['metaData']['instances'] = $DBLIB->getValue("instances", "COUNT(instances_id)");
        if (!$limitedMode) {
            $DBLIB->where("users_deleted", 0);
            $data['metaData']['users'] = $DBLIB->getValue("users", "COUNT(users_userid)");

            $DBLIB->where("assets_deleted", 0);
            $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
            $assetValues = $DBLIB->getOne("assets", ["COUNT(assets_id) AS count", "SUM(assetTypes_value) AS value", "SUM(assetTypes_mass) AS mass"]);
            $data['metaData']['assetsCount'] = $assetValues['count'];
            //$data['metaData']['assetsValueUSD'] = round($assetValues['value'],0); // This value is not adjusted for currency so is considered unreliable
            $data['metaData']['assetsMassKg'] = round($assetValues['mass'], 0);
        }
        try {
            if ($CONFIGCLASS->get("DEV") !== true) { // Skip telemetry in dev mode, but only at the last minute to ensure bugs are caught in dev with the above
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, self::TELEMETRY_URL);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($curl, CURLOPT_USERAGENT, 'api');
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
                curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 120);
                curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($curl);
                curl_close($curl);
            }
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
    }
}
