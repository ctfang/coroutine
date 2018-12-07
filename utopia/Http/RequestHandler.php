<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/4
 * Time: 15:57
 */

namespace Utopia\Http;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Traversable;
use TypeError;

abstract class RequestHandler implements RequestHandlerInterface
{
    protected $queue = [];

    /**
     *
     * Constructor.
     *
     * @param array|Traversable $queue A queue of middleware entries.
     * instances.
     *
     */
    public function __construct($queue)
    {
        if (!is_iterable($queue)) {
            throw new TypeError('\$queue must be array or Traversable.');
        }

        $this->queue = $queue;
    }


    /**
     *
     * Handles the current entry in the middleware queue and advances.
     *
     * @param ServerRequestInterface $request The request.
     *
     * @return ResponseInterface
     *
     */
    abstract public function handle(ServerRequestInterface $request) : ResponseInterface;
}