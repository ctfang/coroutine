<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/1
 * Time: 16:01
 */

namespace Vettel\Socket;

use Vettel\Coroutines\HttpCoroutine;
use Vettel\Scheduler;

class HttpConnect extends ConnectAbstract
{
    public $socket;
    /** @var int */
    public $id;
    /** @var Scheduler */
    public $scheduler;

    protected $writes = [];

    /** @var int 已经接受的长度 */
    public $bytesRead = 0;
    /** @var string 已经接受的数据 */
    public $stringBuffer = '';
    /** @var int 报文定义的长度 */
    public $contentLength = 0;

    public $header = [];
    public $body = '';
    public $get = [];
    public $post = [];


    public function setRequestData($socket, Scheduler $scheduler)
    {
        $this->socket = $socket;
        $this->id     = (int)$socket;

        $this->scheduler = $scheduler;
    }

    /**
     * 有新数据传入
     * @param string $buffer
     * @return mixed
     */
    public function onSocketMessage(string $buffer)
    {
        list($header, $body) = explode("\r\n\r\n", $buffer, 2);

        $this->body = $body;

        $arrTemp      = explode("\r\n", $header);
        $arrMethodURL = array_shift($arrTemp);
        $arrMethodURL = explode(" ", $arrMethodURL, 3);

        $server['REQUEST_TIME']    = time();
        $server['REQUEST_METHOD']  = $arrMethodURL[0];
        $server['SERVER_PROTOCOL'] = $arrMethodURL[2];
        $arrUrl                    = parse_url($arrMethodURL[1]);
        $server['REQUEST_URI']     = $arrUrl['path'];
        if (isset($arrUrl['query'])) {
            parse_str($arrUrl['query'], $this->get);
        }

        $headers = [];
        foreach ($arrTemp as $str) {
            list($key, $strValue) = explode(": ", $str, 2);

            $headers[strtolower($key)] = $strValue;
        }

        if (isset($headers['content-type'])) {
            switch ($headers['content-type']) {
                case 'multipart/form-data':
                    list($headers['content-type'], $boundary) = explode("; ", $headers['content-type'], 2);
                    list(, $boundary) = explode("=", $boundary, 2);

                    $post = [];
                    break;
                case 'application/json':
                    $post = json_decode($body, true);
                    break;
                case 'application/x-www-form-urlencoded':
                    parse_str($body, $post);
                    break;
            }
            $this->post = $post;
        }

        $this->header = $headers;

        // 解析完成
        $httpHandle = new HttpCoroutine();
        $requestGen = $httpHandle->handle($this);

        $this->scheduler->newCoroutine($requestGen);
    }

    /**
     * 有新情求进入
     * @return bool true 允许进入
     */
    public function onSocketAccept(): bool
    {
        return true;
    }

    /**
     * 发送数据
     * @param string $str
     * @return mixed
     */
    public function write(string $str)
    {
        $this->scheduler->waitForWrite($this->id, $this);
        $this->writes[0] = $str;
    }

    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * 连接关闭
     *
     * @return mixed
     */
    public function onSocketClose()
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }

    /**
     * 非连接事件,每次有数据都触发
     *
     * @return bool false 关闭连接
     */
    public function input(): bool
    {
        $buffer = fread($this->socket, 65535);

        if ($buffer === '' || $buffer === false) {
            if (feof($this->socket) || !is_resource($this->socket) || $buffer === false) {
                $this->onSocketClose();

                return false;
            }
        }

        $this->bytesRead    += strlen($buffer);
        $this->stringBuffer .= $buffer;

        if ($this->contentLength == 0) {
            $this->contentLength = $this->getContentLength($this->stringBuffer);
        }

        if ($this->contentLength == $this->bytesRead) {
            $this->onSocketMessage($this->stringBuffer);

            return false;
        }

        return true;
    }

    /**
     * 获取报文声明的大小
     *
     * @param $buffer
     * @return int
     */
    private function getContentLength($buffer): int
    {
        if (!strpos($buffer, "\r\n\r\n")) {
            return 0;
        }

        list($header,) = explode("\r\n\r\n", $buffer, 2);
        $method = substr($header, 0, strpos($header, ' '));

        if ($method === 'GET' || $method === 'OPTIONS' || $method === 'HEAD') {
            return strlen($header) + 4;
        }
        $match = array();
        if (preg_match("/\r\nContent-Length: ?(\d+)/i", $header, $match)) {
            $content_length = isset($match[1]) ? $match[1] : 0;

            return $content_length + strlen($header) + 4;
        }

        return $method === 'DELETE' ? strlen($header) + 4 : 0;
    }

    /**
     * 发送可写事件
     *
     * @return bool false 不需要继续监听可写
     */
    public function onSocketWrite(): bool
    {
        foreach ($this->writes as $str) {
            fwrite($this->socket, $str);
        }
        fclose($this->socket);

        return false;
    }
}