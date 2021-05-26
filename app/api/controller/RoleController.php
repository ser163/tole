<?php

namespace app\api\controller;

use app\api\repository\RoleRepository;
use app\api\transform\RoleTransform;
use app\ExController;
use app\model\UserRoleAccess;
use think\App;
use think\db\exception\PDOException;
use think\Request;
use tauthz\facade\Enforcer;
use app\model\Role;
use app\model\User;
use \think\response\Json;
use DateTime;
use think\facade\Db;

/**
 * Class RoleController
 * @package app\api\controller
 */
class RoleController extends ExController
{
    /**
     * @var RoleRepository
     */
    protected $repository;

    /**
     * @var RoleTransform
     */
    protected $transform;

    /**
     * RoleController constructor.
     * @param RoleRepository $repository
     * @param RoleTransform $transform
     * @param $app
     */
    public function __construct(RoleRepository $repository, RoleTransform $transform, App $app)
    {
        parent::__construct($app);
        $this->repository = $repository;
        $this->transform = $transform;
    }

    /**
     * 显示资源列表
     *
     */
    public function index()
    {
        $size = input('size');
        $current = input('current');

        $roleInfo = $this->repository->paginate(
            [
                'list_rows' => $size ?: 10,
                'page' => $current ?: 1,
            ]
        )->toArray();

        $roleInfo['total_page'] = (int)ceil($roleInfo['total'] / $roleInfo['per_page']);
        return $this->retSucceed($roleInfo);
    }

    /**
     * 保存新建的资源
     *
     */
    public function save()
    {
        $name = input('name');

        $exsitCount = Db::table('role')->where('name', $name)->count();

        if ($exsitCount > 0) {
            return $this->retSucceedInfo('角色名已存在',1);
        }

        try {
            $role = $this->repository->create($this->request->post());
        } catch (PDOException $e) {
            return $this->retSucceedInfo($e->getMessage(), 2);
        }

        // 激活权限添加事件
        event('RoleAdd', $role);
        $optDesc = "Add Role: $role->name ";
        // 激活记录事件
        $this->sendEvent('AddRole', $optDesc);
        return $this->retSucceedInfo('添加角色成功');
    }

    /**
     * 显示指定的资源
     *
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function read($id)
    {
        $data = $this->repository->first($id);
        $this->success($data);
    }

    /**
     * 保存更新的资源
     *
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function update()
    {
        $id = input('id');
        $name = input('name');
        $fullName = input('full_name');
        $desc = input('desc');
        $dt = new DateTime();
        $data = [
            "name" => $name,
            'full_name' => $fullName,
            'desc' => $desc,
            'updated' => $dt->format('Y-m-d H:i:s')
        ];
        try {
            $this->repository->update($id, $data);
        } catch (PDOException $exception) {
            return $this->retSucceedInfo($exception->getMessage(), 2);
        }
        $optDesc = "name:$name,full_name: $fullName,desc: $desc";
        $optDesc = "Modify Role: " . $data['name'] . $optDesc;
        $this->sendEvent('ModifyRole', $optDesc);
        return $this->retSucceedInfo('修改角色成功');
    }

    /**
     * 删除指定资源
     * @param int $id
     */
    public function delete()
    {
        $id = input('id');
        $role = Role::find($id);
        if (!$role) {
            return $this->retFailure('未找到记录');
        }
        // 删除用户角色关联
        UserRoleAccess::where('role_id', $role->id)->delete();
        // 删除rules表
        Enforcer::deletePermissionsForUser($role->name);
        Enforcer::deleteRole($role->name);

        $this->repository->delete($id);

        return $this->retSucceedInfo('删除角色成功');
    }

    /**
     *  获取角色内的所有用户
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRoleInUser()
    {
        $id = input('id');
        $role = Role::find($id);
        if (!$role) {
            return $this->retFailure('未找到记录');
        }
        $data = $role->users
            ->visible(['id', 'username'])->hidden(['pivot'])
            ->toArray();

        return $this->retSucceed(['data' => $data]);
    }

    /**
     * 删除角色内的用户
     */
    public function deleteUserOnRoles()
    {
        $id = input('id');
        $usersIdArr = input('users');
        if (!$usersIdArr) {
            return $this->retFailure('需要删除用户的id');
        }
        $role = Role::find($id);
        if (!$role) {
            return $this->retFailure('未找到记录');
        }
        $optDesc = "del Role: $role->name In User=>";
        // 删除用户角色关联
        UserRoleAccess::where('role_id', $role->id)
            ->where('user_id', 'IN', $usersIdArr)->delete();

        // 删除rules表
        $userList = User::where('id', 'IN', $usersIdArr)->select();

        $idx = 0;
        $tmpStr = '';
        foreach ($userList as $user) {
            $idx++;
            Enforcer::deleteRoleForUser($user->username, $role->name);
            $tmpStr .= strval($idx) . ": $user->username,";
        }
        $optDesc .= $tmpStr;
        $this->sendEvent('DelUserOnRoles', $optDesc);
        // 执行缓存清理
        $this->executeClear();
        return $this->retSucceedInfo('用户角色解除成功');
    }

    /**
     * 获取所有用户，并把角色内的用户禁用
     */
    public function getUserAllInRoles()
    {
        $roleId = input('id');
        $search = input('search');
        $role = Role::find($roleId);
        if (!$role) {
            return $this->retFailure('未找到记录');
        }
        $allInRolesUser = $role->users->column('id');

        if ($search) {
            $allUser = User::where('enable', 1)
                ->where('username|first_name|last_name', 'like', '%' . $search . '%')
                ->select();
        } else {
            $allUser = User::where('enable', 1)
                ->select();
        }

        $userList = [];

        foreach ($allUser as $user) {
            $tmpArr = [];
            $tmpArr['id'] = $user->id;
            $tmpArr['username'] = $user->username;
            if (in_array($user->id, $allInRolesUser)) {
                $tmpArr['disable'] = true;
            } else {
                $tmpArr['disable'] = false;
            }
            array_push($userList, $tmpArr);
        }

        return $this->retSucceed($userList);
    }

    /**
     *  添加用户到指定的角色内
     */
    public function joinRoles()
    {
        $roleId = input('id');
        $userList = input('users');
        if (!$userList) {
            return $this->retFailure('未找到用户');
        }

        $role = Role::find($roleId);
        if (!$role) {
            return $this->retFailure('未找到记录');
        }

        $allUser = User::where('id', 'IN', $userList)->select();

        $roleAccData = [];
        foreach ($allUser as $user) {
            $tmp = [
                'user_id' => $user->id,
                'role_id' => $role->id
            ];
            array_push($roleAccData, $tmp);

            // 2.添加用户到对应的权限组
            Enforcer::addRoleForUser($user->username, $role->name);
        }
        // 添加用户到角色关联表
        $userToRole = new UserRoleAccess;

        $userToRole->saveAll($roleAccData);
        // 执行缓存清理
        $this->executeClear();

        return $this->retSucceedInfo('用户添加角色成功');
    }

}
