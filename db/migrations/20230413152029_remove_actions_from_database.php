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
            2 => 'USERS:CREATE',
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
            21 => 'INSTANCES::FULL_PERMISSIONS_IN_INSTANCE',
            22 => 'USERS:EDIT:NOTIFICATION_SETTINGS',
            23 => 'INSTANCES:DELETE',
            24 => 'INSTANCES:PERMANENTLY_DELETE',            
        ];
        $legacyInstanceActionsLookupTable = [
            1 => 'ASSETS:CREATE',
            2 => 'ASSETS:ASSET_TYPES:CREATE',
            3 => 'ASSETS:DELETE',
            4 => 'ASSETS:MANUFACTURERS:CREATE',
            5 => 'ASSETS:ASSET_TYPES:EDIT',
            6 => 'ASSETS:EDIT',
            7 => 'ASSETS:EDIT:OVVERRIDES',
            8 => 'ASSETS:ASSET_BARCODES:VIEW',
            9 => 'ASSETS:ASSET_BARCODES:DELETE',
            10 => 'ASSETS:ARCHIVE',
            11 => 'CLIENTS:VIEW',
            12 => 'CLIENTS:CREATE',
            13 => 'CLIENTS:EDIT',
            14 => 'PROJECTS:VIEW',
            15 => 'PROJECTS:CREATE',
            16 => 'PROJECTS:EDIT:CLIENT',
            17 => 'PROJECTS:EDIT:LEAD',
            18 => 'PROJECTS:EDIT:DESCRIPTION_AND_SUB_PROJECTS',
            19 => 'PROJECTS:ARCHIVE',
            20 => 'PROJECTS:DELETE',
            21 => 'PROJECTS:EDIT:DATES',
            22 => 'PROJECTS:EDIT:NAME',
            23 => 'PROJECTS:EDIT:STATUS',
            24 => 'PROJECTS:EDIT:ADDRESS',
            25 => 'PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN',
            26 => 'PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_ALL_BUSINESS_ASSETS',
            27 => 'PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMNET_COMMENT',
            28 => 'PROJECTS:PROJECT_NOTES:EDIT:NOTES',
            29 => 'PROJECTS:PROJECT_NOTES:CREATE:NOTES',
            30 => 'PROJECTS:EDIT:INVOICE_NOTES',
            31 => 'PROJECTS:PROJECT_CREW:VIEW:VIEW_AND_APPLY_FOR_CREW_ROLES',
            32 => 'PROJECTS:EDIT:PROJECT_TYPE',
            33 => 'PROJECTS:PROJECT_TYPES:VIEW',
            34 => 'PROJECTS:PROJECT_TYPES:CREATE',
            35 => 'PROJECTS:PROJECT_TYPES:EDIT',
            36 => 'PROJECTS:PROJECT_TYPES:DELETE',
            37 => 'PROJECTS:PROJECT_PAYMENTS:VIEW',
            38 => 'PROJECTS:PROJECT_PAYMENTS:CREATE',
            39 => 'PROJECTS:PROJECT_PAYMENTS:DELETE',
            40 => 'FINANCE:PAYMENTS_LEDGER:VIEW',
            41 => 'PROJECTS:PROJECT_ASSETS:EDIT:CUSTOM_PRICE',
            42 => 'PROJECTS:PROJECT_ASSETS:EDIT:DISCOUNT',
            43 => 'PROJECTS:PROJECT_CREW:VIEW',
            44 => 'PROJECTS:PROJECT_CREW:CREATE',
            45 => 'PROJECTS:PROJECT_CREW:EDIT',
            46 => 'PROJECTS:PROJECT_CREW:VIEW:EMAIL_CREW',
            47 => 'PROJECTS:PROJECT_CREW:EDIT:CREW_RANKS',
            48 => 'PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS',
            49 => 'PROJECTS:PROJECT_PAYMENTS:VIEW:FILE_ATTACHMENTS',
            50 => 'PROJECTS:PROJECT_PAYMENTS:CREATE:FILE_ATTACHMENTS',
            51 => 'PROJECTS:PROJECT_CREW:EDIT:CREW_RECRUITMENT',
            52 => 'ASSETS:ASSET_TYPE_FILE_ATTACHMENTS:VIEW',
            53 => 'ASSETS:ASSET_TYPE_FILE_ATTACHMENTS:CREATE',
            54 => 'ASSETS:FILE_ATTACHMENTS:EDIT',
            55 => 'ASSETS:FILE_ATTACHMENTS:DELETE',
            56 => 'ASSETS:ASSET_FILE_ATTACHMENTS:VIEW',
            57 => 'ASSETS:ASSET_FILE_ATTACHMENTS:CREATE',
            58 => 'LOCATIONS:LOCATION_FILE_ATTACHMENTS:VIEW',
            59 => 'LOCATIONS:LOCATION_FILE_ATTACHMENTS:CREATE',
            60 => 'PROJECTS:PROJECT_FLIE_ATTACHMENTS:CREATE',
            61 => 'FILES:FILE_ATTACHMENTS:EDIT:SHARING_SETTINGS',
            62 => 'MAINTENANCE_JOBS:VIEW',
            63 => 'MAINTENANCE_JOBS:EDIT:JOB_DUE_DATE',
            64 => 'MAINTENANCE_JOBS:EDIT:USER_ASSIGNED_TO_JOB',
            65 => 'MAINTENANCE_JOBS:EDIT:USERS_TAGGED_IN_JOB',
            66 => 'MAINTENANCE_JOBS:EDIT:NAME',
            67 => 'MAINTENANCE_JOBS:EDIT:ADD_MESSAGE_TO_JOB',
            68 => 'MAINTENANCE_JOBS:DELETE',
            69 => 'MAINTENANCE_JOBS:EDIT:STATUS',
            70 => 'MAINTENANCE_JOBS:EDIT:ADD_ASSETS',
            71 => 'MAINTENANCE_JOBS:EDIT',
            72 => 'MAINTENANCE_JOBS:MAINTENANCE_JOBS_FILE_ATTACHMENTS:CREATE',
            73 => 'MAINTENANCE_JOBS:EDIT:JOB_PRIORITY',
            74 => 'MAINTENANCE_JOBS:EDIT:ASSET_FLAGS',
            75 => 'MAINTENANCE_JOBS:EDIT:ASSET_BLOCKS',
            76 => 'BUSINESS:BUSINESS_STATS:VIEW',
            77 => 'BUSINESS:BUSINESS_SETTINGS:VIEW',
            78 => 'BUSINESS:BUSINESS_SETTINGS:EDIT',
            79 => 'ASSETS:ASSET_CATEGORIES:VIEW',
            80 => 'ASSETS:ASSET_CATEGORIES:CREATE',
            81 => 'ASSETS:ASSET_CATEGORIES:EDIT',
            82 => 'ASSETS:ASSET_CATEGORIES:DELETE',
            83 => 'CMS:CMS_PAGES:CREATE',
            84 => 'CMS:CMS_PAGES:EDIT',
            85 => 'CMS:CMS_PAGES:VIEW:ACCESS_LOG',
            86 => 'CMS:CMS_PAGES:EDIT:CUSTOM_DASHBOARDS',
            87 => 'TRAINING:VIEW',
            88 => 'TRAINING:VIEW:DRAFT_MODULES',
            89 => 'TRAINING:CREATE',
            90 => 'TRAINING:EDIT',
            91 => 'TRAINING:VIEW:USER_PROGRESS_IN_MODULES',
            92 => 'TRAINING:EDIT:CERTIFY_USER',
            93 => 'TRAINING:EDIT:REVOKE_USER_CERTIFICATION',
            94 => 'LOCATIONS:VIEW',
            95 => 'LOCATIONS:CREATE',
            96 => 'LOCATIONS:EDIT',
            97 => 'LOCATIONS:LOCATION_BARCODES:VIEW',
            98 => 'ASSETS:ASSET_GROUPS:CREATE',
            99 => 'ASSETS:ASSET_GROUPS:EDIT',
            100 => 'ASSETS:ASSET_GROUPS:DELETE:ASSETS_WITHIN_GROUP',
            101 => 'ASSETS:ASSET_GROUPS:EDIT:ASSETS_WITHIN_GROUP',
            102 => 'ASSETS:ASSET_BARCODES:VIEW:SCAN_IN_APP',
            103 => 'ASSETS:ASSET_BARCODES:EDIT:ASSOCIATE_UNNASOCIATED_BARCODES_WITH_ASSETS',
            104 => 'BUSINESS:USERS:VIEW:LIST',
            105 => 'BUSINESS:USERS:CREATE:ADD_USER_BY_EMAIL',
            106 => 'BUSINESS:USERS:DELETE:REMOVE_FORM_BUSINESS',
            107 => 'BUSINESS:USERS:EDIT:CHANGE_ROLE',
            108 => 'BUSINESS:USERS:EDIT:USER_THUMBNAIL',
            110 => 'BUSINESS:USERS:VIEW:INDIVIDUAL_USER',
            111 => 'BUSINESS:USER_SIGNUP_CODES:VIEW',
            112 => 'BUSINESS:USER_SIGNUP_CODES:CREATE',
            113 => 'BUSINESS:USER_SIGNUP_CODES:EDIT',
            114 => 'BUSINESS:USER_SIGNUP_CODES:DELETE',
            115 => 'BUSINESS:USERS:EDIT:ARCHIVE',
            116 => 'BUSINESS:SETTINGS:EDIT:TRUSTED_DOMAINS',
            117 => 'BUSINESS:ROLES_AND_PERMISSIONS:VIEW',
            118 => 'BUSINESS:ROLES_AND_PERMISSIONS:EDIT',
            119 => 'BUSINESS:USERS:EDIT:ROLES_AND_PERMISSIONS',
            120 => 'BUSINESS:ROLES_AND_PERMISSIONS:CREATE',
            121 => 'PROJECTS:PROJECT_STATUSES:VIEW',
            122 => 'PROJECTS:PROJECT_STATUSES:CREATE',
            123 => 'PROJECTS:PROJECT_STATUSES:EDIT',
            124 => 'PROJECTS:PROJECT_STATUSES:DELETE',
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
