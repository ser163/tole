<?php
declare (strict_types=1);

namespace app\api\controller;

use app\ExController;
use app\model\Rules;
use Psr\SimpleCache\InvalidArgumentException;
use think\db\exception\PDOException;
use think\facade\Db;
use think\facade\Log;
use think\View;
use \think\response\Json;

class RuleController extends ExController
{
    /**
     *  首页浏览查询
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $size = input('size');
        $current = input('current');
        $seach = input('seach');
        if (empty($seach)) {
            $logInfo = Db::table('rules')
                ->alias('r')
                ->join('role o', 'r.v0 = o.name')
                ->field('r.id,ptype,v0  as role,v1 as rout,v2 as action,o.full_name as name')
                ->order('r.id', 'desc')
                ->paginate([
                    'list_rows' => $size ?: 10,
                    'page' => $current ?: 1,
                ])
                ->toArray();
        } else {
            $role = input('role');
            $route = input('route');
            if ($role || $role) {
                $map = [];
                if ($role) {
                    $map = [
                        ['r.v0', 'like', '%' . $role . '%'],
                    ];
                }
                if ($route) {
                    $map = array_merge($map, [['r.v1', 'like', '%' . $route . '%']]);
                }
                $logInfo = Db::table('rules')
                    ->alias('r')
                    ->join('role o', 'r.v0 = o.name')
                    ->where($map)
                    ->field('r.id,ptype,v0  as role,v1 as rout,v2 as action,o.full_name as name')
                    ->order('r.id', 'desc')
                    ->paginate([
                        'list_rows' => $size ?: 10,
                        'page' => $current ?: 1,
                    ])
                    ->toArray();

                $logInfo['seach'] = true;
                $logInfo['role'] = $role;
                $logInfo['route'] = $route;
            } else {
                // 执行全部搜素
                $logInfo = Db::table('rules')
                    ->alias('r')
                    ->join('role o', 'r.v0 = o.name')
                    ->field('r.id,ptype,v0  as role,v1 as rout,v2 as action,o.full_name as name')
                    ->order('r.id', 'desc')
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

    /**
     *  保存数据
     */
    public function save(): Json
    {
        $ptype = input('ptype');
        $optDesc ="ptype: $ptype ,";
        $role = input('role');
        $optDesc .="role: $role ,";
        $rout = input('rout');
        $optDesc .="rout: $rout ,";
        $action = input('action');
        $optDesc .="action: $action ,";
        $data = [
            'ptype' => $ptype,
            'v0' => $role,
            'v1' => $rout,
            'v2' => $action
        ];
        $exsit = Db::table('rules')->where(
            [
                ['ptype', '=', $ptype],
                ['v0', '=', $role],
                ['v1', '=', $rout],
                ['v2', '=', $action],
            ]
        )->count();

        if ($exsit > 0) {
            $optDesc .="  Fail!!! exsit item";
            $this->sendEvent('AddRules', $optDesc);
            return $this->retFailure('记录已存在');
        }

        try {
            $ret = Rules::create($data);
        } catch (PDOException $exception) {
            return $this->retFailure($exception->getMessage());
        }
        if ($ret) {
            // 清理缓存
            $this->executeClear();
            $optDesc .="  Suss";
            $this->sendEvent('ChangeRules', $optDesc);
            return $this->retSucceedInfo('创建成功');
        }
        return $this->retFailure('创建失败');
    }

    /**
     *  修改数据
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update(): Json
    {
        $id = input('id');
        $optDesc = "Change Id: $id ";
        $ptype = input('ptype');
        $optDesc .= "Change ptype: $ptype ";
        $role = input('role');
        $optDesc .= "Change role: $role ";
        $rout = input('rout');
        $optDesc .= "Change rout: $rout ";
        $action = input('action');
        $optDesc .= "Change action: $action ";
        $data = [
            'ptype' => $ptype,
            'v0' => $role,
            'v1' => $rout,
            'v2' => $action
        ];
        $rules = Rules::find($id);

        if (!$rules) {
            return $this->retFailure('未找到此条记录');
        }

        $exsit = Db::table('rules')->where(
            [
                ['ptype', '=', $ptype],
                ['v0', '=', $role],
                ['v1', '=', $rout],
                ['v2', '=', $action],
            ]
        )->field('id')->select()->toArray();
        $exsitId = [];
        array_map(function ($v) use (&$exsitId) {
            array_push($exsitId, $v['id']);
        }, $exsit);
        if (count($exsit) > 0) {
            if (!in_array($id, $exsitId)) {
                $optDesc .="  Fail!!!exsit id";
                $this->sendEvent('ChangeRules', $optDesc);
                return $this->retFailure('记录已存在');
            }
        }

        try {
            $ret = $rules->save($data);
        } catch (PDOException $e) {
            $optDesc .="  Fail!!!error";
            $this->sendEvent('ChangeRules', $optDesc);
            return $this->retFailure($e->getMessage());
        }

        if ($ret) {
            // 清理缓存
            $this->executeClear();
            // 记录操作
            $optDesc .="  Suss";
            $this->sendEvent('ChangeRules', $optDesc);
            return $this->retSucceedInfo('修改成功');
        }
        return $this->retFailure('创建失败');
    }

    /**
     *  删除成功
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete(): Json
    {
        $id = input('id');
        $rules = Rules::find($id);
        if (!$rules) {
            return $this->retFailure('记录未找到');
        }
        $ret = $rules->delete();

        if ($ret) {
            // 清理缓存
            $this->executeClear();
            // 添加操作信息日志
            $optDesc = "Delete: Rules=>" . strval($id);
            $this->sendEvent('DelRules', $optDesc);
            return $this->retSucceedInfo('删除成功');
        }

    }

}
