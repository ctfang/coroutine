<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/6
 * Time: 22:25
 */

namespace Easy\Coroutines;

/**
 * Class CoroutineTask
 * 协程任务单位
 * @package Easy\Coroutines
 */
class CoroutineTask implements CoroutineTaskInterface
{
    protected $cid  = 0;
    protected $pcid = 0;
    protected $coroutine;
    protected $sendValue = null;
    protected $beforeFirstYield = true;

    public function __construct($cid, int $pcid,\Generator $coroutine)
    {
        $this->cid       = $cid;
        $this->pcid      = $pcid;
        $this->coroutine = $coroutine;
    }

    /**
     * 获取当前协程id
     * @return int
     */
    public function getCoroutineId()
    {
        return $this->cid;
    }

    /**
     * 获取当前父级协程id
     * @return int
     */
    public function getParentCoroutineId()
    {
        return $this->pcid;
    }

    public function setSendValue($sendValue)
    {
        $this->sendValue = $sendValue;
    }

    public function run() {
        if ($this->beforeFirstYield) {
            $this->beforeFirstYield = false;
            return $this->coroutine->current();
        } else {
            $retval = $this->coroutine->send($this->sendValue);
            $this->sendValue = $retval;
            return $retval;
        }
    }

    public function isFinished():bool
    {
        return !$this->coroutine->valid();
    }
}