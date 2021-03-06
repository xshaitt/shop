<?php
return [
    'title' => "上海黑之白科技",
    'description' => "APi接口文档",
    'template' => 'grape', // 苹果绿:apple 葡萄紫:grape
    'class' => [
        'api' => [
            'App\Http\Controllers\Api\IndexController',
            'App\Http\Controllers\Api\UserController',
            'App\Http\Controllers\Api\GoodsController',
            'App\Http\Controllers\Api\AddressController',
            'App\Http\Controllers\Api\CartController',
            'App\Http\Controllers\Api\OrderController',
        ],
    ],
];