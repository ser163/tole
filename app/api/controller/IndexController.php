<?php
declare (strict_types=1);

namespace app\api\controller;

use app\ExController;
use app\model\Notes;
use think\facade\Db;

class IndexController extends ExController
{
    public function index()
    {
        return '这是api 示例';
    }

    /**
     *  首页统计信息
     */
    public function info()
    {
        $user = $this->getTokenUser();
        if (!$user) {
            $this->retSucceedInfo('用户获取失败', 2);
        }

        $userId = $user->id;

        // 统计note项目的数量
        $noteCount = Db::table('notes')->where('owner', $userId)->count();
        // 统计items项目的数量
        $itemCount = Db::table('items')->count();
        //统计用户的items项目数量
        $itemUserCount = Db::table('items')->where('user_id', $userId)->count();
        // 统计项目访问次数
        $vistCount = Db::table('records')->where('owner', $userId)->count();
        // 自己加入的角色
        $roleArr = $user->roles()->visible(['id', 'full_name'])->select()->toArray();
        // 动态
        $activityInfo = Db::table('activitys')
            ->order('id', 'desc')
            ->limit(20)
            ->select()->toArray();

        // 循环计算时间
        foreach ($activityInfo as &$item) {
            $item['time'] = humanDate($item['created']);
        }
        unset($item);
        // 用户最近新添加的项目
        $notesUserNews = Notes::where('owner', $userId)
            ->order('created', 'desc')
            ->limit(21)
            ->select();

        $news = [];
        foreach ($notesUserNews as $no) {
            $tempArr = [];
            $tempArr['title'] = $no->name;
            $tempArr['desc'] = $no->desc;
            $tempArr['time'] = humanDate($no->created);

            if ($no->flag == 0) {
                $tempArr['group'] = "个人项目";
            } else {
                $roles = $user->roles()->column('full_name');
                $tempArr['group'] = implode(',', $roles);
            }
            array_push($news, $tempArr);
        }


        $data = [
            'type' => 0,
            'stat' => [
                'noteCount' => $noteCount,
                'itemCount' => $itemCount,
                'itemUserCount' => $itemUserCount,
                'vistCount' => $vistCount
            ],
            'roles' => $roleArr,
            'activity' => $activityInfo,
            'news'=>$news
        ];

        return $this->retSucceed($data);
    }


}
