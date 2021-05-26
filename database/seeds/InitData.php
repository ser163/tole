<?php

use think\migration\Seeder;
use think\facade\Db;

class InitData extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        // 添加默认组织总公司
        $DepartData = [
            'name' => '总公司',
        ];

        $this->table('organization')->insert($DepartData)->saveData();
        
        // 添加默认类别
        $CategoryData = [
            ['name' => '网站密码'],
            ['name' => '系统密码'],
            ['name' => '手机密码'],
            ['name' => '门禁密码'],
            ['name' => '设备密码'],
        ];

        $this->table('category')->insert($CategoryData)->saveData();
    }
}