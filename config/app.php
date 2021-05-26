<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------
// 自定义常量
// 提示成功
define('TIP_SUCC', 0);
// 提示警告
define('TIP_WARN', 1);
// 提示错误
define('TIP_ERROR', 1);

// 系统配置
return [
    'app_debug' => env('APP_DEBUG', false),
    // 应用地址
    'app_host' => env('app.host', ''),
    // 应用的命名空间
    'app_namespace' => '',
    // 是否启用路由
    'with_route' => true,
    // 默认应用
    'default_app' => 'site',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    'app_map' => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind' => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list' => [],

    // 异常页面的模板文件
    'exception_tmpl' => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message' => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg' => false,
    // 网站绑定域名
    'site_url'=>env('app.site_url', 'http://www.pw.com'),
];
