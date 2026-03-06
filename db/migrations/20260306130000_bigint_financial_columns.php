<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BigintFinancialColumns extends AbstractMigration
{
    /**
     * Change all monetary/rate/value columns from INT to BIGINT to support
     * currencies with large unit values (e.g. HUF) without overflow.
     */
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `assetTypes`
                MODIFY COLUMN `assetTypes_dayRate` BIGINT NOT NULL DEFAULT 0,
                MODIFY COLUMN `assetTypes_weekRate` BIGINT NOT NULL DEFAULT 0,
                MODIFY COLUMN `assetTypes_value` BIGINT NOT NULL DEFAULT 0;

            ALTER TABLE `assets`
                MODIFY COLUMN `assets_dayRate` BIGINT NULL DEFAULT NULL,
                MODIFY COLUMN `assets_weekRate` BIGINT NULL DEFAULT NULL,
                MODIFY COLUMN `assets_value` BIGINT NULL DEFAULT NULL;

            ALTER TABLE `assetsAssignments`
                MODIFY COLUMN `assetsAssignments_customPrice` BIGINT NOT NULL DEFAULT 0;

            ALTER TABLE `payments`
                MODIFY COLUMN `payments_amount` BIGINT NOT NULL DEFAULT 0;

            ALTER TABLE `projectsFinanceCache`
                MODIFY COLUMN `projectsFinanceCache_equipmentSubTotal` BIGINT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_equiptmentDiscounts` BIGINT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_equiptmentTotal` BIGINT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_salesTotal` BIGINT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_staffTotal` BIGINT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_externalHiresTotal` BIGINT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_paymentsReceived` BIGINT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_grandTotal` BIGINT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_value` BIGINT NULL DEFAULT NULL;
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE `assetTypes`
                MODIFY COLUMN `assetTypes_dayRate` INT NOT NULL DEFAULT 0,
                MODIFY COLUMN `assetTypes_weekRate` INT NOT NULL DEFAULT 0,
                MODIFY COLUMN `assetTypes_value` INT NOT NULL DEFAULT 0;

            ALTER TABLE `assets`
                MODIFY COLUMN `assets_dayRate` INT NULL DEFAULT NULL,
                MODIFY COLUMN `assets_weekRate` INT NULL DEFAULT NULL,
                MODIFY COLUMN `assets_value` INT NULL DEFAULT NULL;

            ALTER TABLE `assetsAssignments`
                MODIFY COLUMN `assetsAssignments_customPrice` INT NOT NULL DEFAULT 0;

            ALTER TABLE `payments`
                MODIFY COLUMN `payments_amount` INT NOT NULL DEFAULT 0;

            ALTER TABLE `projectsFinanceCache`
                MODIFY COLUMN `projectsFinanceCache_equipmentSubTotal` INT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_equiptmentDiscounts` INT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_equiptmentTotal` INT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_salesTotal` INT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_staffTotal` INT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_externalHiresTotal` INT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_paymentsReceived` INT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_grandTotal` INT NULL DEFAULT NULL,
                MODIFY COLUMN `projectsFinanceCache_value` INT NULL DEFAULT NULL;
        ");
    }
}
