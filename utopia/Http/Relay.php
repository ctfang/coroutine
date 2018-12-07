<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/4
 * Time: 15:29
 */

namespace Utopia\Http;


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

class Relay extends RequestHandler
{
    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $runner = new Runner($this->queue);
        return $runner->handle($request);
    }

    public function pushQueue(MiddlewareInterface $middleware)
    {
        $this->queue[] = $middleware;
    }
}