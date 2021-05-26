<?php

namespace app\api\controller;

use app\api\repository\DepartRepository;
use app\api\transform\DepartTransform;
use app\ExController;
use app\model\UserRoleAccess;
use think\App;
use think\db\exception\PDOException;
use DateTime;


/**
 * Class RoleController
 * @package app\api\controller
 */
class DepartController extends ExController
{
    /**
     * @var DepartRepository
     */
    protected $repository;

    /**
     * @var DepartTransform
     */
    protected $transform;

    /**
     * RoleController constructor.
     * @param DepartRepository $repository
     * @param DepartTransform $transform
     * @param $app
     */
    public function __construct(DepartRepository $repository, DepartTransform $transform, App $app)
    {
        parent::__construct($app);
        $this->repository = $repository;
        $this->transform = $transform;
    }

    /**
     *  获取属性数据列表
     */
    public function getTreeData()
    {
        $treeData = $this->repository->treeData();
        return $this->retSucceed($treeData);
    }

    /**
     * 根据当前id过滤数据
     */
    public function getDepartData()
    {
        $id = input('id');
        $allData = $this->repository->allData($id);

        if ($allData) {
            return $this->retSucceed($allData);
        }
        return $this->retSucceed([]);
    }


    /**
     * 新建的时候返回所有部门
     */
    public function getAllDepart()
    {
        $allData = $this->repository->allDepart();

        if ($allData) {
            return $this->retSucceed($allData);
        }
        return $this->retSucceed([]);
    }

    /**
     * 新增部门
     */
    public function addDepart()
    {
        $name = input('name');
        $parent = input('parent');
        $role = input('role');

        if ($role) {
            $data = [
                "name" => $name,
                "parent_id" => $parent,
                "role_id" => $role
            ];
        } else {
            $data = [
                "name" => $name,
                "parent_id" => $parent,
            ];
        }

        try {
            $ret = $this->repository->create($data);
        } catch (PDOException $e) {
            return $this->retFailure($e->getMessage());
        }

        if ($ret) {
            return $this->retSucceedInfo('创建部门成功');
        }
        return $this->retSucceedInfo('创建部门失败', 2);
    }

    /**
     * 修改部门
     */
    public function updateDepart()
    {
        $id = input('id');
        $name = input('name');
        $parent = input('parent');
        $role = input('role');

        if ($role) {
            $data = [
                "name" => $name,
                "parent_id" => $parent,
                "role_id" => $role,

            ];
        } else {
            $data = [
                "name" => $name,
                "parent_id" => $parent,
            ];
        }
        $dt = new DateTime();
        $data = array_merge($data, ['updated' => $dt->format('Y-m-d H:i:s')]);

        try {
            $ret = $this->repository->update(['id' => $id], $data);
        } catch (PDOException $e) {
            return $this->retFailure($e->getMessage());
        }

        if ($ret) {
            return $this->retSucceedInfo('修改部门成功');
        }
        return $this->retSucceedInfo('修改部门失败', 2);
    }

    /**
     *  删除部门
     */
    public function deleteDepart(): \think\response\Json
    {
        $id = input('id');
        if (!$id) {
            return $this->retSucceedInfo('未找到id', 2);
        }
        // 判断此部门是否有子部门
        if ($this->repository->isSubDepart($id)) {
            return $this->retSucceedInfo('有子部门，不能删除', 2);
        }

        try {
            $ret = $this->repository->delete(['id' => $id]);
        } catch (PDOException $exception) {
            $this->retSucceedInfo($exception->getMessage());
        }

        if ($ret) {
            return $this->retSucceedInfo('删除成功');
        }
        return $this->retSucceedInfo('删除部门失败');
    }

}
