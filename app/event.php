<?php
// 事件定义文件
return [
    'bind' => [
        'UserAction' => 'app\event\UserAction',
    ],

    'listen' => [
        'AppInit' => [],
        'HttpRun' => [],
        'HttpEnd' => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'UserLogin' => ['app\listener\UserLogin'],
        'UserAction' => ['app\listener\UserAction'],
        'RoleAdd' => ['app\listener\RoleAdd'],
        'ClearCache' => ['app\listener\ClearCache'],
    ],

    'subscribe' => [
        'app\subscribe\User',
    ],
];
