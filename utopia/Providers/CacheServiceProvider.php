<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/8
 * Time: 14:50
 */

namespace Utopia\Providers;

use Doctrine\Common\Cache\FilesystemCache;

/**
 * 缓存
 * Class CacheServiceProvider
 * @package Utopia\Providers
 */
class CacheServiceProvider extends ServiceProvider
{
    /**
     * 所有 register() 都执行完之后执行 boot()
     * @return mixed
     */
    public function boot()
    {
        $directory = $this->getConfig('cache');

        $cache = new FilesystemCache($directory);

        $this->bindService('cache',$cache);
    }
}