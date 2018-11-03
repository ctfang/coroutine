<?php

use Easy\Coroutines\CoroutineTask;
use Easy\Scheduler;

/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/6
 * Time: 23:10
 */

/**
 * 获取当前协程
 *
 * @return int
 */
function getCoroutineId(): int
{
    return Scheduler::getRunningCid();
}

/**
 * 获取当前父协程
 *
 * @return int
 */
function getParentCoroutineId(): int
{
    return Scheduler::getRunningPCid();
}

/**
 * 异步协程执行闭包逻辑
 *
 * @param callable $callback
 * @return int
 */
function go(callable $callback):int
{
    return \Easy\Coroutine::go($callback);
}

/**
 * 模拟协程睡眠
 *
 * yield co_sleep(2);
 *
 * @param int $second 单位秒
 * @return \Easy\Calling\NotThink
 */
function co_sleep(int $second)
{
    /**
     * 暂停协程本身
     * 创建子协程，用来唤醒自己
     */
    $scheduler  = \Easy\Coroutine::getScheduler();
    $waitingCid = $scheduler::getRunningCid();
    go(
        function () use ($second, $waitingCid) {
            $scheduler = \Easy\Coroutine::getScheduler();
            $cid       = $scheduler::getRunningPCid();
            $scheduler->waiting($waitingCid, $cid);
            $intStopTime = time() + $second;
            while ($intStopTime >= time()) {
                yield;
            }
            $scheduler->recover($waitingCid, $cid);
        }
    );
    return new \Easy\Calling\NotThink();
}