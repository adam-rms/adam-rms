<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DayRateMultipliers extends AbstractMigration
{
    /**
     * Lets a business charge a multiple of the day rate for a given number of days (e.g. 1.5x the day rate for a 2 day hire).
     * Stored as JSON: [{"from": 2, "to": 2, "multiplier": "1.5"}, ...]. NULL means none are set, so days are charged one day rate each.
     */
    public function change(): void
    {
        $this->table('instances')
            ->addColumn('instances_dayRateMultipliers', 'text', ['null' => true, 'default' => null])
            ->update();
    }
}
