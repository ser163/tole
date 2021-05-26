<?php
declare (strict_types=1);

namespace app\event;

use app\model\User;

class UserAction
{
    public User $user;
    public string $actCode;
    public string $desc;

    public function __construct(User $user, string $actCode, ...$args)
    {
        $this->user = $user;
        $this->actCode = $actCode;
        if (isset($args)) {
            if (is_array($args) && (count($args)>0) ) {
                if (is_string($args[0])){
                    $this->desc = $args[0];
                } else {
                    $this->desc = $args[0]['desc'];
                }
            }else{
                $this->desc ='';
            }
        } else {
            $this->desc = $actCode;
        }
    }
}
