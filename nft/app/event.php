<?php
// 事件定义文件
return [
    'bind' => [
    ],

    'listen' => [
        'AppInit'  => [
            \app\event\AppInit::class
        ],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
    ],

    'subscribe' => [
    ],
];
