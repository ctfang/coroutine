<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/4
 * Time: 14:39
 */

namespace Utopia\SocketServer;


use Utopia\Helper\Browser;
use Utopia\Helper\OS;
use Utopia\Socket\Connect\HttpConnect;
use Utopia\Socket\Scheduler;

class HttpServer extends Scheduler
{
    public function __construct(int $port = 80, $local = '0.0.0.0')
    {
        $local   = $local.':'.$port;
        $connect = new HttpConnect();
        $connect->setHandle(new HttpHandle());
        $this->monitor('tcp://'.$local, $connect);
        Browser::open($local);
    }
}