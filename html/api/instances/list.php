<?php
require_once __DIR__ . '/../apiHeadSecure.php';
$return = [];
foreach ($AUTH->data['instances'] as $instance) {
    $return[] = [
        "this" => ($AUTH->data['instance']['instances_id'] == $instance['instances_id']),
        "instances_name" =>  $instance['instances_name'],
        "permissions" => $instance['permissions'],
        "instances_id" => $instance['instances_id'],
        "userInstances_label"=> $instance['userInstances_label']
    ];
}
finish(true, null, $return);