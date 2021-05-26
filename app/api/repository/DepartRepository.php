<?php

namespace app\api\repository;

use app\model\Depart;
use fanxd\repository\Repository;

/**
 * Class RoleRepository
 */
class DepartRepository extends Repository
{
    public function model()
    {
        return Depart::class;
    }

    public function allDepart(): array
    {
        $all = $this->get()->toArray();
        return $all;
    }

    /**
     *  返回此id可以变为父节点的
     * @param $id
     * @return array
     */
    public function allData($id): array
    {
        $all = $this->get()->toArray();
        $topArr = [];
        $curentArr = [];
        foreach ($all as $item) {
            if (empty($item['parent_id'])) {
                if ($item['id'] == $id) {
                    $curentArr = $item;
                } else {
                    array_push($topArr, $item);
                }
            }

        }
        // 是顶级组织则，返回topArr
        if ($curentArr) {
            if (count($topArr) > 0) {
                return $topArr;
            }
            return [];
        }

        return $this->filterDepartId($all, $id);
    }

    /**
     *  过滤部门id
     * @param $all
     * @param $id
     * @return array
     */
    private function filterDepartId($all, $id): array
    {
        $rejectList = [$id];
        $this->filterId($all, $id, $rejectList);
        return array_filter($all, function ($item) use ($rejectList) {
            if (!in_array($item['id'], $rejectList)) {
                return $item;
            }
        });
    }

    /**
     *  过滤id
     * @param $departArr
     * @param $id
     * @param $rejectList
     */
    private function filterId($departArr, $id, &$rejectList)
    {
        $tempArr = [];
        $findList = [];
        foreach ($departArr as $item) {
            if ($item['parent_id'] == $id) {
                array_push($rejectList, $item['id']);
                array_push($findList, $item['id']);
            } else {
                array_push($tempArr, $item);
            }
        }
        if (count($findList) > 0) {
            foreach ($findList as $ol) {
                $this->filterId($tempArr, $ol, $rejectList);
            }
        }
    }

    /**
     *  生成树形数据
     */
    public function treeData(): array
    {
        $all = $this->get()->toArray();
        //  查找顶级
        $list = [];
        $depart = [];
        foreach ($all as $item) {
            if (!$item['parent_id']) {
                array_push($list, [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'expand' => true,
                    'children' => [],
                    'parent_id' => $item['parent_id'],
                    'role' => $item['role_id']
                ]);
            } else {
                array_push($depart, $item);
            }
        }

        foreach ($list as &$itemArr) {
            $level = 0;
            $this->findData($depart, $itemArr, $level);
        }
        unset($itemArr);

        return ['data' => $list, 'level' => 3];
    }

    /**
     *  递归查找
     * @param $all
     * @param $pArr
     * @param $level
     */
    private function findData($all, &$pArr, &$level)
    {
        $depart = [];
        $tempList = [];
        foreach ($all as $ol) {
            if ($ol['parent_id'] == $pArr['id']) {
                array_push($tempList, [
                    'id' => $ol['id'],
                    'name' => $ol['name'],
                    'expand' => true,
                    'children' => [],
                    'parent_id' => $ol['parent_id'],
                    'role' => $ol['role_id']
                ]);
            } else {
                array_push($depart, $ol);
            }
        }
        if (count($tempList) > 0) {
            $pArr['children'] = $tempList;
            $level++;
            foreach ($pArr['children'] as &$itemArr) {
                $this->findData($depart, $itemArr, $level);
            }
            unset($itemArr);
        }
    }

    /**
     *  判断是否有子部门
     * @param $id
     */
    public function isSubDepart($id): bool
    {
        $subArr = $this->findWhere(['parent_id' => $id]);
        if ($subArr) {
            return true;
        }
        return false;
    }
}
