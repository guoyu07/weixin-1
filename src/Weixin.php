<?php
namespace pakey\weixin;
use pakey\tool\Http;
use think\Cache;

class weixin
{

    /**
     * 是否需要access_token
     *
     * @var bool
     */
    protected $token = false;
    /**
     * 单例
     *
     * @var static
     */
    public static $_instance;

    /**
     * appid
     *
     * @var string
     */
    protected $appId;

    /**
     * @var string
     */
    protected $appSecret;

    /**
     * @var string
     */
    protected $access_token;

    const API_TOKEN_GET = 'https://api.weixin.qq.com/cgi-bin/token';

    public function __construct($appId, $appSecret)
    {
        $this->appId     = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * @param $appId
     * @param $appSecret
     * @return static
     */
    public static function getInstance($appId, $appSecret)
    {
        if (!static::$_instance instanceof static) {
            static::$_instance = new static($appId, $appSecret);
        }
        return static::$_instance;
    }


    public function getToken($forceRefresh = 0)
    {
        $cacheKey = 'pakey_weixin_accesstoken_' . $this->appId;
        $data     = Cache::get($cacheKey);
        if ($forceRefresh || empty($data)) {
            $token = $this->_getTokenFromServer();
            // XXX: T_T... 7200 - 1500
            Cache::set($cacheKey, $token['access_token'], $token['expires_in'] - 1500);
            return $token['access_token'];
        }
        return $data;
    }

    protected function _getTokenFromServer()
    {
        $params = [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'client_credential',
        ];
        $token = $this->parseJSON(Http::get(self::API_TOKEN_GET, $params));
        if (empty($token['access_token'])) {
            trigger_error('Request AccessToken fail. response: '.json_encode($token, JSON_UNESCAPED_UNICODE),E_USER_ERROR);
        }
        return $token;
    }

    protected function parseJSON($data){
        if($data{0}=='{'){
            return json_decode($data,true);
        }else{
            return null;
        }
    }

    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}