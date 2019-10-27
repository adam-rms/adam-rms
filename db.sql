create table actionsCategories
(
  actionsCategories_id    int auto_increment
    primary key,
  actionsCategories_name  varchar(500)  not null,
  actionsCategories_order int default 0 null
);

create table actions
(
  actions_id           int auto_increment
    primary key,
  actions_name         varchar(255) not null,
  actionsCategories_id int          not null,
  actions_dependent    varchar(500) null,
  actions_incompatible varchar(500) null,
  constraint actions_actionsCategories_actionsCategories_id_fk
    foreign key (actionsCategories_id) references actionsCategories (actionsCategories_id)
);


create table loginAttempts
(
  loginAttempts_id          int(255) auto_increment
    primary key,
  loginAttempts_timestamp   timestamp default CURRENT_TIMESTAMP not null,
  loginAttempts_textEntered varchar(500)                        not null,
  loginAttempts_ip          varchar(500)                        null,
  loginAttempts_blocked     tinyint(1)                          not null,
  loginAttempts_successful  tinyint(1)                          not null
);

create table positions
(
  positions_id              int auto_increment
    primary key,
  positions_displayName     varchar(255)                  not null,
  positions_positionsGroups varchar(500)                  null,
  positions_rank            tinyint(1) unsigned default 4 not null comment 'Rank of the position - so that the most senior position for a user is shown as their "main one". 0 is the most senior'
);

create table positionsGroups
(
  positionsGroups_id      int auto_increment
    primary key,
  positionsGroups_name    varchar(255)  not null,
  positionsGroups_actions varchar(1000) null
);

create table s3files
(
  s3files_id                    int auto_increment
    primary key,
  s3files_path                  varchar(255)                          null comment 'NO LEADING /',
  s3files_filename              varchar(255)                          not null,
  s3files_extension             varchar(255)                          not null,
  s3files_original_name         varchar(500)                          null comment 'What was this file originally called when it was uploaded? For things like file attachments
',
  s3files_region                varchar(255)                          not null,
  s3files_endpoint              varchar(255)                          not null,
  s3files_cdn_endpoint          varchar(255)                          not null,
  s3files_bucket                varchar(255)                          not null,
  s3files_meta_size             bigint                                not null comment 'Size of the file in bytes',
  s3files_meta_public           tinyint(1)  default 0                 not null,
  s3files_meta_type             tinyint(11) default 0                 not null comment '0 = undefined
Rest are set out in /html/files/index.php',
  s3files_meta_subType          tinyint(11)                           null comment 'Depends what it is - each module that uses the file handler will be setting this for themselves',
  s3files_meta_uploaded         timestamp   default CURRENT_TIMESTAMP not null,
  users_userid                  int                                   null comment 'Who uploaded it?',
  s3files_meta_deleteOn         date                                  null comment 'Delete this file on this set date (basically if you hit delete we will kill it after say 30 days)',
  s3files_meta_physicallyStored tinyint(1)  default 1                 not null comment 'If we have the file it''s 1 - if we deleted it it''s 0 but the "deleteOn" is set. If we lost it it''s 0 with a null "delete on"'
);

create table users
(
  users_username         varchar(200)                         null,
  users_name1            varchar(100)                         null,
  users_name2            varchar(100)                         null,
  users_userid           int(32) auto_increment
    primary key,
  users_salty1           varchar(30)                          null,
  users_password         varchar(150)                         null,
  users_salty2           varchar(50)                          null,
  users_hash             varchar(255)                         not null,
  users_email            varchar(257)                         null,
  users_created          timestamp  default CURRENT_TIMESTAMP null comment 'When user signed up',
  users_notes            text                                 null comment 'Internal Notes - Not visible to user',
  users_thumbnail        int                                  null,
  users_changepass       tinyint(1) default 0                 not null,
  users_suspended        tinyint(1) default 0                 not null,
  users_deleted          tinyint(1) default 0                 null,
  users_emailVerified    tinyint(1) default 0                 not null,
  users_social_facebook  varchar(100)                         null,
  users_social_twitter   varchar(100)                         null,
  users_social_instagram varchar(100)                         null,
  users_social_linkedin  varchar(100)                         null,
  users_social_snapchat  varchar(100)                         null
);

create table auditLog
(
  auditLog_id           int auto_increment
    primary key,
  auditLog_actionType   varchar(500)                        null,
  auditLog_actionTable  varchar(500)                        null,
  auditLog_actionData   varchar(500)                        null,
  auditLog_timestamp    timestamp default CURRENT_TIMESTAMP not null,
  users_userid          int                                 null,
  auditLog_actionUserid int                                 null,
  constraint auditLog_users_users_userid_fk
    foreign key (users_userid) references users (users_userid),
  constraint auditLog_users_users_userid_fk_2
    foreign key (auditLog_actionUserid) references users (users_userid)
);

create table authTokens
(
  authTokens_id        int auto_increment
    primary key,
  authTokens_token     varchar(500)                         not null,
  authTokens_created   timestamp  default CURRENT_TIMESTAMP not null,
  authTokens_ipAddress varchar(500)                         null,
  users_userid         int(32)                              not null,
  authTokens_valid     tinyint(1) default 1                 not null comment '1 for true. 0 for false',
  authTokens_adminId   int(100)                             null,
  constraint token
    unique (authTokens_token),
  constraint authTokens_users_users_userid_fk
    foreign key (users_userid) references users (users_userid),
  constraint authTokens_users_users_userid_fk_2
    foreign key (authTokens_adminId) references users (users_userid)
);

create table emailSent
(
  emailSent_id        int auto_increment
    primary key,
  users_userid        int                                 not null,
  emailSent_html      longtext                            not null,
  emailSent_subject   varchar(255)                        not null,
  emailSent_sent      timestamp default CURRENT_TIMESTAMP not null,
  emailSent_fromEmail varchar(200)                        not null,
  emailSent_fromName  varchar(200)                        not null,
  emailSent_toName    varchar(200)                        not null,
  emailSent_toEmail   varchar(200)                        not null,
  constraint emailSent_users_users_userid_fk
    foreign key (users_userid) references users (users_userid)
);

create table emailVerificationCodes
(
  emailVerificationCodes_id        int auto_increment
    primary key,
  emailVerificationCodes_code      varchar(1000)                        not null,
  emailVerificationCodes_used      tinyint(1) default 0                 not null,
  emailVerificationCodes_timestamp timestamp  default CURRENT_TIMESTAMP not null,
  emailVerificationCodes_valid     int        default 1                 not null,
  users_userid                     int                                  not null,
  constraint emailVerificationCodes_users_users_userid_fk
    foreign key (users_userid) references users (users_userid)
);

create table passwordResetCodes
(
  passwordResetCodes_id        int auto_increment
    primary key,
  passwordResetCodes_code      varchar(1000)                        not null,
  passwordResetCodes_used      tinyint(1) default 0                 not null,
  passwordResetCodes_timestamp timestamp  default CURRENT_TIMESTAMP not null,
  passwordResetCodes_valid     int        default 1                 not null,
  users_userid                 int                                  not null,
  constraint passwordResetCodes_users_users_userid_fk
    foreign key (users_userid) references users (users_userid)
);

create table userPositions
(
  userPositions_id               int auto_increment
    primary key,
  users_userid                   int                                  null,
  userPositions_start            timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
  userPositions_end              timestamp                            null,
  positions_id                   int                                  null comment 'Can be null if you like - as long as you set the relevant other fields',
  userPositions_displayName      varchar(255)                         null,
  userPositions_extraPermissions varchar(500)                         null comment 'Allow a few extra permissions to be added just for this user for that exact permissions term
',
  userPositions_show             tinyint(1) default 1                 not null,
  constraint userPositions_positions_positions_id_fk
    foreign key (positions_id) references positions (positions_id),
  constraint userPositions_users_users_userid_fk
    foreign key (users_userid) references users (users_userid)
);

create index username_2
  on users (users_userid);
  
  
  
  INSERT INTO nouse.positionsGroups (positionsGroups_id, positionsGroups_name, positionsGroups_actions) VALUES (1, 'Administrator', '11,12,4,5,6,7,9,10,13,2,3,14');
INSERT INTO nouse.positionsGroups (positionsGroups_id, positionsGroups_name, positionsGroups_actions) VALUES (2, 'Managerial Committee', '3,11,2,6');
INSERT INTO nouse.positionsGroups (positionsGroups_id, positionsGroups_name, positionsGroups_actions) VALUES (3, 'Editor', '2');
INSERT INTO nouse.actions (actions_id, actions_name, actionsCategories_id, actions_dependent, actions_incompatible) VALUES (2, 'Access a list of users', 1, null, null);
INSERT INTO nouse.actions (actions_id, actions_name, actionsCategories_id, actions_dependent, actions_incompatible) VALUES (3, 'View emails in the user list', 1, '2', null);
INSERT INTO nouse.actions (actions_id, actions_name, actionsCategories_id, actions_dependent, actions_incompatible) VALUES (4, 'Create a new user', 1, '2', null);
INSERT INTO nouse.actions (actions_id, actions_name, actionsCategories_id, actions_dependent, actions_incompatible) VALUES (5, 'Edit details about a user', 1, '2,4', null);
INSERT INTO nouse.actions (actions_id, actions_name, actionsCategories_id, actions_dependent, actions_incompatible) VALUES (6, 'View mailing for a user', 1, '2', null);
INSERT INTO nouse.actions (actions_id, actions_name, actionsCategories_id, actions_dependent, actions_incompatible) VALUES (7, 'Logout a user across all devices', 1, '2', null);
INSERT INTO nouse.actions (actions_id, actions_name, actionsCategories_id, actions_dependent, actions_incompatible) VALUES (9, 'Suspend a user', 1, '2,7', null);
INSERT INTO nouse.actions (actions_id, actions_name, actionsCategories_id, actions_dependent, actions_incompatible) VALUES (10, 'View site as a user', 1, '2,3,5,6,7,9', null);
INSERT INTO nouse.actions (actions_id, actions_name, actionsCategories_id, actions_dependent, actions_incompatible) VALUES (11, 'Access a list of permissions', 2, null, null);
INSERT INTO nouse.actions (actions_id, actions_name, actionsCategories_id, actions_dependent, actions_incompatible) VALUES (12, 'Edit list of permissions', 2, null, null);
INSERT INTO nouse.actions (actions_id, actions_name, actionsCategories_id, actions_dependent, actions_incompatible) VALUES (13, 'Change a user''s permissions', 2, '5', null);
INSERT INTO nouse.actions (actions_id, actions_name, actionsCategories_id, actions_dependent, actions_incompatible) VALUES (14, 'Set a user''s thumbnail', 1, '5', null);

