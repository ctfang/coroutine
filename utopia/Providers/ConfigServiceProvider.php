<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/6
 * Time: 17:15
 */

namespace Utopia\Providers;

use Utopia\Application;
use Utopia\Services\ConfigService;

/**
 * 总配置引导
 * @package Utopia\Providers
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * @return mixed|void
     */
    public function register()
    {
        $config = new ConfigService(Application::getRootPath('/config'));
        $this->bindService('config',$config);
    }

    /**
     * 所有 register() 都执行完之后执行 boot()
     * @return mixed
     */
    public function boot()
    {
        // TODO: Implement boot() method.
    }
}