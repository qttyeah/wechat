<?php
/**
 * 微信公众号的基本类
 * Created by PhpStorm.
 * User: 15213
 * Date: 2020/8/13
 * Time: 14:31
 */

namespace Qttyeah\Wechat;


class Account
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
        'tokenUrl' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={appid}&secret={secret}',
        'codeUrl' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid={appid}&redirect_uri={redirect_url}&response_type=code&scope=snsapi_base&state={state}#wechat_redirect',
        'authUrl' => 'https://api.weixin.qq.com/sns/oauth2/access_token?appid={appid}&secret={secret}&code={code}&grant_type=authorization_code',
        'userUrl' => 'https://api.weixin.qq.com/sns/userinfo?access_token={access_token}&openid={openid}&lang=zh_CN',
        'refreshToken' => 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid={appid}&grant_type=refresh_token&refresh_token={refresh_token}',
        'ckToken' => 'https://api.weixin.qq.com/sns/auth?access_token={access_token}&openid={openid}'
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
     * 获取基础token
     * @return mixed|string
     * access_token,expires_in
     */
    function getAccessToken()
    {
        $url = str_replace([
            '{appid}',
            '{secret}'
        ], [
            $this->options['appid'],
            $this->options['secret'],
        ], $this->urls['codeUrl']);
        return BaseCallbackapi::curl_get($url);
    }

    /**
     * 授权链接
     * @param $redirectUrl 通知地址
     * @param int $state
     * @return mixed
    {
     * "access_token":"ACCESS_TOKEN",
     * "expires_in":7200,
     * "refresh_token":"REFRESH_TOKEN",
     * "openid":"OPENID",
     * "scope":"SCOPE"
     * }
     */
    public function getcodeAction($redirectUrl, $state = 123)
    {
        $url = str_replace([
            '{appid}',
            '{redirect_url}',
            '{state}',
        ], [
            $this->options['appid'],
            urlencode($redirectUrl),
            $state
        ], $this->urls['codeUrl']);
        return $url;
    }

    /**
     * 获取授权access_token
     * @param $code
     * @return mixed|string
    "access_token":"ACCESS_TOKEN",
     * "expires_in":7200,
     * "refresh_token":"REFRESH_TOKEN",
     * "openid":"OPENID",
     * "scope":"SCOPE"
     */
    public function authorization($code)
    {
        $url = str_replace([
            '{appid}',
            '{secret}',
            '{code}',
        ], [
            $this->options['appid'],
            $this->options['secret'],
            $code
        ], $this->urls['authUrl']);
        return BaseCallbackapi::curl_get($url);
    }

    /**
     * 用户信息
     * @param $accessToken
     * @param $openid
     * @return mixed|string
    "openid":" OPENID",
     * "nickname": NICKNAME,
     * "sex":"1",
     * "province":"PROVINCE",
     * "city":"CITY",
     * "country":"COUNTRY",
     * "headimgurl":       "http://thirdwx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
     * "privilege":[ "PRIVILEGE1" "PRIVILEGE2"     ],
     * "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
     */
    public function getInfo($accessToken, $openid)
    {
        $url = str_replace([
            '{access_token}',
            '{openid}'
        ], [
            $accessToken,
            $openid
        ], $this->urls['userUrl']);
        return BaseCallbackapi::curl_get($url);
    }

    /**
     * 刷新token
     * @param $refreshToken
     * @return mixed|string
     */
    public function refreshToken($refreshToken)
    {
        $url = str_replace([
            '{refresh_token}',
            '{appid}'
        ], [
            $refreshToken,
            $this->options['appid']
        ], $this->urls['refreshToken']);
        return BaseCallbackapi::curl_get($url);
    }

    /**
     * 验证token
     * @param $accessToken
     * @param $openid
     * @return bool
     */
    public function checkToken($accessToken, $openid)
    {
        $url = str_replace([
            '{access_token}',
            '{openid}'
        ], [
            $accessToken,
            $openid
        ], $this->urls['ckToken']);

        $data = BaseCallbackapi::curl_get($url);
        return $data['errcode'] ? false : true;
    }




}