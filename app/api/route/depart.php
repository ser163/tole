<?php

use think\facade\Route;

// 获取树形数据
Route::get('depart/getTreeData', 'Depart/getTreeData')
    ->middleware('auth', ['depart/getTreeData', 'read']);
// 过滤当前id获取可以使用的部门
Route::post('depart/getDepartData', 'Depart/getDepartData')
    ->middleware('auth', ['depart/getDepartData', 'read']);

// 获取全部部门
Route::post('depart/getAllDepart', 'Depart/getAllDepart')
    ->middleware('auth', ['depart/getAllDepart', 'read']);

// 添加部门
Route::post('depart/addDepart', 'Depart/addDepart')
    ->middleware('auth', ['depart/addDepart', 'write']);

// 修改部门
Route::post('depart/updateDepart', 'Depart/updateDepart')
    ->middleware('auth', ['depart/updateDepart', 'write']);
// 删除部门接口
Route::post('depart/deleteDepart', 'Depart/deleteDepart')
    ->middleware('auth', ['depart/deleteDepart', 'write']);






