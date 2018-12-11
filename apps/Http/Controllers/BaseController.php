<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/11
 * Time: 15:47
 */

namespace Apps\Http\Controllers;


use Utopia\Annotations\RequestMapping;

class BaseController
{
    /**
     * 404请求会重定向到这里
     * @RequestMapping("404")
     */
    public function notFound()
    {
        return '404 NOT FOUND';
    }

    /**
     * 非法访问（未定义的请求类型）重定向到这里
     * @RequestMapping("403")
     */
    public function methodNotAllowed()
    {
        return '403 METHOD NOT ALLOWED';
    }
}