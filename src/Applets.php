<?php
/**
 * 微信小程序基本类
 * Created by PhpStorm.
 * User: 15213
 * Date: 2020/8/14
 * Time: 10:12
 */

namespace Qttyeah\Wechat;


class Applets
{

    /**
     * 公众号配置
     * @var array
     */
    private $options = [
        'appid' => '',
        'secret' => '',
        'backUrl' => '',
    ];

    /**
     * 授权url
     * @var array
     */
    private $urls = [
        'atUrl' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={appid}&secret={secret}',
        'authUrl' => 'https://api.weixin.qq.com/sns/jscode2session?appid={appid}&secret={secret}&js_code={code}&grant_type=authorization_code'

    ];

    /**
     * Account constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     * 获取接口调用凭据
     * @return mixed|string
     */
    public function getAccessToken()
    {
        $atUrl = str_replace([
            '{appid}',
            '{secret}',
        ], [
            $this->options['appid'],
            $this->options['secret']
        ], $this->urls['atUrl']);
        return BaseCallbackapi::curl_get($atUrl);
    }

    /**
     * 普通授权信息
     * @param $code
     * @return mixed|string
     * errcode -1:系统繁忙;0:请求成功;40029:code 无效;45011:每个用户每分钟100次
     * sessionKey.openid.unionid
     */
    public function authorization($code)
    {
        $authUrl = str_replace([
            '{appid}',
            '{secret}',
            '{code}',
        ], [
            $this->options['appid'],
            $this->options['secret'],
            $code
        ], $this->urls['authUrl']);
        return BaseCallbackapi::curl_get($authUrl);
    }


    /**
     * 解密
     * @param $encryptedData
     * @param $iv
     * @param $sessionKey
     * @return array|mixed
     */
    public function decryptData($encryptedData, $iv, $sessionKey)
    {
        if (strlen($sessionKey) != 24) {
            return ['error_code' => -41001];
        }
        $aesKey = base64_decode($sessionKey);


        if (strlen($iv) != 24) {
            return ['error_code' => -41002];
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj = json_decode($result);
        if ($dataObj == NULL) {
            return ['error_code' => -41003];
        }
        if ($dataObj->watermark->appid != $this->options['appid']) {
            return ['error_code' => -41003];
        }
        return json_decode($result, 1);
    }

    /**
     * 订阅消息发送
     * @param $access_token
     * @param $openid
     * @param $template_id
     * @param $content
     * @param string $state developer为开发版；trial为体验版；formal为正式版；默认为正式版
     * @param string $page
     * @return mixed|string
     */
    public function sendSubscribeMessage($access_token, $openid, $template_id, $content, $state = 'formal', $page = '')
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=" . $access_token;
        $data = [
            'touser' => $openid,
            'template_id' => $template_id,
            'data' => $content,
            'miniprogram_state' => $state,
            'page' => $page,
        ];
        return BaseCallbackapi::curl_post_ssl($url, json_encode($data));
    }

    /**
     * 小程序码 
     * @param $access_token
     * @param $scene
     * @param string $page
     * @param int $width
     * @param bool $auto_color
     * @param string $line_color
     * @param bool $is_hyaline
     * @return mixed|string
     */
    function getERcode($access_token, $scene, $page = 'pages/index/index', $width = 430, $auto_color = false, $line_color = '{"r":0,"g":0,"b":0}', $is_hyaline = false)
    {
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
        $data = [
            "scene" => $scene,
            "page" => $page,
            "width" => $width,
            "auto_color" => $auto_color,
            "line_color" => $line_color,
            "is_hyaline" => $is_hyaline,
        ];
        return BaseCallbackapi::curl_post_ssl($url, json_encode($data));
    }




}