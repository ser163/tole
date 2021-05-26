<?php
declare (strict_types = 1);

namespace app\subscribe;

use think\facade\Log;
use app\model\UserLogs;
use DateTime;

class User
{

    protected $eventPrefix = 'User';

    public function onLogin($user)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $userId = $user->id;
        $userLog = new UserLogs;
        $userLog->save([
            'ip'  => $ip,
            'user_id' =>  $userId,
            'login_time'=>Date('Y-m-d H:i:s'),
            'login'=>true
        ]);
    }

    public function onLogout($user)
    {
        // UserLogout事件响应处理
        $ip = $_SERVER['REMOTE_ADDR'];
        $userId = $user->id;
        $userLog = new UserLogs;
        $userLog->save([
            'ip'  => $ip,
            'user_id' =>  $userId,
            'login_time'=>Date('Y-m-d H:i:s'),
            'login'=>false
        ]);
    }

}
