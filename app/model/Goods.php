<?php
namespace app\model;

use think\Model;

class Goods extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'goods';

    public function accessorys()
    {
        return $this->hasOne(Accessory::class,'id','accessory_id');
    }

}