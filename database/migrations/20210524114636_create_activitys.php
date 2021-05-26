<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateActivitys extends Migrator
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
        $tables = $this->table('activitys');
        $tables->addColumn('note_id', 'integer')
            ->addColumn('user_id', 'integer')
            // 关联项目表
            ->addColumn('item_id', 'integer')
            // 用户
            ->addColumn('username', 'string',['limit'=>20])
            // 主项目
            ->addColumn('note_name', 'string',['limit'=>30])
            // 附加项目
            ->addColumn('item_name', 'string',['limit'=>30])
            // 创建时间
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();
    }

    public  function down()
    {
        $this->table('activitys')->drop()->save();
    }
}
