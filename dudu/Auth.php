<?php
namespace Weixin;

/**
 * 网页授权
 * Class Auth
 *
 * 1、以snsapi_base为scope发起的网页授权，是用来获取进入页面的用户的openid的，并且是静默授权并自动跳转到回调页的。用户感知的就是直接进入了回调页（往往是业务页面）
 * 2、以snsapi_userinfo为scope发起的网页授权，是用来获取用户的基本信息的。但这种授权需要用户手动同意，并且由于用户同意过，所以无须关注，就可在授权后获取该用户的基本信息。
 *
 * @package Weixin
 */
class Auth extends \Weixin {

    public $user;

    protected $lastPermission;

    const API_USER = 'https://api.weixin.qq.com/sns/userinfo';
    const API_TOKEN_GET = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    const API_TOKEN_REFRESH = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';
    const API_TOKEN_VALIDATE = 'https://api.weixin.qq.com/sns/auth';
    const API_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';


    /**
     * 前往api服务器进行授权
     *
     * @param null $plat
     */
    public function toapi($plat = null) {
        if ($plat === null) {
            $param = 'id=' . $this->app_id;
        } elseif (is_numeric($plat)) {
            $param = 'plat=' . $plat;
        } else {
            $param = 'id=' . $this->$plat;
        }
        $url = \Tool_Url::current();
        $url .= (strpos($url, '?') ? '&' : '?') . 'wxauthnoredirect=1';
        header('Location:' . self::API_DUDU_AUTH . '?' . $param . '&url=' . rawurlencode(base64_encode($url)));
        exit;
    }

    /**
     * 通过授权获取用户详细信息
     *
     * @param mixed $plat
     *
     * @return mixed
     */
    public function get_userinfo($plat = null) {
        if (!$this->user) {
            if ($this->input->has('error')) {
                return false;
            } elseif (!$this->input->has('openid')) {
                if ($this->input->has('wxauthnoredirect')) {
                    return false;
                } else{
                    $this->toapi($plat);
                }
            } else {
                $openid     = $this->input->get('openid', 'str');
                $this->user = $this->getUser($openid);
            }
        }
        return $this->user;
    }

    /**
     * 通过授权获取用户openid
     * get_openid
     *
     * @param null $plat
     *
     * @return mixed
     */
    public function get_openid($plat = null) {
        if (!$this->user) {
            if ($this->input->has('error')) {
                return false;
            } elseif (!$this->input->has('openid')) {
                if ($this->input->has('wxauthnoredirect')) {
                    return false;
                } else{
                    $this->toapi($plat);
                }
            } else{
                $this->user['openid'] = $this->input->get('openid', 'str');
            }
        }
        return $this->user['openid'];
    }

    /**
     * 刷新 access_token
     *
     * @param string $refreshToken
     *
     * @return mixed
     */
    public function refresh($refreshToken) {
        $params = array(
            'appid'         => $this->app_id,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        );

        return $this->get(self::API_TOKEN_REFRESH, $params);
    }

    /**
     * 检查 Access Token 是否有效
     *
     * @param string $accessToken
     * @param string $openId
     *
     * @return boolean
     */
    public function accessTokenIsValid($accessToken, $openId) {
        $params = array(
            'openid'       => $openId,
            'access_token' => $accessToken,
        );
        try {
            $this->get(self::API_TOKEN_VALIDATE, $params);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取用户信息
     *
     * @param string $openId
     * @param string $accessToken
     *
     * @return array
     */
    public function getUser($openId) {

        $queries = array(
            'id' => $this->app_id,
            'openid'       => $openId,
        );

        return $this->get('http://city.duduapp.net/wxapi/getinfo', $queries);
    }

    /**
     * 获取access token
     *
     * @param string $code
     *
     * @return string
     */
    public function getAccessPermission($code) {
        $params = array(
            'appid'      => $this->app_id,
            'secret'     => $this->app_secret,
            'code'       => $code,
            'grant_type' => 'authorization_code',
        );

        return $this->lastPermission = $this->get(self::API_TOKEN_GET, $params);
    }
}