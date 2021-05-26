<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\ExController;
use Psr\SimpleCache\InvalidArgumentException;
use think\facade\Log;
use think\View;

class IndexController extends ExController
{
    public function index(): \think\response\View
    {
        return view('index');
    }
}
