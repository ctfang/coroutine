<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/1
 * Time: 16:36
 */

namespace Vettel\Socket;

use Vettel\Scheduler;

abstract class ConnectAbstract
{
    public $header = [];
    public $body = '';
    public $get = [];
    public $post = [];

    /**
     * 设置连接回话基础信息
     *
     * @param $socket
     * @param Scheduler $scheduler
     * @return mixed
     */
    abstract public function setRequestData($socket, Scheduler $scheduler);

    /**
     * 非连接事件,每次有数据都触发
     *
     * @return bool false 关闭连接
     */
    abstract public function input(): bool;

    /**
     * 获取socket连接
     *
     * @return mixed
     */
    abstract public function getSocket();

    /**
     * 有新数据传入
     *
     * @param string $data
     * @return mixed
     */
    abstract public function onSocketMessage(string $data);


    /**
     * 连接关闭
     *
     * @return mixed
     */
    abstract public function onSocketClose();

    /**
     * 有新情求进入
     *
     * @return bool true 允许进入
     */
    abstract public function onSocketAccept(): bool;

    /**
     * 发送可写事件
     *
     * @return bool false 不需要继续监听可写
     */
    abstract public function onSocketWrite(): bool;

    /**
     * 发送数据
     * @param string $str
     * @return mixed
     */
    abstract public function write(string $str);
}