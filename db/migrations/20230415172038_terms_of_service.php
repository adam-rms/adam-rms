<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TermsOfService extends AbstractMigration
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
        $this->table('users')
        ->addColumn('users_termsAccepted', 'timestamp', [
            'null' => true,
            'after' => 'users_changepass'
        ])
        ->save();

        $this->execute('UPDATE users SET users_termsAccepted = users_created WHERE users_oauth_googleid IS NULL'); //For users that don't have a google link we can prove they accepted the terms at sign up
    }
}
