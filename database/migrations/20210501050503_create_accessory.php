<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAccessory extends Migrator
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
    public  function up(){
        // 创建用户表
        $tables = $this->table('accessory');
        $tables->addColumn('url', 'string', ['limit' => 500,'null'=>true])
            ->addColumn('path', 'string', ['limit' => 500,'null'=>true])
            ->addColumn('cloud','boolean',['default' => false])
            ->addColumn('file_name', 'string', ['limit' => 250])
            ->addColumn('ext', 'string', ['limit' => 15])
            // type(类型image 0,file 1)
            ->addColumn('type', 'integer', ['default'=>0])
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated', 'timestamp', ['null' => true])
            ->create();
    }
    public  function down(){
        $this->table('accessory')->drop()->save();
    }
}
