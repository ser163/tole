<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateItems extends Migrator
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
        // 创建tag表
        $tables = $this->table('items');
        $tables->addColumn('name', 'string', ['limit' => 30])
            ->addColumn('desc', 'string', ['limit' => 120,'null' => true])
            // 建立用户
            ->addColumn('user_id', 'integer')
            // note_id
            ->addColumn('note_id', 'integer')
            //tags
            ->addColumn('tags', 'string', ['limit' => 200,'null' => true])
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated', 'timestamp', ['null' => true])
            ->create();
    }

    public function down()
    {
        $this->table('items')->drop()->save();
    }
}
