<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/6
 * Time: 22:54
 */

namespace Utopian\Calling;


use Utopian\Coroutines\CoroutineTaskInterface;
use Utopian\Scheduler;

abstract class SchedulerCall
{
    protected $callback;

    abstract public function __invoke(CoroutineTaskInterface $task, Scheduler $scheduler);
}