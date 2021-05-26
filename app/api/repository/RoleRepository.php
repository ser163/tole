<?php

namespace app\api\repository;

use app\model\Role;
use fanxd\repository\Repository;

/**
 * Class RoleRepository
 */
class RoleRepository extends Repository
{
    public function model()
    {
        return Role::class;
    }
}
