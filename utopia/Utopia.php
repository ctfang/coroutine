<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/10
 * Time: 17:07
 */

namespace Utopia;

/**
 * Class Utopia
 * @package Utopia
 */
class Utopia
{
    /** @var \Utopia\Co\Scheduler */
    protected $utopiaCoroutines;
    /** @var \Utopia\Socket\Scheduler */
    protected $utopiaSocket;


    /**
     * 环境初始化
     */
    public function init()
    {

    }

    /**
     * 设置协程调度
     * @param $utopiaCoroutines
     */
    public function setCoroutines($utopiaCoroutines)
    {
        $this->utopiaCoroutines = $utopiaCoroutines;
    }

    /**
     * 新增一个端口监听
     * @param $utopiaSocket
     */
    public function addSocket($utopiaSocket)
    {
        $this->utopiaSocket = $utopiaSocket;
    }

    /**
     * 应用总入口
     */
    public function run()
    {
        if( $this->utopiaCoroutines ){
            while (true) {
                $has        = $this->utopiaCoroutines->run();
                $timeout    = $has ? 0 : null;
                $this->utopiaSocket->run($timeout);
            }
        }else{
            while (true) {
                $this->utopiaSocket->run(null);
            }
        }
    }
}