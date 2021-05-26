<?php

use think\migration\Seeder;
use think\facade\Db;

class InitUserRole extends Seeder
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
        // 1.新建用户
        $UserData = [
            'username' => 'admin',
            'password' => confusionPassword('admin'),
            'password_salt' => RoundGenerate(),
            'mobile' => '15966873129',
            'email' => 'l3478830@163.com',
            'first_name' => 'Harry',
            'last_name' => 'Liu',
            'enable' => true,
        ];

        $userId = Db::name('user')->insertGetId($UserData);

//        $user = $this->table('user')->insert($UserData)->saveData();

        // 2.新建管理员角色
        $RoleData = [
            'name' => 'admins',
            'full_name' => '管理员',
            'desc' => '管理员是也'
        ];

        $roleId = Db::name('role')->insertGetId($RoleData);

        // 3.建立角色关联
        $userRoleData = [
            'user_id' => $userId,
            'role_id' => $roleId
        ];

        Db::name('user_role_access')->insertGetId($userRoleData);
        // 将admin存入admins
        try {
            // 切记规则添加不能有重复
            $RuleData = [
                // 1.管理员加入管理组
                ['ptype' => 'g','v0'=>'admin','v1'=>'admins'],
                // 2.设置getAllUser接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'user/getAllUser','v2'=>'read'],
                // 3.设置getAllRole接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'user/getAllRole','v2'=>'read'],
                // 4.设置user/addUser接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'user/addUser','v2'=>'read'],
                // 5.设置setUserInfo接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'user/setUserInfo','v2'=>'write'],
                // 6.设置 setUserEnable 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'user/setUserEnable','v2'=>'write'],
                // 7.设置 getUserRoles 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'user/getUserRoles','v2'=>'read'],
                // 8.设置 setUserPassWord 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'user/setUserPassWord','v2'=>'write'],
                // 9.设置 getAllRoles 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'role/getAllRoles','v2'=>'read'],
                // 10.设置 addRoles 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'role/addRoles','v2'=>'read'],
                // 11.设置 editRoles 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'role/editRoles','v2'=>'write'],
                // 12.设置 deleteRoles 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'role/deleteRoles','v2'=>'write'],
                // 13.设置 getRoleInUser 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'role/getRoleInUser','v2'=>'read'],
                // 14.设置 deleteUserOnRoles 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'role/deleteUserOnRoles','v2'=>'write'],
                // 15.设置 getAllUserLogs 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'logs/getAllUserLogs','v2'=>'read'],
                // 16.设置 getAllHistory 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'history/getAllHistory','v2'=>'read'],
                // 17.设置 getAllRules 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'rule/getAllRules','v2'=>'read'],
                // 18.设置 addRules 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'rule/addRules','v2'=>'write'],
                // 19.设置 editRules 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'rule/editRules','v2'=>'write'],
                // 20.设置 deleteRules 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'rule/deleteRules','v2'=>'write'],
                // 21.设置 getTreeData 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'depart/getTreeData','v2'=>'read'],
                // 22.设置 getDepartData 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'depart/getDepartData','v2'=>'read'],
                // 23.设置 getAllDepart 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'depart/getAllDepart','v2'=>'read'],
                // 24.设置 getAllDepart 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'depart/getAllDepart','v2'=>'read'],
                // 25.设置 addDepart 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'depart/addDepart','v2'=>'write'],
                // 26.设置 updateDepart 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'depart/updateDepart','v2'=>'write'],
                // 27.设置 deleteDepart 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'depart/deleteDepart','v2'=>'write'],

                // 28.设置 getAllData 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'cate/getAllData','v2'=>'read'],

                // 29.设置 getUserRoles 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'notes/getUserRoles','v2'=>'read'],
                // 30.设置 addNotes 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'notes/addNotes','v2'=>'write'],
                // 31.设置 getAllNotes 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'notes/getAllNotes','v2'=>'read'],
                // 32.设置 getNotesRoles 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'notes/getNotesRoles','v2'=>'read'],
                // 33.设置 checkPassWord 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'notes/updateNotes','v2'=>'write'],
                // 34.设置 checkPassWord 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'notes/checkPassWord','v2'=>'read'],

                // 35.设置 addItems 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'items/addItems','v2'=>'write'],
                // 36.获取 info 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'home/info','v2'=>'read'],

                // 37.设置 getUserAllInRoles 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'role/getUserAllInRoles','v2'=>'read'],
                // 38.设置 joinRoles 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'role/joinRoles','v2'=>'write'],
                // 39.设置 delete 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'notes/delete','v2'=>'write'],
                // 40.设置 delete 接口权限
                ['ptype' => 'p','v0'=>'admins','v1'=>'items/delete','v2'=>'write'],
            ];
            $this->table('rules')->insert($RuleData)->saveData();
        } catch (\think\Exception $exception) {
            echo $exception->getMessage();
        }
    }
}