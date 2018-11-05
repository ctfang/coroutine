<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/11
 * Time: 17:11
 */

namespace Utopian\Coroutines;

use Utopian\Http\Server\Request;
use Utopian\Http\Server\Response;
use Utopian\Http\Stream\StringStream;

class HttpCoroutine implements ServerInterface
{
    /**
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $response = new Response();
        $body     = new StringStream("hello world");
        $response = $response->withBody($body);

        return $response;
    }
}