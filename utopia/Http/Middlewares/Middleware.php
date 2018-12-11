<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/11
 * Time: 11:58
 */

namespace Utopia\Http\Middlewares;


use Psr\Http\Server\MiddlewareInterface;

/**
 * 注解时，提示
 *
 * @Annotation
 * @Target("CLASS")
 */
abstract class Middleware implements MiddlewareInterface
{
    /**
     * 返回不需要生效的控制器
     *
     * 返回控制器数组
     * 执行控制器时，跳过中间件
     *
     * @return array
     */
    public function exception():array
    {
        return [];
    }
}