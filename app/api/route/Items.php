<?php

use think\facade\Route;

// 通过notes_id 获取items数据
Route::post('items/getItems/:id', 'Items/getItems')
    ->middleware('auth', ['read']);

// 增加密码附加条目
Route::post('items/addItems', 'Items/addItems')
    ->middleware('auth', ['items/addItems', 'write']);

// 删除items
Route::post('items/delete', 'Items/delete')
    ->middleware('auth', ['items/delete', 'write']);

// 获取详情item详情
Route::post('items/desc/:id/:hash', 'Items/ItemInfo')
    ->middleware('auth', ['read']);

// 修改item
Route::put('items/desc/:id/:hash', 'Items/update')
    ->middleware('auth', ['write']);






