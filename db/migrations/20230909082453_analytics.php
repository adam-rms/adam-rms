<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Analytics extends AbstractMigration
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
        
        $this->execute('create table analyticsEvents
            (
                analyticsEvents_id        int auto_increment,
                analyticsEvents_timetsamp timestamp    not null,
                users_userid              int          not null,
                adminUser_users_userid    int          null,
                authTokens_id             int          not null,
                instances_id              int          null,
                analyticsEvents_path      varchar(500) not null,
                analyticsEvents_action    VARCHAR(500) not null,
                analyticsEvents_payload   text         null,
                constraint analyticsEvents_pk
                    primary key (analyticsEvents_id),
                constraint analyticsEvents_users_users_userid_fk
                    foreign key (users_userid) references users (users_userid)
                        on update cascade on delete cascade,
                constraint analyticsEvents_admin_users_users_userid_fk
                    foreign key (adminUser_users_userid) references users (users_userid)
                        on update cascade on delete cascade,
                constraint analyticsEvents_instances_instances_id_fk
                    foreign key (instances_id) references instances (instances_id)
                        on update set null on delete set null,
                constraint analyticsEvents_authTokens_authTokens_id_fk
                    foreign key (authTokens_id) references authTokens (authTokens_id)
                        on update cascade on delete cascade
            );
        ');
    }
}
