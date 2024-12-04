<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSocialAccounts extends AbstractMigration
{
    public function down(): void
    {
        // if rolling back, add back the users_oauth_microsoftid and users_oauth_googleid columns to the users table
        $table = $this->table('users');
        $table->addColumn('users_oauth_googleid', 'string', [
            'null' => true,
            'default' => null,
            'after' => 'users_assetGroupsWatching'
        ])->update();
        $table->addColumn('users_oauth_microsoftid', 'string', [
            'null' => true,
            'default' => null,
            'after' => 'users_oauth_googleid'
        ])->update();

        // update users table with microsoft accounts
        $this->execute('UPDATE users u
                        JOIN userSocialAccounts usa ON u.users_userid = usa.users_userid
                        SET u.users_oauth_microsoftid = usa.userSocialAccounts_uid
                        WHERE usa.userSocialAccounts_provider = "microsoft"');

        // update users table with google accounts
        $this->execute('UPDATE users u
                        JOIN userSocialAccounts usa ON u.users_userid = usa.users_userid
                        SET u.users_oauth_googleid = usa.userSocialAccounts_uid
                        WHERE usa.userSocialAccounts_provider = "google"');

        $this->table('userSocialAccounts')->drop()->save();
    }

    public function up(): void
    {
        $this->table('userSocialAccounts', ['id' => false, 'primary_key' => ['users_userid', 'userSocialAccounts_provider']])
            ->addColumn('users_userid', 'integer', ['null' => false])
            ->addColumn('userSocialAccounts_provider', 'string', ['null' => false])
            ->addColumn('userSocialAccounts_uid', 'string', ['null' => false])
            ->addForeignKey('users_userid', 'users', 'users_userid', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addIndex(['users_userid', 'userSocialAccounts_provider'], ['unique' => true])
            ->create();

        // go through users table and get all rows with users_oauth_microsoftid and insert them into the social_accounts table with userSocialAccounts_provider "microsoft"
        $this->execute('INSERT INTO userSocialAccounts (users_userid, userSocialAccounts_provider, userSocialAccounts_uid)
                        SELECT users_userid, "microsoft", users_oauth_microsoftid
                        FROM users
                        WHERE users_oauth_microsoftid IS NOT NULL
        ');

        // do the same for userSocialAccounts_provider "google"
        $this->execute(
            'INSERT INTO userSocialAccounts (users_userid, userSocialAccounts_provider, userSocialAccounts_uid)
                        SELECT users_userid, "google", users_oauth_googleid
                        FROM users
                        WHERE users_oauth_googleid IS NOT NULL'
        );

        // remove users_oauth_microsoftid and users_oauth_googleid columns from users table
        $this->table('users')
            ->removeColumn('users_oauth_microsoftid')
            ->removeColumn('users_oauth_googleid')
            ->save();
    }
}
