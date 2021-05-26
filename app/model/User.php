<?php
namespace app\model;

use think\Model;
use \think\model\relation\BelongsToMany;

class User extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'user';

    protected $type = [
        'enable' =>  'boolean',
    ];

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role_access','role_id','user_id',);
    }

    /**
     *  将用户添加进
     */
    public function joinRole() {

    }

}