<?php
declare (strict_types=1);

namespace app\api\controller;

use app\ExController;
use app\model\Notes;
use app\model\Role;
use tauthz\facade\Enforcer;
use think\db\exception\PDOException;
use think\facade\Db;
use think\facade\Log;
use think\response\Json;
use app\model\User;
use DateTime;

class NotesController extends ExController
{
    /**
     * 获取详情
     */
    public function index()
    {
        $size = input('size');
        $current = input('current');
        $seach = input('seach');
        // 0 为个人 1 团队 为全部
        $flag = input('flag');
        $name = input('name');

        $user = $this->getTokenUser();
        if (!$user) {
            return $this->retSucceedInfo('用户未找到', 2);
        }
        // 查找用户的角色id
        $userRoleList = $user->roles()->column('id');
        // 查找角色id对应的noteId。
        $findNotesId = Db::table('notes_role_access')
            ->where('role_id', 'IN', $userRoleList)
            ->column('note_id');
        $sqlRole = "SELECT a.id,name,`desc`,user_id,owner,protect,tip,flag,category_id,a.created,u.username 
                        from notes a 
                        LEFT JOIN user u 
                            ON a.owner = u.id 
                        where a.id IN (" . implode(',', $findNotesId) . ")";
        $notesInfo = [];
        // 非搜索模式
        if (empty($seach)) {
            if (count($findNotesId) > 0) {
                // 计算用户和角色记录总数
                $countUser = Db::table('notes')
                    ->where('owner', '=', $user->id)
                    ->where('flag', '=', 0)
                    ->count();
                $countRole = Db::table('notes')
                    ->where('id', 'IN', $findNotesId)
                    ->where('flag', '=', 1)
                    ->count();

                $notesInfo = Db::table('notes')
                    ->alias('a')
                    ->where('a.owner', '=', $user->id)
                    ->join('user u', 'a.owner = u.id')
                    ->union([
                        $sqlRole,
                    ])
                    ->field('a.id,name,desc,user_id,owner,protect,tip,flag,category_id,a.created,u.username')
                    ->order('id', 'desc')
                    ->paginate([
                        'list_rows' => $size ?: 10,
                        'page' => $current ?: 1,
                    ], $countUser + $countRole)
                    ->toArray();
            } else {
                $notesInfo = Db::table('notes')
                    ->alias('a')
                    ->where('a.owner', '=', $user->id)
                    ->where('flag', '=', 0)
                    ->join('user u', 'a.owner = u.id')
                    ->field('a.id,name,desc,user_id,owner,protect,tip,flag,category_id,a.created,u.username')
                    ->order('id', 'desc')
                    ->paginate([
                        'list_rows' => $size ?: 10,
                        'page' => $current ?: 1,
                    ])
                    ->toArray();
            }
        } // 搜索模式
        else {
            if (is_null($flag)) {
                return $this->retSucceedInfo('搜索条件不全', 2);
            }
            if (count($findNotesId) > 0) {
                // 个人模式
                if ($flag == 0) {
                    $notesInfo = Db::table('notes')
                        ->alias('a')
                        ->where('a.owner', '=', $user->id)
                        ->where('flag', '=', 0)
                        ->where('name', 'like', "%" . $name . "%")
                        ->join('user u', 'a.owner = u.id')
                        ->field('a.id,name,desc,user_id,owner,protect,tip,flag,category_id,a.created,u.username')
                        ->order('id', 'desc')
                        ->paginate([
                            'list_rows' => $size ?: 10,
                            'page' => $current ?: 1,
                        ])
                        ->toArray();
                }
                // 团队
                if ($flag == 1) {
                    $notesInfo = Db::table('notes')
                        ->alias('a')
                        ->where('a.owner', '=', $user->id)
                        ->where('flag', '=', 1)
                        ->where('a.name', 'like', "%" . $name . "%")
                        ->join('user u', 'a.owner = u.id')
                        ->field('a.id,name,desc,user_id,owner,protect,tip,flag,category_id,a.created,u.username')
                        ->order('id', 'desc')
                        ->paginate([
                            'list_rows' => $size ?: 10,
                            'page' => $current ?: 1,
                        ])
                        ->toArray();
                }
                // 全部模式
                if ($flag == 2) {
                    // 拼合语句
                    $sqlRoleSearch = "SELECT a.id,name,`desc`,user_id,owner,protect,tip,flag,category_id,a.created,u.username 
                        from notes a 
                        LEFT JOIN user u 
                            ON a.owner = u.id 
                        where a.id IN (" . implode(',', $findNotesId) . ") 
                        and  name like '%" . $name . "%'";

                    // 计算用户和角色记录总数
                    $countUser = Db::table('notes')
                        ->where('owner', '=', $user->id)
                        ->where('flag', '=', 0)
                        ->where('name', 'like', "%" . $name . "%")
                        ->count();
                    $countRole = Db::table('notes')
                        ->where('id', 'IN', $findNotesId)
                        ->where('flag', '=', 1)
                        ->where('name', 'like', "%" . $name . "%")
                        ->count();

                    $notesInfo = Db::table('notes')
                        ->alias('a')
                        ->where('a.owner', '=', $user->id)
                        ->where('a.name', 'like', '%' . $name . '%')
                        ->join('user u', 'a.owner = u.id')
                        ->union([
                            $sqlRoleSearch,
                        ])
                        ->field('a.id,name,desc,user_id,owner,protect,tip,flag,category_id,a.created,u.username')
                        ->order('id', 'desc')
                        ->paginate([
                            'list_rows' => $size ?: 10,
                            'page' => $current ?: 1,
                        ], $countUser + $countRole)
                        ->toArray();

                }
                $notesInfo['seach'] = $seach;
                $notesInfo['flag'] = $flag;
                $notesInfo['name'] = $name;
            } else {
                // 搜索模式下。当没有找到团队项目时查找个人项目
                $notesInfo = Db::table('notes')
                    ->alias('a')
                    ->where('a.owner', '=', $user->id)
                    ->where('name', 'like', "%" . $name . "%")
                    ->join('user u', 'a.owner = u.id')
                    ->field('a.id,name,desc,user_id,owner,protect,tip,flag,category_id,a.created,u.username')
                    ->order('id', 'desc')
                    ->paginate([
                        'list_rows' => $size ?: 10,
                        'page' => $current ?: 1,
                    ])
                    ->toArray();
            }
        }

        $notesInfo['total_page'] = (int)ceil($notesInfo['total'] / $notesInfo['per_page']);
        return $this->retSucceed($notesInfo);
    }

    /**
     *  获取用户的组信息
     * @return Json
     */
    public function getUserRoles(): Json
    {
        // 从token获取用户
        $userArr = $this->getTokenUserArr();
        if (!$userArr) {
            return $this->retFailure("非法请求");
        }
        $user = (new User)->find($userArr['id']);
        $rolesList = $user->roles()
            ->visible(['id', 'name', 'full_name'])
            ->hidden(['pivot'])
            ->select()
            ->toArray();
        if ($rolesList) {
            return $this->retSucceed($rolesList);
        }
        return $this->retFailure('获取失败');
    }

    /**
     * 保存提交数据
     * @return Json
     */
    public function save(): Json
    {
        $name = input('name');
        $desc = input('desc');
        $userId = input('user_id');
        $protect = input('protect');
        $showWord = input('showWord');
        $tip = input('tip');
        $flag = input('flag');
        $roleArr = input('role');
        $categoryId = input('categoryId');

        // 验证
        if ($flag == "1") {
            if (!$roleArr) {
                return $this->retSucceedInfo('角色缺失', 2);
            }
        }

        $exsitCount = Db::table('notes')->where('name', '=', $name)->count();

        if ($exsitCount > 0) {
            return $this->retSucceedInfo('项目名已存在，请修改', 2);
        }

        $salt = RoundLongGenerate();
        $passEnStr = keyPassEncry($showWord, $salt);
        $data = [
            'name' => $name,
            'desc' => $desc,
            'user_id' => $userId,
            'owner' => $userId,
            'protect' => $protect,
            'show_word' => $passEnStr,
            'show_salt' => $salt,
            'tip' => $tip,
            'flag' => $flag,
            'category_id' => $categoryId
        ];

        try {
            $notes = Notes::create($data);
        } catch (PDOException $e) {
            return $this->retSucceedInfo($e->getMessage(), 2);
        }

        if ($flag == "1") {
            $notes->roles()->saveAll($roleArr);
            // 统一设置权限
            $roleList = Role::select($roleArr);
            // 添加权限
            foreach ($roleList as $key => $role) {
                // 获取note下的选项
                Enforcer::addPermissionForUser($role->name, 'items/getItems/' . strval($notes->id), 'read');
                // 授权可以访问此id下的所有items
                Enforcer::addPermissionForUser($role->name, 'items/desc/' . strval($notes->id) . "/*", 'read');
                Enforcer::addPermissionForUser($role->name, 'items/desc/' . strval($notes->id) . "/*", 'write');
            }
        } else {
            $user = $this->getTokenUser();
            if (!$user) {
                return $this->retSucceedInfo('未找到用户', 2);
            }
            Enforcer::addPermissionForUser($user->username, 'items/getItems/' . strval($notes->id), 'read');
            Enforcer::addPermissionForUser($user->username, 'items/desc/' . strval($notes->id) . "/*", 'read');
            Enforcer::addPermissionForUser($user->username, 'items/desc/' . strval($notes->id) . "/*", 'write');
        }

        if ($notes) {
            // 清理缓存
            $this->executeClear();
            return $this->retSucceedInfo('新建主条目成功！');
        }
        return $this->retSucceedInfo('新建主条目失败！', 2);
    }

    /**
     * 获取notes角色
     */
    public function getNotesRoles(): Json
    {
        $notesId = input('id');

        $notes = Notes::find($notesId);

        if (!$notes) {
            return $this->retSucceedInfo('未找到主数据', 2);
        }

        $data = $notes->roles()->column('id');
        return $this->retSucceed($data);
    }


    /**
     * 验证密码
     */
    public function checkPassWord()
    {
        $notesId = input('id');
        $pass = input('pass');
        $notes = Notes::find($notesId);

        if (!$notes) {
            return $this->retSucceedInfo('未找到主数据', 2);
        }

        if ($notes->protect == "1") {
            $salt = $notes->show_salt;
            $passEnStr = keyPassEncry($pass, $salt);
            if ($passEnStr != $notes->show_word) {
                return $this->retSucceedInfo('密码不正确', 2);
            }
            return $this->retSucceedInfo('密码验证成功');
        }
        return $this->retSucceedInfo('项目未设密码', 2);
    }

    /**
     *  删除
     */
    public function delete()
    {
        $notesId = input('id');

        $notes = Notes::find($notesId);

        $user = $this->getTokenUser();



        if (!$notes) {
            return $this->retSucceedInfo('未找到主数据', 2);
        }

        if ($user->id != $notes->owner){
            return $this->retSucceedInfo('只有条目所有者，才能删除',2);
        }

        $itemCount = Db::table('items')->where('note_id', '=', $notesId)->count();

        if ($itemCount > 0) {
            return $this->retSucceedInfo('请先删除主条目下的附加条目',2);
        }

        if ($notes->flag == "1") {
            $roleArr = $notes->roles()->column('id');
            // 统一设置权限
            $roleList = Role::select($roleArr);
            // 添加权限
            foreach ($roleList as $key => $role) {
                // 获取note下的选项
                Enforcer::deletePermissionForUser($role->name, 'items/getItems/' . strval($notes->id), 'read');
                // 授权可以访问此id下的所有items
                Enforcer::deletePermissionForUser($role->name, 'items/desc/' . strval($notes->id) . "/*", 'read');
                Enforcer::deletePermissionForUser($role->name, 'items/desc/' . strval($notes->id) . "/*", 'write');
            }
            $notes->roles()->detach($roleArr);
        } else {
            $user = $this->getTokenUser();
            if (!$user) {
                return $this->retSucceedInfo('未找到用户', 2);
            }
            Enforcer::deletePermissionForUser($user->username, 'items/getItems/' . strval($notes->id), 'read');
            Enforcer::deletePermissionForUser($user->username, 'items/desc/' . strval($notes->id) . "/*", 'read');
            Enforcer::deletePermissionForUser($user->username, 'items/desc/' . strval($notes->id) . "/*", 'write');
        }


        $notes->delete();
        // 清理缓存
        $this->executeClear();

        return $this->retSucceedInfo('删除成功！');

    }

    /**
     *  修改
     */
    public function update()
    {
        $id = input('id');
        $name = input('name');
        $desc = input('desc');
        $protect = input('protect');
        $showWord = input('showWord');
        $tip = input('tip');
        $flag = input('flag');
        $roleArr = input('role');
        $categoryId = input('categoryId');
        $oldPass = input('oldPass');
        $owner = input('owner');
        // 验证
        if ($flag == "1") {
            if (!$roleArr) {
                return $this->retSucceedInfo('角色缺失', 2);
            }
        }

        // 获取token比对是否是所有者
        $user = $this->getTokenUser();

        if ($user->id != $owner) {
            return $this->retSucceedInfo('不是所有者不能修改此项', 2);
        }

        $notes = Notes::find($id);

        if (!$notes) {
            return $this->retSucceedInfo('未找到主项目条目', 2);
        }

        $salt = $notes->show_salt;
        if ($notes->protect == "1") {
            $passEnStr = keyPassEncry($oldPass, $salt);
            if ($passEnStr != $notes->show_word) {
                return $this->retSucceedInfo('原始密码错误', 2);
            }
        }

        $dt = new DateTime();
        $data = [
            'name' => $name,
            'desc' => $desc,
            'protect' => $protect,
            'tip' => $tip,
            'flag' => $flag,
            'category_id' => $categoryId,
            'updated' => $dt->format('Y-m-d H:i:s')
        ];

        // 如果密码为空，则不更新
        if (!empty($showWord)) {
            $passEnStr = keyPassEncry($showWord, $salt);
            $data['show_word'] = $passEnStr;
        }

        if ($notes->flag == "0") {
            // 当不是个人模式或者所有者更该时。
            if (($notes->flag != $flag) || ($notes->owner != $owner)) {
                $user = User::find($notes->owner);
                if (!$user) {
                    return $this->retSucceedInfo('用户未找到', 2);
                }
                Enforcer::deletePermissionForUser($user->username, 'items/getItems/' . strval($notes->id), 'read');
                Enforcer::deletePermissionForUser($user->username, 'items/desc/' . strval($notes->id) . "/*", 'read');
                Enforcer::deletePermissionForUser($user->username, 'items/desc/' . strval($notes->id) . "/*", 'write');
            }
        }
        // 获取旧的分组,保存之前
        $oldRole = $notes->roles()->column('id');

        try {
            $notes->save($data);
        } catch (PDOException $e) {
            return $this->retSucceedInfo($e->getMessage(), 2);
        }

        if ($notes->flag == "1") {
            $subRole = array_diff($oldRole, $roleArr);
            $addRole = array_diff($roleArr, $oldRole);
            $allRole = Db::table('role')->column('name', 'id');

            // 减少的，删除权限
            if (count($subRole) > 0) {
                $notes->roles()->detach($subRole);
                $subRoleNameArr = [];
                foreach ($allRole as $key => $item) {
                    if (in_array($key, $subRole)) {
                        $subRoleNameArr[] = $item;
                    }
                }
                foreach ($subRoleNameArr as $ol) {
                    Enforcer::deletePermissionForUser($ol, 'items/getItems/' . strval($notes->id), 'read');
                    Enforcer::deletePermissionForUser($ol, 'items/desc/' . strval($notes->id) . "/*", 'read');
                    Enforcer::deletePermissionForUser($ol, 'items/desc/' . strval($notes->id) . "/*", 'write');
                }
            }
            // 增加的数据增加权限
            if (count($addRole) > 0) {
                $notes->roles()->saveAll($addRole);
                $addRoleNameArr = [];
                foreach ($allRole as $key => $item) {
                    if (in_array($key, $addRole)) {
                        $addRoleNameArr[] = $item;
                    }
                }
                foreach ($addRoleNameArr as $ol) {
                    Enforcer::addPermissionForUser($ol, 'items/getItems/' . strval($notes->id), 'read');
                    Enforcer::addPermissionForUser($ol, 'items/desc/' . strval($notes->id) . "/*", 'read');
                    Enforcer::addPermissionForUser($ol, 'items/desc/' . strval($notes->id) . "/*", 'write');
                }
            }

        } else {
            // 清空旧的note和role的关联
            if (count($oldRole) > 0) {
                $notes->roles()->detach($oldRole);
            }
            $user = User::find($notes->owner);
            if (!$user) {
                return $this->retSucceedInfo('用户未找到', 2);
            }
            Enforcer::addPermissionForUser($user->username, 'items/getItems/' . strval($notes->id), 'read');
            Enforcer::addPermissionForUser($user->username, 'items/desc/' . strval($notes->id) . "/*", 'read');
            Enforcer::addPermissionForUser($user->username, 'items/desc/' . strval($notes->id) . "/*", 'write');
        }

        if ($notes) {
            // 清理缓存
            $this->executeClear();
            return $this->retSucceedInfo('修改主条目成功！');
        }
        return $this->retSucceedInfo('修改主条目失败！', 2);
    }
}
