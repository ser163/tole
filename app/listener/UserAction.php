<?php
declare (strict_types=1);

namespace app\listener;

use app\model\History;

class UserAction
{
    /**
     * 事件监听处理
     *
     * @param $userAction
     * @return mixed
     */
    public function handle($userAction)
    {
        // 记录用户行为
        $ip = $_SERVER['REMOTE_ADDR'];
        $userId = $userAction->user->id;

        $history = new History;
        $data = [
            'action' => $userAction->actCode,
            'ip' => $ip,
            'user_id' => $userId,
            'operation_time' => Date('Y-m-d H:i:s'),
            'desc' => $userAction->desc
        ];
        $history->save($data);
    }
}
