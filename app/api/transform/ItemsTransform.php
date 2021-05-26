<?php

namespace app\api\transform;

use fanxd\repository\command\transform\Transform;

class ItemsTransform extends Transform
{
    public function transform($items)
    {
        return [
            'id'            => $items['id'],
            'createTime'    => $items['create_time'],
            'updateTime'    => $items['update_time']
        ];
    }
}