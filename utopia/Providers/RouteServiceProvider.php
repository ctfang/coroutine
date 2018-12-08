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
use Utopia\Annotations\Middleware;
use Utopia\Annotations\Middlewares;
use Utopia\Annotations\RequestMapping;
use Utopia\Application;
use Utopia\Helper\DirectoryHelper;
use Utopia\Helper\RouteHelper;

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

        /** @var RouteCollector $route */
        $route = new RouteCollector($routeParser, $dataGenerator);
        foreach ($this->scanRoute() as $arr) {
            /** @var RequestMapping $requestMapping */
            $requestMapping = $arr[0];
            /** @var RouteHelper $routeHelper */
            $routeHelper = $arr[1];
            $route->addRoute($requestMapping->getMethod(), $requestMapping->getRoute(), $routeHelper);
        }

        $dispatcher = new Dispatcher($route->getData());

        $this->bindService('route',$dispatcher);
    }

    /**
     * @return \Generator
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function scanRoute()
    {
        $loader    = Application::getLoader();
        $directory = new DirectoryHelper();
        $directory->setLoader($loader);
        $directory->setScanNamespace($this->namespaces);
        $reader = new AnnotationReader();
        foreach ($directory->scanClass() as $class) {
            if (class_exists($class)) {
                $reflectionClass  = new ReflectionClass($class);
                $classAnnotations = $reader->getClassAnnotations($reflectionClass);

                $prefix = "";
                $queue  = [];
                foreach ($classAnnotations AS $annotation) {
                    if ($annotation instanceof Middleware) {
                        $queue[] = $annotation->getClass();;
                    } elseif ($annotation instanceof Middlewares) {
                        /** @var Middleware $middleware */
                        foreach ($annotation->getMiddlewares() as $middleware) {
                            $queue[] = $middleware->getClass();
                        }
                    }
                }

                foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                    $methodAnnotations = $reader->getMethodAnnotations($reflectionMethod);

                    foreach ($methodAnnotations AS $annotation) {
                        $key = "{$prefix}/{$class}/{$reflectionMethod->getName()}";

                        if (!isset($this->routes[$key])) {
                            $routeHelper = new RouteHelper();
                            foreach ($queue as $mid) {
                                $routeHelper->addMiddleware($this->getMiddleware($mid));
                            }
                            $this->routes[$key] = $routeHelper;
                        } else {
                            /** @var RouteHelper $routeHelper */
                            $routeHelper = $this->routes[$key];
                        }

                        if ($annotation instanceof RequestMapping) {
                            if (!$annotation->getRoute()) {
                                $tem_1          = explode("\\", $class);
                                $controllerName = end($tem_1);
                                $tem_2          = explode('Controller', $controllerName);
                                $className      = reset($tem_2);
                                $className      = strtolower($className);
                                $annotation->setRoute("{$prefix}/{$className}/{$reflectionMethod->getName()}");
                            }
                            $routeHelper->setClosure([new $class(), $reflectionMethod->getName()]);
                            $this->requestMappingAnnotations[$key] = $annotation;
                        } elseif ($annotation instanceof Middleware) {
                            $mid = $annotation->getClass();
                            $routeHelper->addMiddleware($this->getMiddleware($mid));
                        } elseif ($annotation instanceof Middlewares) {
                            /** @var Middleware $middleware */
                            foreach ($annotation->getMiddlewares() as $middleware) {
                                $mid = $middleware->getClass();
                                $routeHelper->addMiddleware($this->getMiddleware($mid));
                            }
                        }

                        $this->routes[$key] = $routeHelper;
                    }
                }
            }
        }

        unset($this->middleware);

        if($this->routes){
            foreach ($this->routes as $key => $routeHelper) {
                if (!isset($this->requestMappingAnnotations[$key])) break;
                yield [$this->requestMappingAnnotations[$key], $routeHelper];
            }
        }
    }

    private function getMiddleware($mid)
    {
        if( !isset($this->middleware[$mid]) ){
            $this->middleware[$mid] = new $mid();
        }
        return $this->middleware[$mid];
    }
}