<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RenameCloudflareImageTransformConfig extends AbstractMigration
{
    public function up(): void
    {
        // Strip the /cdn-cgi/image/onerror=redirect path from existing values,
        // since the code now appends this automatically.
        $this->execute(
            "UPDATE `config`
             SET `config_key` = 'CLOUDFLARE_IMAGE_TRANSFORM_DOMAIN',
                 `config_value` = TRIM(TRAILING '/' FROM REPLACE(`config_value`, '/cdn-cgi/image/onerror=redirect', ''))
             WHERE `config_key` = 'CLOUDFLARE_IMAGE_TRANSFORM_URL'"
        );
    }

    public function down(): void
    {
        // Re-append the path portion when reverting
        $this->execute(
            "UPDATE `config`
             SET `config_key` = 'CLOUDFLARE_IMAGE_TRANSFORM_URL',
                 `config_value` = CONCAT(TRIM(TRAILING '/' FROM `config_value`), '/cdn-cgi/image/onerror=redirect')
             WHERE `config_key` = 'CLOUDFLARE_IMAGE_TRANSFORM_DOMAIN'"
        );
    }
}
