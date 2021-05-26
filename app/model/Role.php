<?php
namespace app\model;

use think\Model;

class Role extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'role';

    public function users(): \think\model\relation\BelongsToMany
    {
        return $this->belongsToMany(User::class, UserRoleAccess::class);
    }


}