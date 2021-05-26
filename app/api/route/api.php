<?php

use think\facade\Route;

// 首页统计信息
Route::post('home/info', 'Index/info')
    ->middleware('auth', ['home/info', 'read']);

/**
 *  用户部分
 */
// 登录接口
Route::post('user/login', 'User/login');
// 获取所有用户
Route::post('user/getAllUser', 'User/getAllUser')
    ->middleware('auth', ['user/getAllUser', 'read']);
// 获取所有角色
Route::post('user/getAllRole', 'User/getAllRole')
    ->middleware('auth', ['user/getAllRole', 'read']);
// 增加用户
Route::post('user/addUser', 'User/addUser')
    ->middleware('auth', ['user/addUser', 'read']);
// 修改用户
Route::post('user/setUserInfo', 'User/setUserInfo')
    ->middleware('auth', ['user/setUserInfo', 'write']);
// 设置启用用户
Route::post('user/setUserEnable', 'User/setUserEnable')
    ->middleware('auth', ['user/setUserEnable', 'write']);
// 获取用户所有角色
Route::post('user/getUserRoles', 'User/getUserRoles')
    ->middleware('auth', ['user/getUserRoles', 'read']);
// 修改用户密码
Route::post('user/setUserPassWord', 'User/setUserPassWord')
    ->middleware('auth', ['user/setUserPassWord', 'write']);
// 测试监听事件testEven
Route::any('user/testEven', 'User/testEven');
// 用户资源路由
//Route::resource('user', 'User');
// 用户权限

// 获取用户所有角色
Route::post('role/getAllRoles', 'Role/index')
    ->middleware('auth', ['role/getAllRoles', 'read']);
// 添加角色接口
Route::post('role/addRoles', 'Role/save')
    ->middleware('auth', ['role/getAllRoles', 'read']);
// 修改角色
Route::post('role/editRoles', 'Role/update')
    ->middleware('auth', ['role/editRoles', 'write']);
// 删除角色
Route::post('role/deleteRoles', 'Role/delete')
    ->middleware('auth', ['role/deleteRoles', 'write']);
// 获取角色内用户
Route::post('role/getRoleInUser', 'Role/getRoleInUser')
    ->middleware('auth', ['role/getRoleInUser', 'read']);
// 删除角色内的用户
Route::post('role/deleteUserOnRoles', 'Role/deleteUserOnRoles')
    ->middleware('auth', ['role/deleteUserOnRoles', 'write']);
// 获取所有用户，并把角色内的用户禁用
Route::post('role/getUserAllInRoles', 'Role/getUserAllInRoles')
    ->middleware('auth', ['role/getUserAllInRoles', 'read']);
// 添加用户到角色中
Route::post('role/joinRoles', 'Role/joinRoles')
    ->middleware('auth', ['role/joinRoles', 'write']);

/**
 *  其它部分
 */
// 查看所有logs
Route::post('logs/getAllUserLogs', 'Logs/index')
    ->middleware('auth', ['logs/getAllUserLogs', 'read']);
// 查看历史记录
Route::post('history/getAllHistory', 'History/index')
    ->middleware('auth', ['history/getAllHistory', 'read']);



