<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/10
 * Time: 11:38
 */

namespace Utopia\Helper;


class OS
{
    private $win;
    private $linux;

    public function isWin(callable $callable):self
    {
        $this->win = $callable;
        return $this;
    }

    public function isLinux(callable $callable):self
    {
        $this->linux = $callable;
        return $this;
    }

    public function run()
    {
        if( stristr(PHP_OS, 'WIN') ){
            $callable = $this->win;
            $callable();
        }else{
            $callable = $this->linux;
            $callable();
        }
    }
}