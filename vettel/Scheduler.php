<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/6
 * Time: 22:56
 */

namespace Vettel;


use Vettel\Calling\SchedulerCall;
use Vettel\Coroutines\CoroutineTask;
use Vettel\Coroutines\CoroutineTaskInterface;
use Vettel\Socket\ConnectAbstract;

class Scheduler
{
    /** @var array 正在执行的协程 */
    protected $runningCoroutine = [];
    /** @var array 进入等待的协程 */
    protected $waitingCoroutine = [];
    /** @var array 等待协程发起者，发起者结束时或主动恢复时，等待协程才可以恢复 */
    protected $callWaitingCoroutine = [];

    /** @var \SplQueue 所有协程 */
    protected $coroutineQueue;

    /** @var array 原始连接 */
    protected $socketMap = [];
    /** @var array 原始连接 绑定 处理类 */
    protected $socketBindConnectMap = [];
    /** @var array id=>ConnectInterface 已经连接的客户端，待读 */
    protected $socketWaitingForRead = [];
    /** @var array id=>ConnectInterface 已经连接的客户端，待写入 */
    protected $socketWaitingForWrite = [];

    /** @var int */
    protected static $cid = 0;
    /** @var int */
    protected static $pcid = 0;

    public function __construct()
    {
        $this->coroutineQueue = new \SplQueue();
    }

    /**
     * 获取正在执行协程
     * @return int
     */
    public static function getRunningCid()
    {
        return self::$cid;
    }

    /**
     * 获取正在执行父级协程
     * @return int
     */
    public static function getRunningPCid()
    {
        return self::$pcid;
    }

    /**
     * 创建一个协程
     * @param \Generator $coroutine
     * @return int
     */
    public function newCoroutine(\Generator $coroutine)
    {
        $cid                          = Coroutine::newCoroutineId();
        $pcid                         = self::$cid;
        $task                         = new CoroutineTask($cid, $pcid, $coroutine);
        $this->runningCoroutine[$cid] = $task;
        $this->schedule($task);

        return $cid;
    }

    /**
     * 开启监听协程
     * @param ConnectAbstract $server
     * @param string $host
     */
    public function monitor(ConnectAbstract $server, string $host = '0.0.0.0:8080')
    {
        $socket = stream_socket_server("tcp://{$host}", $errNo, $errStr);
        stream_set_blocking($socket, 0);

        $socketId = (int)$socket;
        /** resource */
        $this->socketMap[$socketId]            = $socket;
        $this->socketBindConnectMap[$socketId] = $server;
    }

    /**
     * 放到执行栈
     * @param CoroutineTaskInterface $task
     */
    public function schedule(CoroutineTaskInterface $task)
    {
        $this->coroutineQueue->enqueue($task);
    }

    /**
     * 是否还有正在运行的协程栈
     * @return bool
     */
    public function hasRunning(): bool
    {
        return !$this->coroutineQueue->isEmpty();
    }

    /**
     * 循环监控
     */
    public function run()
    {
        Coroutine::setScheduler($this);

        while (true) {
            /** 协程执行 */
            if (!$this->coroutineQueue->isEmpty()) {
                /** @var CoroutineTaskInterface $task */
                $task = $this->coroutineQueue->dequeue();

                self::$cid  = $task->getCoroutineId();
                self::$pcid = $task->getParentCoroutineId();

                $return = $task->run();

                if ($return instanceof SchedulerCall) {
                    $return($task, $this);
                    continue;
                }

                if ($task->isFinished()) {
                    $cid = $task->getCoroutineId();
                    unset($this->runningCoroutine[$cid]);
                    if (isset($this->callWaitingCoroutine[$cid])) {
                        foreach ($this->callWaitingCoroutine[$cid] as $waitingPCid) {
                            $this->recover($waitingPCid, $cid);
                        }
                        unset($this->callWaitingCoroutine[$cid]);
                    }
                } else {
                    $this->schedule($task);
                }

                /** 端口数据 */
                $this->socketIoPoll(0);
            } else {
                $this->socketIoPoll(null);
            }
        }
    }

    /**
     * 加入待读列表
     * @param int $id
     * @param ConnectAbstract $socket
     */
    public function waitForRead(int $id, ConnectAbstract $socket)
    {
        if (!isset($this->socketWaitingForRead[$id])) {
            $this->socketWaitingForRead[$id] = $socket;
        }
    }

    /**
     * 待写列表
     * @param int $id
     * @param ConnectAbstract $socket
     */
    public function waitForWrite(int $id, ConnectAbstract $socket)
    {
        if (!isset($this->socketWaitingForWrite[$id])) {
            $this->socketWaitingForWrite[$id] = $socket;
        }
    }


    /**
     * @param $timeout
     */
    protected function socketIoPoll($timeout)
    {
        $rSocks = [];
        foreach ($this->socketMap as $id => $socket) {
            $rSocks[] = $socket;
        }

        /** @var ConnectAbstract $socketConnect */
        foreach ($this->socketWaitingForRead as $socketConnect) {
            $rSocks[] = $socketConnect->getSocket();
        }

        $wSocks = [];
        foreach ($this->socketWaitingForWrite as $socketConnect) {
            $wSocks[$socketConnect->id] = $socketConnect->socket;
        }

        $eSocks = [];
        if (!@stream_select($rSocks, $wSocks, $eSocks, $timeout)) {
            return;
        }

        foreach ($rSocks as $socket) {
            $socketId = (int)$socket;
            if (isset($this->socketMap[$socketId])) {
                // 新连接
                $stream = stream_socket_accept($socket);
                $conId  = (int)$stream;
                /** @var ConnectAbstract $socketConnect */
                $socketConnect = clone $this->socketBindConnectMap[$socketId];
                $socketConnect->setRequestData($stream, $this);
                $canConnect = $socketConnect->onSocketAccept();
                if ($canConnect) {
                    $this->waitForRead($conId, $socketConnect);
                } else {
                    fclose($stream);
                }
            } else {
                /** 已经连接的套接字有事件 */
                /** @var ConnectAbstract $socketConnect */
                $socketConnect = $this->socketWaitingForRead[$socketId];
                $status        = $socketConnect->input();

                if (!$status) {
                    unset($this->socketWaitingForRead[$socketId]);
                }
            }
        }

        foreach ($wSocks as $socket) {
            $socketId      = (int)$socket;
            $socketConnect = $this->socketWaitingForWrite[$socketId];
            $status        = $socketConnect->onSocketWrite();
            if (!$status) {
                unset($this->socketWaitingForWrite[$socketId]);
            }
        }
    }

    /**
     * 父级进入等待列表
     * @param int $pcid 被等待
     * @param int $callCid 发起者
     */
    public function waiting(int $pcid, int $callCid)
    {
        if (isset($this->runningCoroutine[$pcid])) {
            $waitingCoroutine                            = $this->runningCoroutine[$pcid];
            $this->waitingCoroutine[$pcid]               = $waitingCoroutine;
            $this->callWaitingCoroutine[$callCid][$pcid] = time();

            unset($this->runningCoroutine[$pcid]);
        }
    }

    /**
     * 复原协程
     * @param int $pcid 被等待
     * @param int $callCid 发起者
     */
    public function recover(int $pcid, int $callCid)
    {
        if (isset($this->waitingCoroutine[$pcid])) {
            $waitingCoroutine                            = $this->waitingCoroutine[$pcid];
            $this->runningCoroutine[$pcid]               = $waitingCoroutine;
            $this->callWaitingCoroutine[$callCid][$pcid] = time();

            $this->schedule($waitingCoroutine);

            unset($this->waitingCoroutine[$pcid]);
            unset($this->callWaitingCoroutine[$callCid][$pcid]);
        }
    }
}