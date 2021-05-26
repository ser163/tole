<?php

return [
    // 默认磁盘
    'default' => env('filesystem.driver', 'local'),
    // 磁盘列表
    'disks' => [
        'local' => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // 磁盘类型
            'type' => 'local',
            // 磁盘路径
            'root' => app()->getRootPath() . 'public/storage',
            // 磁盘路径对应的外部URL路径
            'url' => '/storage',
            // 可见性
            'visibility' => 'public',
        ],
        // 更多的磁盘配置信息
        // oss 配置
        'oss' => [
            'type' => 'oss',
            'prefix' => '',
            'access_key' => '',
            'secret_key' => '',
            'end_point' => '', // ssl：https://iidestiny.com
            'bucket' => '',
            'is_cname' => true
        ],
        // 七牛配置
        'qiniu' => [
            'type' => 'qiniu',
            'access_key' => '',
            'secret_key' => '',
            'bucket' => '',
            'domain' => '',
        ],
        // 腾讯云配置
        'qcloud' => [
            'type' => 'qcloud',
            'region' => '',
            'credentials' => [
                'appId' => '',// 域名中数字部分
                'secretId' => '',
                'secretKey' => '',
            ],
            'bucket' => 'test',
            'timeout' => 60,
            'connect_timeout' => 60,
            'cdn' => '您的 CDN 域名',
            'scheme' => 'https',
            'read_from_cdn' => false,
        ]
    ],
];
