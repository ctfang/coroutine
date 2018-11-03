<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/6
 * Time: 22:54
 */

namespace Easy\Calling;


use Easy\Coroutines\CoroutineTaskInterface;
use Easy\Scheduler;

abstract class SchedulerCall
{
    protected $callback;

    abstract public function __invoke(CoroutineTaskInterface $task, Scheduler $scheduler);
}