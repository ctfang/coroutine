<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/5
 * Time: 18:18
 */

return [
    /**
     * 缓存配置
     */
    'cache'=>__DIR__.'/../runtime/cache',

    /**
     * 加载引导
     */
    'providers'=>[
        \Utopia\Providers\ConsoleServiceProvider::class,
        \Utopia\Providers\UtopiaLoopServiceProvider::class,
        \Utopia\Providers\RouteServiceProvider::class,
        \Utopia\Providers\CacheServiceProvider::class,
    ],
];