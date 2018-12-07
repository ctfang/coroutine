<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/7
 * Time: 21:07
 */

namespace Utopia\Providers;


use Utopia\Co\Scheduler;
use Utopia\Utopia;

class UtopiaLoopServiceProvider extends ServiceProvider
{
    /**
     * 所有 register() 都执行完之后执行 boot()
     * @return mixed
     */
    public function boot()
    {
        $utopia = new Utopia();
        /**
         * 设置协程调度
         */
        $utopiaCoroutines = new Scheduler();
        $utopia->setCoroutines($utopiaCoroutines);

        $this->bindService('utopia', $utopia);
    }
}