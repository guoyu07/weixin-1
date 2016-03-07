<?php
namespace Weixin;

class Js extends \Weixin {

    protected $token = true;

    protected $ticket;

    const API_TICKET = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi';

    /**
     * 获取JSSDK的配置数组
     *
     * @param array $APIs
     * @param bool  $debug
     * @param bool  $json
     *
     * @return string|array
     */
    public function config(array $APIs, $debug = false, $json = true) {
        //线上环境不支持debug
        if (DUDU_ENV == 'production') $debug = false;
        $signPackage = $this->getSignaturePackage();
        $base        = array(
            'debug' => $debug,
            //'appid'=>$this->app_id,
            //'appsecret'=>$this->app_secret,
            //'ticket'=>$this->getTicket(),
            //'token'=>$this->getToken(),
            //'url'=>\Tool_Url::current(),
        );
        $config      = array_merge($base, $signPackage, array('jsApiList' => $APIs));
        return $json ? $this->jsonencode($config) : $config;
    }

    /**
     * 获取jsticket
     *
     * @return string
     */
    public function getTicket($refresh = 0) {
        if ($this->ticket && !$refresh) {
            return $this->ticket;
        }
        $key = 'dudu.weixin.js.api_ticket' . $this->app_id;
        if ($refresh) {
            $this->getToken(1);
            $this->cache->delete($key);
        }
        if($refresh==2){
            //$result = $this->get(self::API_DUDU_REFRESH_JSTICKET, ['id' => $this->app_id]);
            $result = $this->get(self::API_DUDU_GET_JSTICKET, ['id' => $this->app_id]);
            $this->cache->save($key, $result, ($result['expires_in'] - $_SERVER['REQUEST_TIME']) > 360 ? 360 : ($result['expires_in'] - $_SERVER['REQUEST_TIME']));
        }else{
            $result=$this->cache->fetch($key);
            if (empty($result['expires_in']) || $result['expires_in'] < $_SERVER['REQUEST_TIME']) {
                $result = $this->get(self::API_DUDU_GET_JSTICKET, ['id' => $this->app_id]);
                //if (empty($result['expires_in']) || $result['expires_in'] < $_SERVER['REQUEST_TIME']) {
                //    $result = $this->get(self::API_DUDU_REFRESH_JSTICKET, ['id' => $this->app_id]);
                //}
                $this->cache->save($key, $result, ($result['expires_in'] - $_SERVER['REQUEST_TIME']) > 360 ? 360 : ($result['expires_in'] - $_SERVER['REQUEST_TIME']));
            }
        }
        if (isset($result['ticket']) && $result['expires_in'] > $_SERVER['REQUEST_TIME']) {
            return $this->ticket = $result['ticket'];
        }else{
            return '';
        }
    }

    /**
     * 签名
     *
     * @param string $url
     * @param string $nonce
     * @param int    $timestamp
     *
     * @return array
     */
    public function getSignaturePackage($url = null, $nonce = null, $timestamp = null) {
        $url       = $url ? $url : \Tool_Url::current();
        $nonce     = $nonce ? $nonce : md5($_SERVER['REQUEST_TIME']);
        $timestamp = $timestamp ? $timestamp : $_SERVER['REQUEST_TIME'];
        $ticket    = $this->getTicket();
        $sign      = array(
            'appId'     => $this->app_id,
            'nonceStr'  => $nonce,
            'timestamp' => $timestamp,
            'signature' => $this->getSignature($ticket, $nonce, $timestamp, $url),
        );
        //$sign['t']=$ticket;
        return $sign;
    }


    /**
     * 生成签名
     *
     * @param string $ticket
     * @param string $nonce
     * @param int    $timestamp
     * @param string $url
     *
     * @return string
     */
    public function getSignature($ticket, $nonce, $timestamp, $url) {
        return sha1("jsapi_ticket={$ticket}&noncestr={$nonce}&timestamp={$timestamp}&url={$url}");
    }
}