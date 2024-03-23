<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ConfigTable extends AbstractMigration
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
        $this->execute("
            create table config
            (
                config_key   VARCHAR(100)      not null,
                config_value TEXT default null null,
                constraint config_pk
                    primary key (config_key)
            );
            create unique index config_config_key_uindex
                on config (config_key);        
        ");
    }
}
