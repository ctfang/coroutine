<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/1
 * Time: 20:28
 */

namespace Easy\Coroutines;


use Easy\Socket\ConnectAbstract;

interface ServerInterface
{
    public function handle(ConnectAbstract $connect):\Generator;
}