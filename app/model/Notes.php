<?php
namespace app\model;

use think\Model;
use think\model\relation\BelongsToMany;

class Notes extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'notes';

    public function items()
    {
        return $this->hasMany(Items::class,'note_id');
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'notes_role_access','role_id','note_id',);
    }

}