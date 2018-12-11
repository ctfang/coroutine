<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/11
 * Time: 11:18
 */

namespace Utopia\Services;

/**
 * 中间件管理
 * @package Utopia\Services
 */
class MiddlewareService
{
    private $midMap = [];

    public function bindController(string $controllerName, array $midMap)
    {
        $this->midMap[$controllerName] = $midMap;
    }

    public function getMid(string $controllerName)
    {
        return $this->midMap[$controllerName];
    }
}