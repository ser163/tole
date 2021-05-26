<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateGoods extends Migrator
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
        // 创建密码仓库表
        $tables = $this->table('goods');
        // 类型（0：text 1: password 2：url 3：ip 4.file 5.image ）
        $tables->addColumn('type', 'integer', ['default' => 20])
            // 项目名称
            ->addColumn('name', 'string', ['limit' => 100])
            // 值
            ->addColumn('value', 'string', ['limit' => 500])
            // 关联项目表
            ->addColumn('item_id', 'integer')
            // 序号在项目中的排列顺序
            ->addColumn('seq', 'integer',['default' => 0])
            // 加密盐值
            ->addColumn('salt', 'string', ['limit' => 20])
            // 是否已经上传
            ->addColumn('up', 'boolean', ['default' => false])
            // 后缀名,可以为非空
            ->addColumn('ext', 'string', ['limit' => 15,'null' => true])
            // 关联附件表
            ->addColumn('accessory_id', 'integer',['null' => true])
            // 过期时间
            ->addColumn('expiration', 'timestamp', ['null' => true])
            // 创建时间
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            // 更新时间
            ->addColumn('updated', 'timestamp', ['null' => true])
            ->create();
    }

    public  function down()
    {
        $this->table('goods')->drop()->save();
    }
}
