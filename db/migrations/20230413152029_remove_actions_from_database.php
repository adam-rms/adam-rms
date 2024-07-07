<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveActionsFromDatabase extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        /**
         * Having actions and instance actions in the database resulted in a number of migrations, causing challenging merge conflicts.
         * 
         * This migration removes the actions from the database, and replaces them with an array, but to do this change the database must be updated to change the numeric values to the new string values.
         */
        $legacyServerActionsLookupTable = [
            2 => 'USERS:VIEW',
            5 => 'USERS:EDIT',
            6 => 'USERS:VIEW:MAILINGS',
            7 => 'VIEW-AUDIT-LOG',
            8 => 'INSTANCES:CREATE',
            9 => 'USERS:EDIT:SUSPEND',
            10 => 'USERS:VIEW_SITE_AS',
            11 => 'PERMISSIONS:VIEW',
            12 => 'PERMISSIONS:EDIT',
            13 => 'PERMISSIONS:EDIT:USER_POSITION',
            14 => 'USERS:EDIT:THUMBNAIL',
            15 => 'USERS:DELETE',
            16 => 'USERS:VIEW:OWN_POSITIONS',
            17 => 'USE-DEV',
            18 => 'VIEW-PHPINFO',
            19 => 'ASSETS:EDIT:ANY_ASSET_TYPE',
            20 => 'INSTANCES:VIEW',
            21 => 'INSTANCES:FULL_PERMISSIONS_IN_INSTANCE',
            22 => 'USERS:EDIT:NOTIFICATION_SETTINGS',
            23 => 'INSTANCES:DELETE',
            24 => 'INSTANCES:PERMANENTLY_DELETE',            
        ];
        $legacyInstanceActionsLookupTable = [
            2 => 'BUSINESS:USERS:VIEW:LIST',
            3 => 'BUSINESS:USERS:CREATE:ADD_USER_BY_EMAIL',
            5 => 'BUSINESS:USERS:DELETE:REMOVE_FORM_BUSINESS',
            6 => 'BUSINESS:USERS:EDIT:CHANGE_ROLE',
            11 => 'BUSINESS:ROLES_AND_PERMISSIONS:VIEW',
            12 => 'BUSINESS:ROLES_AND_PERMISSIONS:EDIT',
            13 => 'BUSINESS:USERS:EDIT:ROLES_AND_PERMISSIONS',
            14 => 'BUSINESS:USERS:EDIT:USER_THUMBNAIL',
            16 => 'BUSINESS:ROLES_AND_PERMISSIONS:CREATE',
            17 => 'ASSETS:CREATE',
            18 => 'ASSETS:ASSET_TYPES:CREATE',
            19 => 'ASSETS:DELETE',
            20 => 'PROJECTS:VIEW',
            21 => 'PROJECTS:CREATE',
            22 => 'PROJECTS:EDIT:CLIENT',
            23 => 'PROJECTS:EDIT:LEAD',
            24 => 'PROJECTS:EDIT:DESCRIPTION_AND_SUB_PROJECTS',
            25 => 'PROJECTS:ARCHIVE',
            26 => 'PROJECTS:DELETE',
            27 => 'PROJECTS:EDIT:DATES',
            28 => 'PROJECTS:EDIT:NAME',
            29 => 'PROJECTS:EDIT:STATUS',
            30 => 'PROJECTS:EDIT:ADDRESS',
            31 => 'PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN',
            32 => 'PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_ALL_BUSINESS_ASSETS',
            33 => 'PROJECTS:PROJECT_PAYMENTS:VIEW',
            34 => 'PROJECTS:PROJECT_PAYMENTS:CREATE',
            35 => 'PROJECTS:PROJECT_PAYMENTS:DELETE',
            36 => 'CLIENTS:VIEW',
            37 => 'CLIENTS:CREATE',
            38 => 'ASSETS:MANUFACTURERS:CREATE',
            39 => 'CLIENTS:EDIT',
            40 => 'FINANCE:PAYMENTS_LEDGER:VIEW',
            41 => 'PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMNET_COMMENT',
            42 => 'PROJECTS:PROJECT_ASSETS:EDIT:CUSTOM_PRICE',
            43 => 'PROJECTS:PROJECT_ASSETS:EDIT:DISCOUNT',
            44 => 'PROJECTS:PROJECT_NOTES:EDIT:NOTES',
            45 => 'PROJECTS:PROJECT_NOTES:CREATE:NOTES',
            46 => 'PROJECTS:EDIT:INVOICE_NOTES',
            47 => 'PROJECTS:PROJECT_CREW:VIEW',
            48 => 'PROJECTS:PROJECT_CREW:CREATE',
            49 => 'PROJECTS:PROJECT_CREW:EDIT',
            50 => 'PROJECTS:PROJECT_CREW:VIEW:EMAIL_CREW',
            51 => 'PROJECTS:PROJECT_CREW:EDIT:CREW_RANKS',
            52 => 'BUSINESS:USERS:VIEW:INDIVIDUAL_USER',
            53 => 'PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS',
            54 => 'ASSETS:ASSET_TYPE_FILE_ATTACHMENTS:VIEW',
            55 => 'ASSETS:ASSET_TYPE_FILE_ATTACHMENTS:CREATE',
            56 => 'ASSETS:FILE_ATTACHMENTS:EDIT',
            57 => 'ASSETS:FILE_ATTACHMENTS:DELETE',
            58 => 'ASSETS:ASSET_TYPES:EDIT',
            59 => 'ASSETS:EDIT',
            61 => 'ASSETS:ASSET_FILE_ATTACHMENTS:VIEW',
            62 => 'ASSETS:ASSET_FILE_ATTACHMENTS:CREATE',
            63 => 'MAINTENANCE_JOBS:VIEW',
            67 => 'MAINTENANCE_JOBS:EDIT:JOB_DUE_DATE',
            68 => 'MAINTENANCE_JOBS:EDIT:USER_ASSIGNED_TO_JOB',
            69 => 'MAINTENANCE_JOBS:EDIT:USERS_TAGGED_IN_JOB',
            70 => 'MAINTENANCE_JOBS:EDIT:NAME',
            71 => 'MAINTENANCE_JOBS:EDIT:ADD_MESSAGE_TO_JOB',
            72 => 'MAINTENANCE_JOBS:DELETE',
            73 => 'MAINTENANCE_JOBS:EDIT:STATUS',
            74 => 'MAINTENANCE_JOBS:EDIT:ADD_ASSETS',
            75 => 'MAINTENANCE_JOBS:EDIT',
            76 => 'MAINTENANCE_JOBS:MAINTENANCE_JOBS_FILE_ATTACHMENTS:CREATE',
            77 => 'MAINTENANCE_JOBS:EDIT:JOB_PRIORITY',
            78 => 'MAINTENANCE_JOBS:EDIT:ASSET_FLAGS',
            79 => 'MAINTENANCE_JOBS:EDIT:ASSET_BLOCKS',
            80 => 'BUSINESS:BUSINESS_STATS:VIEW',
            81 => 'BUSINESS:BUSINESS_SETTINGS:VIEW',
            82 => 'ASSETS:EDIT:OVVERRIDES',
            83 => 'BUSINESS:BUSINESS_SETTINGS:EDIT',
            84 => 'ASSETS:ASSET_BARCODES:VIEW',
            85 => 'ASSETS:ASSET_BARCODES:VIEW:SCAN_IN_APP',
            86 => 'ASSETS:ASSET_BARCODES:DELETE',
            87 => 'LOCATIONS:VIEW',
            88 => 'ASSETS:ASSET_BARCODES:EDIT:ASSOCIATE_UNNASOCIATED_BARCODES_WITH_ASSETS',
            89 => 'ASSETS:ASSET_CATEGORIES:VIEW',
            90 => 'ASSETS:ASSET_CATEGORIES:CREATE',
            91 => 'ASSETS:ASSET_CATEGORIES:EDIT',
            92 => 'ASSETS:ASSET_CATEGORIES:DELETE',
            93 => 'ASSETS:ASSET_GROUPS:CREATE',
            94 => 'ASSETS:ASSET_GROUPS:EDIT',
            95 => 'ASSETS:ASSET_GROUPS:DELETE:ASSETS_WITHIN_GROUP',
            96 => 'ASSETS:ASSET_GROUPS:EDIT:ASSETS_WITHIN_GROUP',
            97 => 'ASSETS:ARCHIVE',
            98 => 'LOCATIONS:CREATE',
            99 => 'LOCATIONS:EDIT',
            100 => 'LOCATIONS:LOCATION_FILE_ATTACHMENTS:VIEW',
            101 => 'LOCATIONS:LOCATION_FILE_ATTACHMENTS:CREATE',
            102 => 'PROJECTS:PROJECT_FLIE_ATTACHMENTS:CREATE',
            103 => 'LOCATIONS:LOCATION_BARCODES:VIEW',
            104 => 'PROJECTS:EDIT:PROJECT_TYPE',
            105 => 'PROJECTS:PROJECT_TYPES:VIEW',
            106 => 'PROJECTS:PROJECT_TYPES:CREATE',
            107 => 'PROJECTS:PROJECT_TYPES:EDIT',
            108 => 'PROJECTS:PROJECT_TYPES:DELETE',
            109 => 'BUSINESS:USER_SIGNUP_CODES:VIEW',
            110 => 'BUSINESS:USER_SIGNUP_CODES:CREATE',
            111 => 'BUSINESS:USER_SIGNUP_CODES:EDIT',
            112 => 'BUSINESS:USER_SIGNUP_CODES:DELETE',
            113 => 'TRAINING:VIEW',
            114 => 'TRAINING:VIEW:DRAFT_MODULES',
            115 => 'TRAINING:CREATE',
            116 => 'TRAINING:EDIT',
            117 => 'TRAINING:VIEW:USER_PROGRESS_IN_MODULES',
            118 => 'BUSINESS:USERS:EDIT:ARCHIVE',
            119 => 'TRAINING:EDIT:CERTIFY_USER',
            120 => 'TRAINING:EDIT:REVOKE_USER_CERTIFICATION',
            121 => 'PROJECTS:PROJECT_PAYMENTS:VIEW:FILE_ATTACHMENTS',
            122 => 'PROJECTS:PROJECT_PAYMENTS:CREATE:FILE_ATTACHMENTS',
            123 => 'PROJECTS:PROJECT_CREW:EDIT:CREW_RECRUITMENT',
            124 => 'PROJECTS:PROJECT_CREW:VIEW:VIEW_AND_APPLY_FOR_CREW_ROLES',
            125 => 'CMS:CMS_PAGES:CREATE',
            126 => 'CMS:CMS_PAGES:EDIT',
            127 => 'FILES:FILE_ATTACHMENTS:EDIT:SHARING_SETTINGS',
            128 => 'CMS:CMS_PAGES:VIEW:ACCESS_LOG',
            132 => 'CMS:CMS_PAGES:EDIT:CUSTOM_DASHBOARDS',
            133 => 'BUSINESS:SETTINGS:EDIT:TRUSTED_DOMAINS',
            134 => 'PROJECTS:PROJECT_STATUSES:VIEW',
            135 => 'PROJECTS:PROJECT_STATUSES:CREATE',
            136 => 'PROJECTS:PROJECT_STATUSES:EDIT',
            137 => 'PROJECTS:PROJECT_STATUSES:DELETE',  
        ];
          
        $this->table('actions')->drop()->save();
        $this->table('actionsCategories')->drop()->save();
        $this->table('instanceActions')->drop()->save();
        $this->table('instanceActionsCategories')->drop()->save();

        $this->table('positionsGroups')
            ->renameColumn('positionsGroups_actions', 'tempCol')
            ->save();
        $this->table('positionsGroups')   
            ->addColumn('positionsGroups_actions', 'string', [
                'limit' => 10000,
                'null' => true,
                'after' => 'tempCol'
            ])
            ->update();
        $positionsGroups = $this->fetchAll('SELECT * FROM positionsGroups');   
        foreach ($positionsGroups as $positionsGroup) {
            $positionsGroup['positionsGroups_actions'] = explode(",", $positionsGroup['tempCol']);
            $newPermissions = [];
            foreach ($positionsGroup['positionsGroups_actions'] as $action) {
                if ($action and isset($legacyServerActionsLookupTable[$action])) {
                    $newPermissions[] = $legacyServerActionsLookupTable[$action];
                }
            }
            $this->execute('UPDATE positionsGroups SET positionsGroups_actions = "' . implode(",", $newPermissions) . '" WHERE positionsGroups_id = ' . $positionsGroup['positionsGroups_id']);
        }
        $this->table('positionsGroups')
            ->removeColumn('tempCol')
            ->save();

        $this->table('instancePositions')
            ->renameColumn('instancePositions_actions', 'tempCol')
            ->save();
        $this->table('instancePositions')   
            ->addColumn('instancePositions_actions', 'string', [
                'limit' => 15000,
                'null' => true,
                'after' => 'tempCol'
            ])
            ->update();
        $instancePositionsGroups = $this->fetchAll('SELECT * FROM instancePositions');   
        foreach ($instancePositionsGroups as $positionsGroup) {
            $positionsGroup['instancePositions_actions'] = explode(",", $positionsGroup['tempCol']);
            $newPermissions = [];
            foreach ($positionsGroup['instancePositions_actions'] as $action) {
                if ($action and isset($legacyInstanceActionsLookupTable[$action])) {
                    $newPermissions[] = $legacyInstanceActionsLookupTable[$action];
                }
            }
            $this->execute('UPDATE instancePositions SET instancePositions_actions = "' . implode(",", $newPermissions) . '" WHERE instancePositions_id = ' . $positionsGroup['instancePositions_id']);
        }
        $this->table('instancePositions')
            ->removeColumn('tempCol')
            ->save();

        $this->table('userInstances')
            ->renameColumn('userInstances_extraPermissions', 'tempCol')
            ->save();
        $this->table('userInstances')   
            ->addColumn('userInstances_extraPermissions', 'string', [
                'limit' => 15000,
                'null' => true,
                'after' => 'tempCol'
            ])
            ->update();
        $userExtraPermissions = $this->fetchAll('SELECT * FROM userInstances WHERE tempCol IS NOT NULL');   
        foreach ($userExtraPermissions as $userExtraPermissions) {
            $userExtraPermissions['userInstances_extraPermissions'] = explode(",", $userExtraPermissions['tempCol']);
            $newPermissions = [];
            foreach ($userExtraPermissions['userInstances_extraPermissions'] as $action) {
                if ($action and isset($legacyInstanceActionsLookupTable[$action])) {
                    $newPermissions[] = $legacyInstanceActionsLookupTable[$action];
                }
            }
            $this->execute('UPDATE userInstances SET userInstances_extraPermissions = "' . implode(",", $newPermissions) . '" WHERE userInstances_id = ' . $userExtraPermissions['userInstances_id']);
        }
        $this->table('userInstances')
            ->removeColumn('tempCol')
            ->save();
    }
}
