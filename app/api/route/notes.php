<?php

use think\facade\Route;

// 获取类别数据
Route::post('notes/getUserRoles', 'Notes/getUserRoles')
    ->middleware('auth', ['notes/getUserRoles', 'read']);
// 添加notes 数据
Route::post('notes/addNotes', 'Notes/save')
    ->middleware('auth', ['notes/addNotes', 'write']);
// 获取所有条目
Route::post('notes/getAllNotes', 'Notes/index')
    ->middleware('auth', ['notes/getAllNotes', 'read']);
// 获取notes下的角色数据
Route::post('notes/getNotesRoles', 'Notes/getNotesRoles')
    ->middleware('auth', ['notes/getNotesRoles', 'read']);
// 密码验证
Route::post('notes/checkPassWord', 'Notes/checkPassWord')
    ->middleware('auth', ['notes/checkPassWord', 'read']);
// 修改主条目
Route::post('notes/updateNotes', 'Notes/update')
    ->middleware('auth', ['notes/updateNotes', 'write']);
// 主条目删除
Route::post('notes/delete', 'Notes/delete')
    ->middleware('auth', ['notes/delete', 'write']);





