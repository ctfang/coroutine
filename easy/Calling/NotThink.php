<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/2
 * Time: 15:14
 */

namespace Easy\Calling;


use Easy\Coroutines\CoroutineTaskInterface;
use Easy\Scheduler;

class NotThink extends SchedulerCall
{
    public function __invoke(CoroutineTaskInterface $task, Scheduler $scheduler)
    {

    }
}