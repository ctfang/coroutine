<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/5
 * Time: 11:48
 */

namespace Apps\Http\Middlewares;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExceptionMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try{
            return $handler->handle($request);
        }catch (\Error $error){
            dump($error);
        }catch (\Exception $exception){
            dump($exception);
        }
    }
}