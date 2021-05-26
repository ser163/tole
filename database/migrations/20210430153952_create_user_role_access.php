<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserRoleAccess extends Migrator
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
        // 创建用户角色关联表
        $role = $this->table('user_role_access',['id' => false, 'primary_key' => ['user_id', 'role_id']]);
        $role->addColumn('user_id', 'integer')
            ->addColumn('role_id','integer')
            ->create();
    }
    public  function down(){
        $this->table('user_role_access')->drop()->save();
    }
}
