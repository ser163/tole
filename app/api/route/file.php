<?php

use think\facade\Route;

// 上传文件接口
Route::post('file/upload', 'File/upload');

// 上传图片接口
Route::post('file/imageUpload', 'File/imageUpload');
// 文件下载接口
Route::get('file/fileDown/:id', 'File/fileDown');

// 图片下载接口
Route::get('file/imageDown/:id', 'File/imageDown');





