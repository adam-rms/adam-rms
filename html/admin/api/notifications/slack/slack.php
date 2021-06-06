<?php
require_once __DIR__ . '/../../apiHead.php';
function sendSlackNotification($user, $subject, $text)
{
    global $DBLIB,$CONFIG;
    $channel_id = $user["userData"]['users_oauth_slackid'];
    $token = $user["userData"]['users_oauth_slacktoken'];
    if (!$user or $channel_id == '' or $token == '') return false; //If the user hasn't linked slack yet

    // TODO add more detailed templates for slack notifications, will involve looking at the notify function because atm it only deals with email templates
    if ($token != null) {
        $data = array(
            "channel" => $channel_id,
            "blocks" => [
                [
                    "type" =>"section",
                    "text"=>
                        [
                            "type"=> "plain_text",
                            "text"=> $subject
                        ]
                ],
                [
                    "type" =>"section",
                    "text"=>
                        [
                            "type"=> "plain_text",
                            "text"=> $text
                        ]
                ]
            ]
        );
        $slack_call = curl_init();
        curl_setopt($slack_call, CURLOPT_URL, "https://slack.com/api/chat.postMessage");
        curl_setopt($slack_call, CURLOPT_POST, 1);
        curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($slack_call, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($slack_call, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer ${token}",
            'Content-type: application/json'
        ));
        $response = curl_exec($slack_call);
        curl_close($slack_call);
        $responseJson = json_decode($response,true);
        var_dump($responseJson);
        if ($responseJson['ok']) return true;
        else {
            trigger_error("Slack API issue " . $response, E_USER_WARNING);
            return true;
        }
    }
    else {
        trigger_error("Slack API Key not set", E_USER_WARNING);
        return true;
    }
}