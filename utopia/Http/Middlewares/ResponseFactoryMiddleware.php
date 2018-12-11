<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/4
 * Time: 15:23
 */

namespace Utopia\Http\Middlewares;


use FastRoute\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Utopia\Application;
use Utopia\Exception\ResponseException;
use Utopia\Socket\Http\ServerResponse;

/**
 * Class ResponseFactoryMiddleware
 * @package Utopia\Http\Middlewares
 */
class ResponseFactoryMiddleware implements MiddlewareInterface
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
     * @throws ResponseException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteCollector $dispatcher */
        $dispatcher = Application::get('route');
        $routeInfo  = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

        if ($routeInfo[2]) {
            $queryParams = $request->getQueryParams();
            $queryParams = array_merge($queryParams, $routeInfo[2]);
            $request     = $request->withQueryParams($queryParams);
        }

        $serverResponse = call_user_func_array($routeInfo[1], [$request]);

        if (is_array($serverResponse)) {
            $serverResponse = $this->getJsonResponse($serverResponse);
        } elseif (is_string($serverResponse)) {
            $serverResponse = $this->getTextResponse($serverResponse);
        } elseif (!$serverResponse) {
            $serverResponse = $this->getTextResponse($serverResponse);
        } elseif (!$serverResponse instanceof ServerResponse) {
            throw new ResponseException('错误的返回');
        }

        return $serverResponse;
    }

    private function getJsonResponse($array)
    {
        return new ServerResponse(
            200,
            array(
                'Content-Type' => 'application/json',
            ),
            json_encode($array)
        );
    }

    private function getTextResponse($string)
    {
        return new ServerResponse(
            200,
            array(
                'Content-Type' => 'text/plain',
            ),
            $string
        );
    }
}