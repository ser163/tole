<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateHistory extends Migrator
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
        // 创建操作记录表
        $tables = $this->table('history');
        // 用户动作
        $tables->addColumn('action', 'string', ['limit' => 20])
            ->addColumn('ip', 'string', ['limit' => 129])
            // 建立用户
            ->addColumn('user_id', 'integer')
            // 登录时间
            ->addColumn('operation_time', 'datetime')
            // 操作详情
            ->addColumn('desc', 'string',['limit'=>500,'null' => true])
            ->create();
    }

    public function down()
    {
        $this->table('history')->drop()->save();
    }
}
