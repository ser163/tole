<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateRecords extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        // 创建活动表
        $tables = $this->table('records');
        $tables->addColumn('visit', 'integer')
            ->addColumn('owner', 'integer')
            // 关联项目表
            ->addColumn('item_id', 'integer')
            // 创建时间
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();
    }

    public  function down()
    {
        $this->table('records')->drop()->save();
    }
}
