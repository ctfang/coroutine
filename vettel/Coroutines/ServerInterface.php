<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/1
 * Time: 20:28
 */

namespace Vettel\Coroutines;


use Vettel\Socket\ConnectAbstract;

interface ServerInterface
{
    public function handle(ConnectAbstract $connect):\Generator;
}