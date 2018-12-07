<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/5
 * Time: 16:57
 */

namespace Utopia\Providers;


use Utopia\Di;

abstract class ServiceProvider
{
    /** @var Di  */
    protected $di;

    /**
     * ServiceProvider constructor.
     * @param Di $di
     */
    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    /**
     * @param string $name
     * @param object $service
     */
    public function bindService(string $name,$service)
    {
        $this->di->set($name,$service);
    }

    /**
     * 按顺序执行 register() 不能依赖其他 Servier Provider
     * @return mixed
     */
    public function register()
    {

    }

    /**
     * 所有 register() 都执行完之后执行 boot()
     * @return mixed
     */
    abstract public function boot();
}