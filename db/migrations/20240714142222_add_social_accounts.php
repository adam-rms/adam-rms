<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSocialAccounts extends AbstractMigration
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
        if (!$this->isMigratingUp()) {
            // if rolling back, add back the users_oauth_microsoftid and users_oauth_googleid columns to the users table
            $table = $this->table('users');
            $table->addColumn('users_oauth_googleid', 'string', [
                'null' => true,
                'default' => null,
                'after' => 'users_oauth_microsoftid'
            ])->update();
            $table->addColumn('users_oauth_microsoftid', 'string', [
                'null' => true,
                'default' => null,
                'after' => 'users_oauth_googleid'
            ])->update();


            // go through social_accounts table and get all rows with provider "microsoft" and insert them into the users table with column users_oauth_microsoftid
            $microsoftAccounts = $this->fetchAll('SELECT user_id, uid FROM social_accounts WHERE provider = "microsoft"');
            $stmt = $this->query('UPDATE users SET users_oauth_microsoftid = :uid WHERE id = :user_id');
            foreach ($microsoftAccounts as $account) {
                $stmt->execute($account);
            }

            // do the same for provider "google"
            $googleAccounts = $this->fetchAll('SELECT user_id, uid FROM social_accounts WHERE provider = "google"');
            $stmt = $this->query('UPDATE users SET users_oauth_googleid = :uid WHERE id = :user_id');
            foreach ($googleAccounts as $account) {
                $stmt->execute($account);
            }
        }

        // create new table "social account", with users foreign key column, string provider column, and string uid column. Primary key should be user_id and provider.
        $this->table('social_accounts')
            ->addColumn('user_id', 'integer')
            ->addColumn('provider', 'string')
            ->addColumn('uid', 'string')
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addIndex(['user_id', 'provider'], ['unique' => true])
            ->create();

        if ($this->isMigratingUp()) {
            // go through users table and get all users with a users_oauth_microsoftid, and insert them into the social_accounts table with provider "microsoft"
            $users = $this->fetchAll('SELECT id, users_oauth_microsoftid FROM users WHERE users_oauth_microsoftid IS NOT NULL');
            foreach ($users as $user) {
                $this->table('social_accounts')
                    ->insert([
                        'user_id' => $user['id'],
                        'provider' => 'microsoft',
                        'uid' => $user['users_oauth_microsoftid']
                    ])
                    ->save();
            }

            // do the same for users_oauth_googleid
            $users = $this->fetchAll('SELECT id, users_oauth_googleid FROM users WHERE users_oauth_googleid IS NOT NULL');
            foreach ($users as $user) {
                $this->table('social_accounts')
                    ->insert([
                        'user_id' => $user['id'],
                        'provider' => 'google',
                        'uid' => $user['users_oauth_googleid']
                    ])
                    ->save();
            }

            // remove users_oauth_microsoftid and users_oauth_googleid columns from users table
            $this->table('users')
                ->removeColumn('users_oauth_microsoftid')
                ->removeColumn('users_oauth_googleid')
                ->save();
        }
    }
}
