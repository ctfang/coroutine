<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/8
 * Time: 15:30
 */

namespace Utopia\Providers;

use Doctrine\Common\Annotations\AnnotationReader;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use ReflectionClass;
use Utopia\Annotations\RequestMapping;
use Utopia\Helper\DirectoryHelper;
use Utopia\Http\Middlewares\Middleware;
use Utopia\Services\MiddlewareService;

/**
 * 路由
 * Class RouteServiceProvider
 * @package Utopia\Providers
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * 用来做控制器的命名
     * @var array
     */
    protected $namespaces = [
        "Apps\\Http\\Controllers\\",
    ];

    private $dispatcher;

    private $routes;

    private $requestMappingAnnotations;

    private $middleware = [];

    /**
     * 所有 register() 都执行完之后执行 boot()
     * @return mixed
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function boot()
    {
        $routeParser   = new Std();
        $dataGenerator = new GroupCountBased();
        $midService    = new MiddlewareService();
        /** @var RouteCollector $route */
        $route = new RouteCollector($routeParser, $dataGenerator);
        $this->scanRoute($route, $midService);

        $this->bindService('route', new Dispatcher($route->getData()));
        $this->bindService('middleware', $midService);
    }

    /**
     * @param RouteCollector $route
     * @param MiddlewareService $middlewareService
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function scanRoute(RouteCollector $route, MiddlewareService $middlewareService)
    {
        $directory = new DirectoryHelper($this->namespaces);
        $reader    = new AnnotationReader();
        foreach ($directory->scanClass() as $class) {
            if (class_exists($class)) {
                $reflectionClass  = new ReflectionClass($class);
                $classAnnotations = $reader->getClassAnnotations($reflectionClass);

                $midQueue = [];
                /**
                 * 类注释，中间件，前缀
                 */
                foreach ($classAnnotations AS $annotation) {
                    if ($annotation instanceof Middleware) {
                        $midQueue[] = $annotation;
                    }
                }
                $controller = new $class();
                $middlewareService->bindController(get_class($controller), $midQueue);
                /**
                 * 路由详情
                 */
                foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                    $methodAnnotations = $reader->getMethodAnnotations($reflectionMethod);
                    /** @var RequestMapping $annotation */
                    foreach ($methodAnnotations AS $annotation) {
                        $route->addRoute(
                            $annotation->getMethod(),
                            $annotation->getRoute(),
                            [$controller, $reflectionMethod->getName()]
                        );
                    }
                }
            }
        }
    }
}