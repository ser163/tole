<?php
declare (strict_types=1);

namespace app\api\controller;

use app\ExController;
use Psr\SimpleCache\InvalidArgumentException;
use think\facade\Db;
use think\facade\Log;
use think\View;

class HistoryController extends ExController
{
    public function index()
    {
        $size = input('size');
        $current = input('current');
        $seach = input('seach');
        if (empty($seach)) {
            $logInfo = Db::table('history')
                ->alias('h')
                ->join('user u', 'h.user_id = u.id')
                ->field('h.id,ip,action,operation_time,desc,u.username')
                ->order('h.id', 'desc')
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
                    $map = array_merge($map, [['h.ip', 'like', '%' . $ip . '%']]);
                }
                $logInfo = Db::table('history')
                    ->alias('h')
                    ->join('user u', 'h.user_id = u.id')
                    ->where($map)
                    ->field('h.id,ip,action,operation_time,desc,u.username')
                    ->order('h.id', 'desc')
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
                $logInfo = Db::table('history')
                    ->alias('h')
                    ->join('user u', 'h.user_id = u.id')
                    ->field('h.id,ip,action,operation_time,desc,u.username')
                    ->order('h.id', 'desc')
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
