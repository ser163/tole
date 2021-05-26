<?php
declare (strict_types = 1);

namespace app\site\controller;

use app\ExController;

class IndexController extends ExController
{
    public function index()
    {
        return '<h1 style="position: absolute;left: 38.2%; top:38.2%;">
                        <a href="https://github.com/ser163/tole"
                        style="text-decoration:none;color: #657180;"> 
                        密码树洞(Tole) v 0.0.1
                        </a>
                 </h1>';
    }
}
