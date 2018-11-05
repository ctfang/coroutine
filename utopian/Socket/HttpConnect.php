<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/1
 * Time: 16:01
 */

namespace Utopian\Socket;

use Utopian\Coroutines\HttpCoroutine;
use Utopian\Http\HttpCode;
use Utopian\Http\Server\Request;
use Utopian\Http\Server\Response;
use Utopian\Http\Stream\StringStream;
use Utopian\Http\Uri\Uri;
use Utopian\Scheduler;

class HttpConnect extends ConnectAbstract
{
    public $socket;
    /** @var int */
    public $id;
    /** @var Scheduler */
    public $scheduler;

    /** @var Response */
    protected $response;

    /** @var int 已经接受的长度 */
    public $bytesRead = 0;
    /** @var string 已经接受的数据 */
    public $stringBuffer = '';
    /** @var int 报文定义的长度 */
    public $contentLength = 0;

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

        $request = new Request();
        $request->withBody(new StringStream($body));

        $arrTemp      = explode("\r\n", $header);
        $arrMethodURL = array_shift($arrTemp);
        $arrMethodURL = explode(" ", $arrMethodURL, 3);

        $request = $request->withRequestTarget($arrMethodURL[1]);
        $request = $request->withUri(new Uri($arrMethodURL[1]));
        $request = $request->withMethod($arrMethodURL[0]);
        $request = $request->withProtocolVersion($arrMethodURL[2]);

        foreach ($arrTemp as $str) {
            list($key, $strValue) = explode(": ", $str, 2);
            $request = $request->withHeader($key, $strValue);
        }

        if ($request->getMethod() == 'POST') {
            $post = [];
            switch ($request->getHeader('content-type')) {
                case 'multipart/form-data':
                    list($contentType, $boundary) = explode("; ", $request->getHeader('content-type'), 2);

                    $request->withHeader('content-type', $contentType);
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
            $request = $request->withQueryParams($post);
        }

        // 解析完成
        $httpHandle = new HttpCoroutine();
        $response   = $httpHandle->handle($request);
        $this->write($response);
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
     * @param $response
     * @return mixed
     */
    public function write($response)
    {
        $this->scheduler->waitForWrite($this->id, $this);
        $this->response = $response;
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
        if( $this->response ){
            $response   = $this->response;
            $statusCode = $response->getStatusCode();
            $httpString = $response->getProtocolVersion().' '.$statusCode.' '.HttpCode::$codes[$statusCode]."\r\n";
            foreach ($response->getHeaders() as $key=>$header){
                $httpString .= $key.': '.$header."\r\n";
            }
            $httpString .= 'Content-Length: '.$response->getBody()->getSize()."\r\n";
            $httpString .= "\r\n".$response->getBody()->getContents();
            fwrite($this->socket, $httpString);
        }

        fclose($this->socket);
        return false;
    }
}