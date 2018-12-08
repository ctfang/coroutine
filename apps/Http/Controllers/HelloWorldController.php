<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/10/17
 * Time: 11:27
 */

namespace Apps\Http\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Utopia\Annotations\RequestMapping;

/**
 * @package Apps\Http\Controllers
 */
class HelloWorldController
{
    /**
     * 全局路由
     * @RequestMapping("/")
     * @param ServerRequestInterface $request
     * @return array
     */
    public function helloWorld(ServerRequestInterface $request)
    {
        return ['time' => time(), 'query' => $request->getQueryParams(), 'parsed' => $request->getParsedBody()];
    }
}