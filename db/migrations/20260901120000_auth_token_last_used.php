<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuthTokenLastUsed extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Adds the timestamp that drives sliding session expiry. Logins used to expire a fixed
     * 12 hours after they were created; they now expire after a configurable period of
     * inactivity instead, measured from this column.
     */
    public function change(): void
    {
        $this->table('authTokens')
            ->addColumn('authTokens_lastUsed', 'timestamp', [
                'null' => true,
                'default' => null,
                'comment' => 'Last time this token authenticated a request - drives sliding session expiry. authTokens_created stays the login time.',
                'after' => 'authTokens_created'
            ])
            ->save();

        //Judge existing logins from when they were created, rather than treating them as never used
        $this->execute('UPDATE authTokens SET authTokens_lastUsed = authTokens_created WHERE authTokens_lastUsed IS NULL');
    }
}
