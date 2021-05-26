<?php

use think\facade\Route;

// 获取类别数据
Route::post('cate/getAllData', 'Cate/getAllData')
    ->middleware('auth', ['cate/getAllData', 'read']);






