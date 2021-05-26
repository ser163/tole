<?php

use think\facade\Route;

// 查看规则列表
Route::post('rule/getAllRules', 'Rule/index')
    ->middleware('auth', ['rule/getAllRules', 'read']);
// 添加规则
Route::post('rule/addRules', 'Rule/save')
    ->middleware('auth', ['rule/addRules', 'write']);
// 编辑规则
Route::post('rule/editRules', 'Rule/update')
    ->middleware('auth', ['rule/editRules', 'write']);

// 删除规则
Route::post('rule/deleteRules', 'Rule/delete')
    ->middleware('auth', ['rule/deleteRules', 'write']);



