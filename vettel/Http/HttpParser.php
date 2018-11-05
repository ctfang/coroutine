<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/2
 * Time: 21:11
 */

namespace Vettel\Http;


class HttpParser
{
    /**
     * 从原始数据解析头
     * @param string $header
     * @return array
     */
    public static function encodeHeader(string $header): array
    {
        $arr = [];

        $arrTemp = explode("\r\n", $header);

        $arrMethodURL = array_shift($arrTemp);
        $arrMethodURL = explode(" ", $arrMethodURL, 3);

        $arr['request_method']  = $arrMethodURL[0];
        $arr['request_url']     = $arrMethodURL[1];
        $arr['request_version'] = $arrMethodURL[0];

        foreach ($arrTemp as $str) {
            list($key, $strValue) = explode(": ", $str, 2);

            $arr[strtolower($key)] = $strValue;
        }

        return $arr;
    }
}