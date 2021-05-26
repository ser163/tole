<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateNotes extends Migrator
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
        // 创建notes
        $tables = $this->table('notes');
        $tables->addColumn('name', 'string', ['limit' => 30])
            ->addColumn('desc', 'string', ['limit' => 120, 'null' => true])
            // 建立用户
            ->addColumn('user_id', 'integer')
            // 此条的所有者
            ->addColumn('owner', 'integer')
            // 是否需要密码才能显示
            ->addColumn('protect', 'boolean', ['default' => false])
            // 存储要显示的密码
            ->addColumn('show_word', 'string', ['limit' => 20, 'null' => true])
            // 存储显示密码加密的盐值
            ->addColumn('show_salt', 'string', ['limit' => 20])
            // 密码加密提示
            ->addColumn('tip', 'string', ['limit' => 30, 'null' => true])
            // 是否是个人类型
            ->addColumn('flag', 'boolean', ['default' => true])
            // 关联类别
            ->addColumn('category_id', 'integer')
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated', 'timestamp', ['null' => true])
            ->addIndex(['name'], ['unique' => true])
            ->create();
    }

    public function down()
    {
        $this->table('notes')->drop()->save();
    }
}
