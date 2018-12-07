<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/6
 * Time: 16:14
 */

namespace Utopia;


class Di
{
    private $servers;

    /**
     * 加入
     *
     * @param $name
     * @param $definition
     */
    public function set($name, $definition)
    {
        $this->servers[$name] = $definition;
    }

    /**
     * 移除
     *
     * @param $name
     */
    public function remove($name)
    {
        unset($this->servers[$name]);
    }

    /**
     * 新建
     *
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->servers[$name];
    }

    /**
     * Check whether the DI contains a service by a name
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->servers[$name]);
    }
}