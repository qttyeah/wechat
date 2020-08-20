<?php
/**
 *
 * Created by PhpStorm.
 * User: 15213
 * Date: 2020/8/20
 * Time: 9:16
 */

namespace Qttyeah\Wechat;


class BaseCallbackapi
{
    //对话凭证
    private $TOKEN;

    /**
     *
     * Base constructor.
     * @param array $options
     */
    function __construct(array $options)
    {

        $this->TOKEN = $options['token'];
    }

    /**
     * 验证是否有效
     * @param $echoStr
     * @return bool
     */
    function valid($echoStr)
    {
        if ($this->checkSignature()) {
            return $echoStr;
        }
        return false;
    }

    /**
     * 接受信息
     * @param $postStr
     * @param array $key
     * @return array
     */
    function responseMsg($postStr, array $key)
    {
        libxml_disable_entity_loader(true);
        $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $objArr = self::object_array($obj);
        $res = [];
        foreach ($key as $value) {
            $res[$value] = isset($objArr[$value]) ? $objArr[$value] : '';
        }
        return $res;
    }

    /**
     * 业务逻辑
     * @param $xml
     */
    function responsInfo($xml)
    {
        echo $xml;
    }

    /**
     * 创建菜单
     * @param $token
     * @param $data
     * @return mixed
     */
    function menu($token, $data)
    {
//        $exmple = [
//            'button'=>[
////                ['type'=>'click','name'=>'一级菜单点击事件','key'=>''],
//                ['name'=>'我只是菜单','sub_button'=>[
//                    ['type'=>'click','name'=>'二级菜单点击事件','key'=>''],
//                    ['type'=>'view','name'=>'二级菜单网页链接','url'=>'http://www.qttyeah.com'],
//                    ['type'=>'miniprogram','name'=>'二级菜单小程序链接','url'=>'http://www.qttyeah.com','appid'=>'','pagepath'=>''],
//                    ['type'=>'scancode_waitmsg','name'=>'二级菜单扫码提示','key'=>'rselfmenu_0_0','sub_button'=>[]],
//                    ['type'=>'scancode_push','name'=>'二级菜单扫码推送','key'=>'rselfmenu_0_0','sub_button'=>[]],
//                ]],
//                ['name'=>'我只是菜单','sub_button'=>[
//                    ['type'=>'pic_sysphoto','name'=>'系统拍照发图','key'=>'rselfmenu_1_0','sub_button'=>[]],
//                    ['type'=>'pic_photo_or_album','name'=>'拍照或者相册发图','key'=>'rselfmenu_1_1','sub_button'=>[]],
//                    ['type'=>'pic_weixin','name'=>'微信相册发图','key'=>'rselfmenu_1_2','sub_button'=>[]],
//                ]],
//                ['name'=>'我只是菜单','sub_button'=>[
//                    ['type'=>'location_select','name'=>'发送位置','key'=>'rselfmenu_2_0'],
//                    ['type'=>'media_id','name'=>'图片','media_id'=>'MEDIA_ID1'],
//                    ['type'=>'view_limited','name'=>'图文消息','media_id'=>'MEDIA_ID2'],
//                ]],
//
//            ]
//        ];
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $token;

        return json_decode(self::curl_post_ssl($url, json_encode($data)), 1);
    }

    /**
     *
     * @return bool
     * @throws \Exception
     */
    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!$this->TOKEN) {

            throw new \Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = $this->TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get curl
     * @param $url
     * @return mixed|string
     */
    public static function curl_get($url)
    {
        $header = array(
            'Accept: application/json',
        );
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 超时设置,以秒为单位
        curl_setopt($curl, CURLOPT_TIMEOUT, 1);

        // 超时设置，以毫秒为单位
        // curl_setopt($curl, CURLOPT_TIMEOUT_MS, 500);

        // 设置请求头
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //执行命令
        $data = curl_exec($curl);

        // 显示错误信息
        if (curl_error($curl)) {
            return ["error_code" => 404, "errmsg" => curl_error($curl)];
        } else {
            curl_close($curl);
            return json_decode($data, 1);
        }
    }

    /**
     * curl方式发送
     * @param $url
     * @param $data
     * @param int $second
     * @param array $aHeader
     * @return mixed|string
     */
    static function curl_post_ssl($url, $data = [], $second = 30, $aHeader = array())
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);

        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return json_encode(array("error_code" => $error));
        }
    }

    static function object_array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = object_array($value);
            }
        }
        return $array;
    }

}