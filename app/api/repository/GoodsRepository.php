<?php

namespace app\api\repository;

use app\model\Category;
use fanxd\repository\Repository;

/**
 * Class RoleRepository
 */
class GoodsRepository extends Repository
{
    public function model()
    {
        return Category::class;
    }

    /**
     * 获取全部数据
     */
    public function getFullData()
    {
        return $this->get()->toArray();
    }

}
