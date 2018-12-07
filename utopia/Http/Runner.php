<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/4
 * Time: 15:56
 */

namespace Utopia\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 *
 * A PSR-15 request handler.
 *
 * @package relay/relay
 *
 */
class Runner extends RequestHandler
{
    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = current($this->queue);
        next($this->queue);

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }

        return $middleware($request, $this);
    }
}