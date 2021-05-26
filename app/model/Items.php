<?php
namespace app\model;

use think\Model;

class Items extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'items';

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

}