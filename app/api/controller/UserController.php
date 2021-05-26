<?php
declare (strict_types=1);

namespace app\api\controller;

use app\ExController;
use app\event\UserAction;
use app\model\Role;
use DateTime;
use think\db\exception\PDOException;
use think\facade\Db;
use think\facade\Log;
use think\Request;
use thans\jwt\facade\JWTAuth;
use app\model\User;
use tauthz\facade\Enforcer;
use \think\response\Json;


class UserController extends ExController
{
    /**
     * 用户登录
     * @return \think\Response
     */
    public function login()
    {
        $userName = input('username');
        $passWord = input('password');
        $type = input('type');

        // 根据不同的登录方式选择字段
        switch ($type){
            case "user":
                $user = User::where('username', $userName)->findOrEmpty();
                break;
            case "mobile":
                $user = User::where('mobile', $userName)->findOrEmpty();
                break;
            default:
                $user = User::where('email', $userName)->findOrEmpty();
        }

        if (!$user->isEmpty()) {
            $md5Str = confusionPassword($passWord);
            $roles = $user->roles;
            if (count($roles) == 0) {
                return $this->retFailure("角色获取失败！");
            }
            $curRole = [];
            foreach ($roles as $role) {
                if ($role['name'] == 'admins') {
                    $curRole = $role->toArray();
                    break;
                }
                $curRole = $roles[0]->toArray();
            }
            $userArray = $user->toArray();
            if ($user['password'] == $md5Str) {
                // 判断账户是否被禁用
                if (!$user->enable) {
                    return $this->retFailure("账户被禁用，请联系管理员！");
                }
                unset($userArray['password']);
                unset($userArray['password_salt']);
                unset($userArray['created']);
                unset($userArray['updated']);
                $token = 'Bearer ' . JWTAuth::builder($userArray);
                if ($curRole['name'] == 'admins') {
                    $data = [
                        'token' => $token,
                        'role' => $curRole['name'],
                        'role_name' => $curRole['full_name'],
                        'name' => $user['username'],
                        'uid' => $user['id']
                    ];
                } else {
                    $data = [
                        'token' => $token,
                        'role' => 'users',
                        'role_name' => $curRole['full_name'],
                        'name' => $user['username'],
                        'uid' => $user['id']
                    ];
                }
//                Enforcer::addPermissionForUser('ser163', 'user/getAllUser', 'read');
                $headerArr = [
                    'Access-Control-Expose-Headers' => 'Authorization',
                    'Authorization' => $token,
                ];
                // 记录登录事件
                event('UserLogin', $user);
                return $this->retSucceedHeader($data, $headerArr);
            }
            return $this->retFailure("用户名或密码不对");
        }

        return $this->retFailure("用户名密码不对");
    }

    /**
     *  获取所有用户
     */
    public function getAllUser()
    {
        $size = input('size');
        $current = input('current');
        $seach = input('seach');
        if (empty($seach)) {
            $userInfo = Db::name('user')
                ->hidden(['updated', 'password', 'password_salt'])
                ->order('id', 'desc')
                ->paginate([
                    'list_rows' => $size ?: 10,
                    'page' => $current ?: 1,
                ])
                ->toArray();
        } else {
            $userName = input('userName');
            $mobile = input('mobile');
            if ($userName || $mobile) {
                $map = [];
                if ($userName) {
                    $map = [
                        ['username', 'like', '%' . $userName . '%'],
                    ];
                }
                if ($mobile) {
                    $map = array_merge($map, [['mobile', 'like', '%' . $mobile . '%']]);
                }
                $userInfo = Db::name('user')
                    ->where($map)
                    ->hidden(['updated', 'password', 'password_salt'])
                    ->order('id', 'desc')
                    ->paginate([
                        'list_rows' => $size ?: 10,
                        'page' => $current ?: 1,
                    ])
                    ->toArray();

                $userInfo['seach'] = true;
                $userInfo['userName'] = $userName;
                $userInfo['mobile'] = $mobile;
            } else {
                // 执行全部搜素
                $userInfo = Db::name('user')
                    ->hidden(['updated', 'password', 'password_salt'])
                    ->order('id', 'desc')
                    ->paginate([
                        'list_rows' => $size ?: 10,
                        'page' => $current ?: 1,
                    ])
                    ->toArray();
            }
        }
        if (count($userInfo['data']) > 0) {
            foreach ($userInfo['data'] as &$item) {
                $item['enable'] = $item['enable'] == 1;
            }
            unset($item);
        }


        $userInfo['total_page'] = (int)ceil($userInfo['total'] / $userInfo['per_page']);
        return $this->retSucceed($userInfo);
    }

    /**
     *  获取所有权限
     */
    public function getAllRole(): Json
    {
        $allRole = Db::table('role')->column('name,full_name', 'id');
        return $this->retSucceed($allRole);
    }

    /**
     * 设置用户启用禁用
     * @return Json
     */
    public function setUserEnable(): Json
    {
        $userId = input('id');
        $enable = input('enable');
        $userInfo = User::find($userId);
        $userInfo->enable = $enable;
        if ($userInfo->save()) {
            if ($enable) {
                $msg = $userInfo->username . ' 启用成功！';
                $optDesc = "enable user: $userId";
                $actCode = "EnableUser";
            } else {
                $optDesc = "disable user: $userId";
                $actCode = "DisableUser";
                $msg = $userInfo->username . ' 禁用成功！';
            }

            $this->sendEvent($actCode, $optDesc);
            return $this->retSucceedInfo($msg, TIP_SUCC);
        }
        return $this->retFailure('用户禁用失败');
    }

    /**
     *  添加用户
     * @return Json
     */
    public function addUser(): Json
    {
        $userName = input('userName');
        $userCount = Db::table('user')->where('username', $userName)->count();
        if ($userCount > 0) {
            return $this->retSucceedInfo($userName . '用户名已存在', 2);
        }
        // 判断是否和角色重名
        $RoleCount = Db::table('role')->where('name', $userName)->count();
        if ($RoleCount > 0) {
            return $this->retSucceedInfo($userName . '用户名已存在', 2);
        }
        $mobile = input('mobile');
        $mobileCount = Db::table('user')->where('mobile', $mobile)->count();
        if ($mobileCount > 0) {
            return $this->retSucceedInfo($mobile . '手机号已存在', 2);
        }
        $email = input('email');
        $emailCount = Db::table('user')->where('email', $mobile)->count();
        if ($emailCount > 0) {
            return $this->retSucceedInfo($email . '邮件地址已存在', 2);
        }
        $password = input('password');
        $rePassword = input('rePassword');
        $firstName = input('firstName');
        $lastName = input('lastName');
        $roleArr = input('role');

        if ($password !== $rePassword) {
            return $this->retFailure('输入秘密不一致');
        }
        if (count($roleArr) == 0) {
            return $this->retFailure('必须选中一个角色');
        }

        // 1.0 组合数组字符串
        $userInfo = [
            "username" => $userName,
            "mobile" => $mobile,
            "email" => $email,
            'password' => confusionPassword($password),
            'password_salt' => RoundGenerate(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'enable' => true
        ];
        try {
            $user = User::create($userInfo);
        } catch (PDOException $e) {
            // 这是进行验证异常捕获
            return $this->retFailure($e->getMessage());
        }

        $userId = $user->id;
        // 2.0 将用户添加进角色
        $user->roles()->saveAll($roleArr);
        // 2.1 将用户添加进casbin规则中的组权限
        $roles = $user->roles;
        $indx = 0;
        $roleMessage = "";
        foreach ($roles as $role) {
            // 将用户添加进角色中，实现权限关联
            $indx++;
            Enforcer::addRoleForUser($user->username, $role->name);
            $roleMessage .= strval($indx) . "：$role->name,";
        }
        // 添加操作信息
        $optDesc = "add user: $user->username id: $userId " . "Role => " . $roleMessage;
        $this->sendEvent('AddUser', $optDesc);
        return $this->retSucceedInfo('添加用户成功');
    }

    /**
     *  获取用户所有的角色
     */
    public function getUserRoles()
    {
        $userId = input('id');

        $user = User::find($userId);
        if (!$user) {
            return $this->retFailure('用户未找到');
        }
        // 获取用户的所有角色
        $roles = $user->roles;

        return $this->retSucceed($roles->toArray());
    }

    /**
     *  修改用户
     */
    public function setUserInfo(): Json
    {
        $userId = input('id');
        $userName = input('userName');
        $mobile = input('mobile');
        $email = input('email');
        $password = input('password');
        $rePassword = input('rePassword');
        $firstName = input('firstName');
        $lastName = input('lastName');
        $roleArr = input('role');
        $enable = input('enable');
        $data = [];
        $optDesc = "Change User=>";
        if ($password && $rePassword) {
            if ($password !== $rePassword) {
                return $this->retFailure('输入秘密不一致');
            }
            $optDesc .= "Password: $password,";
            $data = array_merge($data, ['password' => confusionPassword($password)]);
        }
        $data = array_merge($data, ['mobile' => $mobile]);
        $optDesc .= "Moible: $mobile,";
        $data = array_merge($data, ['first_name' => $firstName]);
        $optDesc .= "first_name: $firstName,";
        $data = array_merge($data, ['last_name' => $lastName]);
        $optDesc .= "last_name: $lastName,";
        $data = array_merge($data, ['enable' => $enable]);
        $enableStr = $enable ? '启用' : '禁用';
        $optDesc .= "enable: $enableStr,";
        $dt = new DateTime();
        $data = array_merge($data, ['updated' => $dt->format('Y-m-d H:i:s')]);
        try {
            Db::name('user')
                ->where('id', $userId)
                ->data($data)
                ->update();
        } catch (PDOException $e) {
            return $this->retFailure($e->getMessage());
        }
        // 更新角色
        $user = User::find($userId);
        $roles = $user->roles;
        $oldRole = [];

        foreach ($roles as $role) {
            $oldRole = array_merge($oldRole, [$role->id]);
        }
        $subRole = array_diff($oldRole, $roleArr);
        $addRole = array_diff($roleArr, $oldRole);
        $allRole = Db::table('role')->column('name', 'id');
        // 减少的，删除权限
        if (count($subRole) > 0) {
            $user->roles()->detach($subRole);
            $subRoleNameArr = [];
            foreach ($allRole as $key => $item) {
                if (in_array($key, $subRole)) {
                    $subRoleNameArr[] = $item;
                }
            }
            foreach ($subRoleNameArr as $ol) {
                $optDesc .= "delRole: $ol,";
                Enforcer::deleteRoleForUser($user->username, $ol);
            }
        }
        // 增加的数据增加权限
        if (count($addRole) > 0) {
            $user->roles()->saveAll($addRole);
            $addRoleNameArr = [];
            foreach ($allRole as $key => $item) {
                if (in_array($key, $addRole)) {
                    $addRoleNameArr[] = $item;
                }
            }
            foreach ($addRoleNameArr as $ol) {
                $optDesc .= "addRole: $ol,";
                Enforcer::addRoleForUser($user->username, $ol);
            }
        }
        $this->sendEvent('ModifyUser', $optDesc);
        return $this->retSucceedInfo($user->username . '修改成功');
    }

    /**
     *  设置个人密码
     */
    public function setUserPassWord(): Json
    {
        // 对比用户身份
        $userArr = $this->getTokenUserArr();
        if (!$userArr) {
            return $this->retFailure("非法请求");
        }
        $id = input('id');
        if ($userArr['id'] != $id) {
            return $this->retFailure("身份验证失败");
        }
        $new = input('new');
        $old = input('old');
        $userInfo = User::find($id);
        if (!$userInfo) {
            return $this->retFailure('用户未找到');
        }
        $oldStr = confusionPassword($old);
        $newStr = confusionPassword($new);
        $optDesc = '';
        if ($userInfo['password'] == $oldStr) {
            $userInfo->password = $newStr;
            $userInfo->save();
            $optDesc = "change self password suss: $new ";
            $this->sendEvent('ChangeUserPW', $optDesc);
            return $this->retSucceedInfo("用户密码修改成功");
        } else {
            $optDesc = "change self password fail: $old | $new ";
            $this->sendEvent('ChangeUserPW', $optDesc);
            return $this->retFailure('原密码错误');
        }
        $optDesc = "change self password fail: $new ";
        $this->sendEvent('ChangeUserPW', $optDesc);
        return $this->retFailure("修改密码失败");
    }

    /**
     * 测试订阅事件
     */
    public function testEven()
    {
        Log::info("事件发送成功！UserLogin");
        $user = User::find(1);
//        event('UserLogin', $user);
        event('UserAction', new UserAction($user, 'TestAction', 'actionDesc'));
        return $this->retSucceedInfo("事件发送成功");
    }

}
