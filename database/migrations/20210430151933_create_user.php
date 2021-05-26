<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUser extends Migrator
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
        $users = $this->table('user');
        $users->addColumn('username', 'string', ['limit' => 20])
            ->addColumn('mobile', 'string', ['limit' => 11])
            ->addColumn('password', 'string', ['limit' => 40])
            ->addColumn('password_salt', 'string', ['limit' => 40])
            ->addColumn('email', 'string', ['limit' => 100])
            ->addColumn('first_name', 'string', ['limit' => 30])
            ->addColumn('last_name', 'string', ['limit' => 30])
            // 禁用的账号
            ->addColumn('enable','boolean',['default' => true])
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated', 'timestamp', ['null' => true])
            ->addIndex(['username'], ['unique' => true])
            ->addIndex(['email'], ['unique' => true])
            ->addIndex(['mobile'], ['unique' => true])
            ->create();
    }
    public  function down(){
        $this->table('user')->drop()->save();
    }
}
