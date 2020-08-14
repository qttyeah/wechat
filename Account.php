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
     * 授权链接
     * @param $redirectUrl 通知地址
     * @param int $state
     * @return mixed
     */
    public function getcodeAction($redirectUrl, $state = 123)
    {
        $codeUrl = str_replace([
            '{appid}',
            '{redirect_url}',
            '{state}',
        ], [
            $this->options['appid'],
            urlencode($redirectUrl),
            $state
        ], $this->urls['codeUrl']);
        return $codeUrl;
    }

    /**
     * 获取授权access_token
     * @param $code
     * @return mixed|string
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
        return self::curl_get($authUrl);
    }

    /**
     * 用户信息
     * @param $accessToken
     * @param $openid
     * @return mixed|string
     */
    public function getInfo($accessToken, $openid)
    {
        $userUrl = str_replace([
            '{access_token}',
            '{openid}'
        ], [
            $accessToken,
            $openid
        ], $this->urls['userUrl']);
        return self::curl_get($userUrl);
    }

    /**
     * 刷新token
     * @param $refreshToken
     * @return mixed|string
     */
    public function refreshToken($refreshToken)
    {
        $refresh = str_replace([
            '{refresh_token}',
            '{appid}'
        ], [
            $refreshToken,
            $this->options['appid']
        ], $this->urls['refreshToken']);
        return self::curl_get($refresh);
    }

    /**
     * 验证token
     * @param $accessToken
     * @param $openid
     * @return bool
     */
    public function checkToken($accessToken, $openid)
    {
        $ckToken = str_replace([
            '{access_token}',
            '{openid}'
        ], [
            $accessToken,
            $openid
        ], $this->urls['ckToken']);
        $data = self::curl_get($ckToken);
        return $data['errcode'] ? false : true;
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

}