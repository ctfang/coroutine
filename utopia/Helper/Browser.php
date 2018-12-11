<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/10
 * Time: 11:22
 */

namespace Utopia\Helper;

/**
 * 浏览器操作
 * @package Utopia\Helper
 */
class Browser
{
    public static function open(string $url)
    {
        (new OS())
            ->isWin(
                function () use ($url) {
                    list($local, $port) = explode(':', $url);

                    if( !$port ){
                        $port = 80;
                    }

                    $arr = explode('.', $local);
                    if (isset($arr[0]) && $arr[0] == '0') {
                        $local = 'localhost';
                    }

                    system('start http://'.$local.':'.$port);
                }
            )
            ->run();
    }
}