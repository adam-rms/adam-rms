<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class FixCollation extends AbstractMigration
{
    public function change(): void
    {
        $this->execute("
            START TRANSACTION;
            ALTER TABLE `assetCategories` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assetTypes` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assetsAssignments` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `auditLog` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `authTokens` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `clients` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `crewAssignments` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `emailSent` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `emailVerificationCodes` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instancePositions` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `loginAttempts` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `maintenanceJobs` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `maintenanceJobsMessages` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `maintenanceJobsStatuses` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `manufacturers` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `passwordResetCodes` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `payments` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `positions` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `positionsGroups` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projects` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projectsNotes` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projectsStatuses` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `s3files` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `userInstances` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `userPositions` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

            ALTER TABLE `assetCategories` MODIFY COLUMN `assetCategories_fontAwesome` varchar(100) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assetCategories` MODIFY COLUMN `assetCategories_name` varchar(200) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assetTypes` MODIFY COLUMN `assetTypes_definableFields` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assetTypes` MODIFY COLUMN `assetTypes_description` varchar(1000) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assetTypes` MODIFY COLUMN `assetTypes_name` varchar(500) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assetTypes` MODIFY COLUMN `assetTypes_productLink` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `asset_definableFields_1` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `asset_definableFields_10` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `asset_definableFields_2` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `asset_definableFields_3` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `asset_definableFields_4` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `asset_definableFields_5` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `asset_definableFields_6` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `asset_definableFields_7` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `asset_definableFields_8` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `asset_definableFields_9` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `assets_archived` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `assets_assetGroups` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `assets_notes` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assets` MODIFY COLUMN `assets_tag` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `assetsAssignments` MODIFY COLUMN `assetsAssignments_comment` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `auditLog` MODIFY COLUMN `auditLog_actionData` longtext NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `auditLog` MODIFY COLUMN `auditLog_actionTable` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `auditLog` MODIFY COLUMN `auditLog_actionType` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `authTokens` MODIFY COLUMN `authTokens_deviceType` varchar(1000) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `authTokens` MODIFY COLUMN `authTokens_ipAddress` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `authTokens` MODIFY COLUMN `authTokens_token` varchar(500) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `authTokens` MODIFY COLUMN `authTokens_type` varchar(100) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `clients` MODIFY COLUMN `clients_address` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `clients` MODIFY COLUMN `clients_email` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `clients` MODIFY COLUMN `clients_name` varchar(500) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `clients` MODIFY COLUMN `clients_notes` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `clients` MODIFY COLUMN `clients_phone` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `clients` MODIFY COLUMN `clients_website` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `crewAssignments` MODIFY COLUMN `crewAssignments_comment` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `crewAssignments` MODIFY COLUMN `crewAssignments_personName` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `crewAssignments` MODIFY COLUMN `crewAssignments_role` varchar(500) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `emailSent` MODIFY COLUMN `emailSent_fromEmail` varchar(200) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `emailSent` MODIFY COLUMN `emailSent_fromName` varchar(200) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `emailSent` MODIFY COLUMN `emailSent_html` longtext NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `emailSent` MODIFY COLUMN `emailSent_subject` varchar(255) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `emailSent` MODIFY COLUMN `emailSent_toEmail` varchar(200) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `emailSent` MODIFY COLUMN `emailSent_toName` varchar(200) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `emailVerificationCodes` MODIFY COLUMN `emailVerificationCodes_code` varchar(1000) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instancePositions` MODIFY COLUMN `instancePositions_actions` varchar(15000) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instancePositions` MODIFY COLUMN `instancePositions_displayName` varchar(500) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_address` varchar(1000) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_cableColours` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_calendarConfig` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_calendarHash` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_config_currency` varchar(200) DEFAULT 'GBP' NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_email` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_name` varchar(200) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_phone` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_planName` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_planStripeCustomerId` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_publicConfig` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_quoteTerms` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_serverNotes` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_suspendedReason` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_suspendedReasonType` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_termsAndPayment` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_trustedDomains` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_website` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `instances` MODIFY COLUMN `instances_weekStartDates` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `loginAttempts` MODIFY COLUMN `loginAttempts_ip` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `loginAttempts` MODIFY COLUMN `loginAttempts_textEntered` varchar(500) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `maintenanceJobs` MODIFY COLUMN `maintenanceJobs_assets` varchar(500) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `maintenanceJobs` MODIFY COLUMN `maintenanceJobs_faultDescription` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `maintenanceJobs` MODIFY COLUMN `maintenanceJobs_title` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `maintenanceJobs` MODIFY COLUMN `maintenanceJobs_user_tagged` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `maintenanceJobsMessages` MODIFY COLUMN `maintenanceJobsMessages_text` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `maintenanceJobsStatuses` MODIFY COLUMN `maintenanceJobsStatuses_name` varchar(200) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `manufacturers` MODIFY COLUMN `manufacturers_internalAdamRMSNote` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `manufacturers` MODIFY COLUMN `manufacturers_name` varchar(500) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `manufacturers` MODIFY COLUMN `manufacturers_notes` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `manufacturers` MODIFY COLUMN `manufacturers_website` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `passwordResetCodes` MODIFY COLUMN `passwordResetCodes_code` varchar(1000) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `payments` MODIFY COLUMN `payments_comment` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `payments` MODIFY COLUMN `payments_method` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `payments` MODIFY COLUMN `payments_reference` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `payments` MODIFY COLUMN `payments_supplier` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `positions` MODIFY COLUMN `positions_displayName` varchar(255) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `positions` MODIFY COLUMN `positions_positionsGroups` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `positionsGroups` MODIFY COLUMN `positionsGroups_actions` varchar(10000) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `positionsGroups` MODIFY COLUMN `positionsGroups_name` varchar(255) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projects` MODIFY COLUMN `projects_description` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projects` MODIFY COLUMN `projects_invoiceNotes` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projects` MODIFY COLUMN `projects_name` varchar(500) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projectsNotes` MODIFY COLUMN `projectsNotes_text` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projectsNotes` MODIFY COLUMN `projectsNotes_title` varchar(200) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projectsStatuses` MODIFY COLUMN `projectsStatuses_backgroundColour` varchar(200) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projectsStatuses` MODIFY COLUMN `projectsStatuses_description` varchar(9000) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projectsStatuses` MODIFY COLUMN `projectsStatuses_fontAwesome` varchar(100) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projectsStatuses` MODIFY COLUMN `projectsStatuses_foregroundColour` varchar(200) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `projectsStatuses` MODIFY COLUMN `projectsStatuses_name` varchar(200) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `s3files` MODIFY COLUMN `s3files_extension` varchar(255) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `s3files` MODIFY COLUMN `s3files_filename` varchar(255) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `s3files` MODIFY COLUMN `s3files_name` varchar(1000) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `s3files` MODIFY COLUMN `s3files_original_name` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `s3files` MODIFY COLUMN `s3files_path` varchar(255) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `s3files` MODIFY COLUMN `s3files_shareKey` varchar(255) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `userInstances` MODIFY COLUMN `userInstances_extraPermissions` varchar(15000) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `userInstances` MODIFY COLUMN `userInstances_label` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `userPositions` MODIFY COLUMN `userPositions_displayName` varchar(255) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `userPositions` MODIFY COLUMN `userPositions_extraPermissions` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_assetGroupsWatching` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_calendarHash` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_email` varchar(257) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_hash` varchar(255) NOT NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_name1` varchar(100) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_name2` varchar(100) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_notes` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_notificationSettings` text NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_oauth_googleid` varchar(255) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_oauth_microsoftid` varchar(255) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_password` varchar(150) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_salty1` varchar(30) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_salty2` varchar(50) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_social_facebook` varchar(100) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_social_instagram` varchar(100) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_social_linkedin` varchar(100) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_social_snapchat` varchar(100) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_social_twitter` varchar(100) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_username` varchar(200) NULL  COLLATE utf8mb4_0900_ai_ci;
            ALTER TABLE `users` MODIFY COLUMN `users_widgets` varchar(500) NULL  COLLATE utf8mb4_0900_ai_ci;
            COMMIT;
        ");
    }
}
