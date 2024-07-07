<?php
/**
 * This file contains the notification types and methods defined throughout the codebase
 */
$NOTIFICATIONTYPES =
[
  "METHODS" => [
    0 => "Post",
    1 => "EMail"
  ],
  "TYPES" =>  [ //These need to be inorder and inorder of group
    [
      "id" => 1,
      "group" => "Account",
      "name" => "Password Reset",
      "methods" => [1],
      "default" => true,
      "canDisable" => false
    ],
    [
      "id" => 3,
      "group" => "Account",
      "name" => "Email verification",
      "methods" => [1],
      "default" => true,
      "canDisable" => false
    ],
    [
      "id" => 4,
      "group" => "Account",
      "name" => "Magic email login link",
      "methods" => [1],
      "default" => true,
      "canDisable" => false
    ],
    [
      "id" => 2,
      "group" => "Account",
      "name" => "Added to Business",
      "methods" => [1],
      "default" => true,
      "canDisable" => false
    ],
    [
      "id" => 11,
      "group" => "Crewing",
      "name" => "Added to Project Crew",
      "methods" => [1],
      "default" => true,
      "canDisable" => true
    ],
    [
      "id" => 10,
      "group" => "Crewing",
      "name" => "Removed from Project Crew",
      "methods" => [1],
      "default" => true,
      "canDisable" => true
    ],
    [
      "id" => 20,
      "group" => "Crewing",
      "name" => "Crew Role Name Changed",
      "methods" => [1],
      "default" => true,
      "canDisable" => true
    ],
    [
      "id" => 12,
      "group" => "Maintenance",
      "name" => "Tagged in new Maintenance Job",
      "methods" => [1],
      "default" => true,
      "canDisable" => true
    ],
    [
      "id" => 13,
      "group" => "Maintenance",
      "name" => "Sent message in Maintenance Job",
      "methods" => [1],
      "default" => true,
      "canDisable" => true
    ],
    [
      "id" => 14,
      "group" => "Maintenance",
      "name" => "Maintenance Job changed Status",
      "methods" => [1],
      "default" => true,
      "canDisable" => true
    ],
    [
      "id" => 15,
      "group" => "Maintenance",
      "name" => "Assigned Maintenance Job",
      "methods" => [1],
      "default" => true,
      "canDisable" => true
    ],
    [
      "id" => 16,
      "group" => "Asset Groups Watching",
      "name" => "Asset added to Group",
      "methods" => [1],
      "default" => false,
      "canDisable" => true
    ],
    [
      "id" => 17,
      "group" => "Asset Groups Watching",
      "name" => "Asset removed from Group",
      "methods" => [1],
      "default" => true,
      "canDisable" => true
    ],
    [
      "id" => 18,
      "group" => "Asset Groups Watching",
      "name" => "Asset assigned to Project",
      "methods" => [1],
      "default" => true,
      "canDisable" => true
    ],
    [
      "id" => 19,
      "group" => "Asset Groups Watching",
      "name" => "Asset removed from Project",
      "methods" => [1],
      "default" => true,
      "canDisable" => true
    ],
    [
      "id" => 30,
      "group" => "Business - Users",
      "name" => "User added to Business using a signup code",
      "methods" => [1],
      "default" => false,
      "canDisable" => true
    ],
    [
      "id" => 40,
      "group" => "Project",
      "name" => "Application made for a crew vacancy on a project you manage",
      "methods" => [1],
      "default" => true,
      "canDisable" => false
    ],
    [
      "id" => 41,
      "group" => "Project",
      "name" => "Application updates for a crew vacancy you applied to",
      "methods" => [1],
      "default" => true,
      "canDisable" => false
    ],
  ]
];
