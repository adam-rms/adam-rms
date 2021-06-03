<?php
require_once __DIR__ . '/../../apiHead.php';
function sendSlackNotification($user, $subject)
{
    global $DBLIB,$CONFIG;

    $DBLIB->where("users_userid", $user["userData"]['users_userid']);
    $userSlack = $DBLIB->getone("users",["users_oauth_slackid"]);
    $channel_id = $userSlack['users_oauth_slackid'];

    if (!$user or $channel_id == '') return false; //If the user hasn't linked slack yet

    $token = $CONFIG['SLACK_KEY'];

    // TODO add more detailed templates for slack notifications, will involve looking at the notify function because atm it only deals with email templates
    if ($token != null) {
        $data = array(
            "channel" => $channel_id,
            'type' => 'plain_text',
            'text' => $subject
        );

        $json_string = json_encode($data);

        $slack_call = curl_init();
        curl_setopt($slack_call, CURLOPT_URL, "https://slack.com/api/chat.postMessage");
        curl_setopt($slack_call, CURLOPT_POST, 1);
        curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($slack_call, CURLOPT_POSTFIELDS, $json_string);
        curl_setopt($slack_call, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer ${token}",
            'Content-type: application/json'
        ));

        curl_exec($slack_call);
        curl_close($slack_call);
        return true;
    }
    else {
        trigger_error("Slack API Key not set", E_USER_WARNING);
        return true;
    }
}