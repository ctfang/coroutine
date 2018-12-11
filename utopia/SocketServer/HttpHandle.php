<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/4
 * Time: 14:47
 */

namespace Utopia\SocketServer;

use Apps\Http\Middlewares\ExceptionMiddleware;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use RingCentral\Psr7\Uri;
use Utopia\Application;
use Utopia\Exception\NotAllowedException;
use Utopia\Exception\NotFoundException;
use Utopia\Http\Middlewares\ResponseFactoryMiddleware;
use Utopia\Http\Relay;
use Utopia\Services\ConfigService;
use Utopia\Services\MiddlewareService;
use Utopia\Socket\Connect\SocketConnect;
use Utopia\Socket\Http\ServerResponse;

/**
 * http清除处理
 * @package Utopia\SocketServer
 */
class HttpHandle extends \Utopia\Socket\Http\HttpHandle
{
    /** @var RouteCollector */
    protected $dispatcher;
    /** @var MiddlewareService */
    protected $middleware;

    /** @var array 系统中间件 */
    protected $defaultMiddleware = [];

    /**
     * HttpHandle constructor.
     */
    public function __construct()
    {
        $this->defaultMiddleware = [
            new ExceptionMiddleware(),
        ];

        $this->dispatcher = Application::get('route');
        $this->middleware = Application::get('middleware');
    }

    /**
     * @param SocketConnect $connect
     * @param \Utopia\Socket\Http\ServerRequest $serverRequest
     * @return mixed|void
     * @throws NotAllowedException
     * @throws NotFoundException
     */
    public function handle(SocketConnect $connect, $serverRequest)
    {
        $this->connect = $connect;
        $queue         = $this->defaultMiddleware;
        $routeInfo     = $this->dispatcher->dispatch($serverRequest->getMethod(), $serverRequest->getUri()->getPath());
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $serverRequest = $serverRequest->withMethod('GET');
                $serverRequest = $serverRequest->withUri(new Uri('404'));
                $routeInfo     = $this->dispatcher->dispatch($serverRequest->getMethod(), $serverRequest->getUri()->getPath());
                if( in_array($routeInfo[0],[Dispatcher::NOT_FOUND,Dispatcher::METHOD_NOT_ALLOWED]) ){
                    throw new NotAllowedException('必须设置404路由');
                }
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $serverRequest = $serverRequest->withMethod('GET');
                $serverRequest = $serverRequest->withUri(new Uri('403'));
                $routeInfo     = $this->dispatcher->dispatch($serverRequest->getMethod(), $serverRequest->getUri()->getPath());
                if( in_array($routeInfo[0],[Dispatcher::NOT_FOUND,Dispatcher::METHOD_NOT_ALLOWED]) ){
                    throw new NotAllowedException('必须设置403路由');
                }
                break;
        }
        $handler = $routeInfo[1];
        foreach ($this->middleware->getMid(get_class($handler[0])) as $mid) {
            $queue[] = $mid;
        }
        $queue[] = new ResponseFactoryMiddleware();
        $relay   = new Relay($queue);

        /** @var ServerResponse $response */
        $response = $relay->handle($serverRequest);
        $this->writeResponse($serverRequest, $response);
    }
}