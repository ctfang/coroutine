<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/11
 * Time: 17:11
 */

namespace Easy\Coroutines;

use Easy\Coroutine;
use Easy\Socket\ConnectAbstract;

class HttpCoroutine implements ServerInterface
{
    /**
     * @param $connect
     * @return \Generator
     */
    public function handle(ConnectAbstract $connect): \Generator
    {
        yield;
        go(
            function ():\Generator {
                yield;
                echo "立即输出 \n";
            }
        );

        go(
            function ():\Generator {
                yield co_sleep(2);
                echo "22222 等待输出\n";
            }
        );

        go(
            function ():\Generator {
                yield co_sleep(1);
                echo "11111 等待输出\n";
            }
        );


        $out = "hello world 2 \n";

        $strLen = strlen($out);

        $response = <<<RES
HTTP/1.1 200 OK
Content-Type: text/plain
Content-Length: {$strLen}
Connection: close

{$out}
RES;
        $connect->write($response);
    }
}