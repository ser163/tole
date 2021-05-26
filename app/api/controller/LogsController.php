<?php
declare (strict_types=1);

namespace app\api\controller;

use app\ExController;
use Psr\SimpleCache\InvalidArgumentException;
use think\facade\Db;
use think\facade\Log;
use think\View;

class LogsController extends ExController
{
    public function index()
    {
        $size = input('size');
        $current = input('current');
        $seach = input('seach');
        if (empty($seach)) {
            $logInfo = Db::table('user_logs')
                ->alias('l')
                ->join('user u', 'l.user_id = u.id')
                ->field('l.id,ip,login_time,login,u.username')
                ->order('l.id', 'desc')
                ->paginate([
                    'list_rows' => $size ?: 10,
                    'page' => $current ?: 1,
                ])
                ->toArray();
        } else {
            $userName = input('userName');
            $ip = input('ip');
            if ($userName || $ip) {
                $map = [];
                if ($userName) {
                    $map = [
                        ['u.username', 'like', '%' . $userName . '%'],
                    ];
                }
                if ($ip) {
                    $map = array_merge($map, [['l.ip', 'like', '%' . $ip . '%']]);
                }
                $logInfo = Db::table('user_logs')
                    ->alias('l')
                    ->join('user u', 'l.user_id = u.id')
                    ->where($map)
                    ->field('l.id,ip,login_time,login,u.username')
                    ->order('l.id', 'desc')
                    ->paginate([
                        'list_rows' => $size ?: 10,
                        'page' => $current ?: 1,
                    ])
                    ->toArray();

                $logInfo['seach'] = true;
                $logInfo['userName'] = $userName;
                $logInfo['ip'] = $ip;
            } else {
                // 执行全部搜素
                $logInfo = Db::table('user_logs')
                    ->alias('l')
                    ->join('user u', 'l.user_id = u.id')
                    ->field('l.id,ip,login_time,login,u.username')
                    ->order('l.id', 'desc')
                    ->paginate([
                        'list_rows' => $size ?: 10,
                        'page' => $current ?: 1,
                    ])
                    ->toArray();
            }
        }

        $logInfo['total_page'] = (int)ceil($logInfo['total'] / $logInfo['per_page']);
        return $this->retSucceed($logInfo);
    }
}
