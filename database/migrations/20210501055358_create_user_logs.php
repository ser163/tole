<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserLogs extends Migrator
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
        // 创建用户记录表
        $tables = $this->table('user_logs');
        $tables->addColumn('ip', 'string', ['limit' => 129])
            // 建立用户
            ->addColumn('user_id', 'integer')
            // 登录时间
            ->addColumn('login_time', 'datetime')
            // 默认是login。true为登录，false为注销
            ->addColumn('login','boolean',['default' => true])
            ->create();
    }

    public function down()
    {
        $this->table('user_logs')->drop()->save();
    }
}
