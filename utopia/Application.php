<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/6
 * Time: 11:43
 */

namespace Utopia;


use Composer\Autoload\ClassLoader;
use Dotenv\Dotenv;
use Utopia\Providers\ConfigServiceProvider;
use Utopia\Providers\ServiceProvider;
use Utopia\Services\ConfigService;

class Application
{
    public static $runUtopia = false;

    /** @var Di */
    protected static $di;
    /** @var bool|string 根目录 */
    protected static $rootDirectory;
    /** @var ClassLoader 加载器 */
    protected static $loader;

    /**
     * Application constructor.
     * @param string $dir
     */
    public function __construct(string $dir)
    {
        self::$di = new Di();

        self::$rootDirectory = realpath($dir);

        if (file_exists(self::$rootDirectory.'/.env')) {
            (new Dotenv(self::$rootDirectory))->load();
        }
    }

    /**
     * 获取根目录地址
     * @param $path
     * @return bool|string
     */
    public static function getRootPath($path)
    {
        return realpath(self::$rootDirectory.$path);
    }

    /**
     * 设置类加载器
     * @param ClassLoader $loader
     */
    public static function setLoader(ClassLoader $loader)
    {
        self::$loader = $loader;
    }

    /**
     * @return ClassLoader
     */
    public static function getLoader()
    {
        return self::$loader;
    }

    /**
     * 获取service对象
     * @param string $name
     * @return mixed
     */
    public static function get(string $name)
    {
        return self::$di->get($name);
    }

    /**
     * 是否存在服务
     * @param string $name
     * @return mixed
     */
    public static function has(string $name)
    {
        return self::$di->has($name);
    }

    /**
     * 加载配置
     */
    public function loadBaseConfig()
    {
        $configServiceProvider = $this->registerServices(new ConfigServiceProvider(self::$di));
        $configServiceProvider->boot();
    }

    /**
     * 服务引到加载
     */
    public function loadServiceProvider()
    {
        /** @var ConfigService $config */
        $config     = self::get('config');
        $providers  = $config->get('providers');
        if( $providers ){
            $serviceProviders = [];
            foreach ($providers as $providerClassName){
                $serviceProviders[] = $this->registerServices(new $providerClassName(self::$di));
            }
            foreach ($serviceProviders as $serviceProvider){
                $serviceProvider->boot();
            }
        }
    }

    /**
     * 注册一个服务
     * @param $service
     * @return ServiceProvider
     */
    private function registerServices(ServiceProvider $service):ServiceProvider
    {
        /** @var ServiceProvider $service */
        $service = new $service(self::$di);
        $service->register();
        return $service;
    }

    /**
     * 运行入口
     */
    public function run()
    {
        self::get('console')->run();

        if( self::$runUtopia ){
            /** @var Utopia $utopia */
            $utopia = Application::get('utopia');
            $utopia->run();
        }
    }
}