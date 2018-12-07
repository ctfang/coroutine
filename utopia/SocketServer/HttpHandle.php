<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/4
 * Time: 14:47
 */

namespace Utopia\SocketServer;

use Apps\Http\Middlewares\ExceptionMiddleware;
use Utopia\Http\Middlewares\ResponseFactoryMiddleware;
use Utopia\Http\Relay;
use Utopia\Socket\Connect\SocketConnect;
use Utopia\Socket\Http\ServerResponse;

/**
 * http清除处理
 * @package Utopia\SocketServer
 */
class HttpHandle extends \Utopia\Socket\Http\HttpHandle
{
    /** @var Relay */
    protected $relay;

    /** @var array */
    protected $defaultMiddleware = [];

    /**
     * HttpHandle constructor.
     */
    public function __construct()
    {
        $this->defaultMiddleware = [
            new ExceptionMiddleware(),
        ];
    }

    /**
     * @param SocketConnect $connect
     * @param \Utopia\Socket\Http\ServerRequest $serverRequest
     * @return mixed|void
     */
    public function handle(SocketConnect $connect, $serverRequest)
    {
        $this->connect  = $connect;

        $queue   = $this->defaultMiddleware;
        $queue[] = new ResponseFactoryMiddleware();
        $relay = new Relay($queue);

        /** @var ServerResponse $response */
        $response = $relay->handle($serverRequest);
        $this->writeResponse($serverRequest, $response);
    }
}