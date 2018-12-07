<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/5
 * Time: 11:49
 */
return [
    'http'=>[
        /**
         * 默认加载的中间件
         * 按顺序执行
         */
        'middleware'=>[
            \Apps\Http\Middlewares\ExceptionMiddleware::class,
        ],
    ],
];