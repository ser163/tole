<?php
declare (strict_types = 1);

namespace app\listener;

use think\facade\Log;

class UserLogin
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($user)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        Log::info("用户登录：".$user->username."   from: ".$ip);
    }
}
