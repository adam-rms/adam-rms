<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

final class AddFileShareAction extends AbstractMigration
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
        $singleRow = [
            'instanceActions_id'    => 127,
            'instanceActions_name'  => 'Manage a file\'s sharing status',
            'instanceActionsCategories_id' => 7,
            'instanceActions_dependent' => null,
            'instanceActions_incompatible' => null
        ];
        $table = $this->table('instanceActions');
        $table->insert($singleRow);
        $table->saveData();

        $table = $this->table('s3files');
        $table->addColumn('s3files_shareKey', 'string', ['after' => 's3files_meta_public',"null"=> true, 'default' => Literal::from('NULL')])
            ->update();
    }
}
