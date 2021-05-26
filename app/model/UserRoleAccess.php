<?php
namespace app\model;

use think\model\Pivot;

class UserRoleAccess extends Pivot
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'user_role_access';

}