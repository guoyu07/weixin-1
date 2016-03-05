<?php
namespace Weixin;

/**
 * 卡券
 * Class Card
 *
 * @package Weixin
 */
class Card extends \Weixin {

    protected $token = true;

    protected $ticket;
    // 卡券类型
    const TYPE_GENERAL_COUPON = 'GENERAL_COUPON';   // 通用券
    const TYPE_GROUPON = 'GROUPON';          // 团购券
    const TYPE_DISCOUNT = 'DISCOUNT';         // 折扣券
    const TYPE_GIFT = 'GIFT';             // 礼品券
    const TYPE_CASH = 'CASH';             // 代金券
    const TYPE_MEMBER_CARD = 'MEMBER_CARD';      // 会员卡
    const TYPE_SCENIC_TICKET = 'SCENIC_TICKET';    // 景点门票
    const TYPE_MOVIE_TICKET = 'MOVIE_TICKET';     // 电影票
    const TYPE_BOARDING_PASS = 'BOARDING_PASS';    // 飞机票
    const TYPE_LUCKY_MONEY = 'LUCKY_MONEY';      // 红包
    const TYPE_MEETING_TICKET = 'MEETING_TICKET';   // 会议门票

    const API_CREATE = 'https://api.weixin.qq.com/card/create';
    const API_DELETE = 'https://api.weixin.qq.com/card/delete';
    const API_GET = 'https://api.weixin.qq.com/card/get';
    const API_UPDATE = 'https://api.weixin.qq.com/card/update';
    const API_LIST = 'https://api.weixin.qq.com/card/batchget';
    const API_WHITELIST = 'https://api.weixin.qq.com/card/testwhitelist/set';
    const API_CONSUME = 'https://api.weixin.qq.com/card/code/consume';
    const API_COLOR = 'https://api.weixin.qq.com/card/getcolors';
    const API_UNAVAILABLE = 'https://api.weixin.qq.com/card/code/unavailable';
    const API_CODE_GET = 'https://api.weixin.qq.com/card/code/get';
    const API_CODE_UPDATE = 'https://api.weixin.qq.com/card/code/update';
    const API_CODE_DECRYPT = 'https://api.weixin.qq.com/card/code/decrypt';
    const API_UPDATE_STOCK = 'https://api.weixin.qq.com/card/modifystock';
    const API_MOVIE_TICKET_UPDATE = 'https://api.weixin.qq.com/card/movieticket/updateuser';
    const API_BOARDING_PASS_CHECKIN = 'https://api.weixin.qq.com/card/boardingpass/checkin';
    const API_MEETING_TICKET_UPDATE = 'https://api.weixin.qq.com/card/meetingticket/updateuser';
    const API_TICKET = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=wx_card';


    /**
     * 获取jsticket
     *
     * @return string
     */
    public function getTicket($refresh = 0) {

    }

    /**
     * 创建卡券
     *
     * @param array  $base
     * @param array  $properties
     * @param string $type
     *
     * @return string
     */
    public function create(array $base, array $properties = array(), $type = self::TYPE_MEMBER_CARD) {
        $key    = strtolower($type);
        $card   = array_merge(array('base_info' => $base), $properties);
        $params = array(
            'card' => array(
                'card_type' => $type,
                $key        => $card,
            ),
        );
        return $this->jsonPost(self::API_CREATE, $params);
    }

    /**
     * 卡券详情
     *
     * @param string $cardId
     *
     * @return mixed
     */
    public function get_detail($cardId) {
        $params = array('card_id' => $cardId);

        $result = $this->jsonPost(self::API_GET, $params);

        return isset($result['card']) ? $result['card'] : $result;
    }

    /**
     * 设置测试号
     *
     * @param        $data
     * @param string $type
     * @return mixed
     */
    public function set_white_list($data, $type = 'openid') {
        $params = [$type => is_array($data) ? $data : [$data]];
        $result = $this->jsonPost(self::API_WHITELIST, $params);
        return $result;
    }


    /**
     * 删除会员卡
     *
     * @param $cardid
     * @return mixed
     */
    public function delete($cardid) {
        $params = [
            'card_id' => $cardid
        ];
        $result = $this->jsonPost(self::API_DELETE, $params);
        return $result;
    }

    /**
     * JschoseCard
     * 生成选择会员卡的js
     *
     * @param           $cardId
     * @param bool|true $json
     * @return array|string
     */
    public function JschoseCard($cardId, $json = true) {
        $params             = [
            'cardId'    => $cardId,
            'timestamp' => $_SERVER['REQUEST_TIME'],
            'nonceStr'  => $this->getNonceStr(),
        ];
        $params['cardSign'] = $this->getSignature(array_merge($params, ['api_ticket' => $this->getTicket()]));
        $params['signType'] = 'SHA1';
        return $json ? $this->jsonEncode($params) : $params;
    }

    /**
     * 生成 js添加到卡包 需要的 card_list 项
     *
     * @param string $cardId
     * @param bool   $json
     *
     * @return string
     */
    public function JsaddCard($cardId, $json = true) {

        $ext              = array(
            'timestamp' => $_SERVER['REQUEST_TIME'],
            'nonce_str' => $this->getNonceStr(),
        );
        $ext['signature'] = $this->getSignature(array_merge($ext, ['api_ticket' => $this->getTicket(), 'card_id' => $cardId]));
        $ext['t']=$this->getTicket();
        return $json ? $this->jsonEncode([
                'cardId' => $cardId, 'cardExt' => ($ext)]
        ) : ['cardId' => $cardId, 'cardExt' => $ext];
    }

    /**
     * 获取卡卷背景色
     *
     * @return mixed
     */
    public function get_color() {
        $result = $this->get(self::API_COLOR, array());
        return $result;
    }

    /**
     * 废弃卡券，失效
     *
     * @param string $code
     * @param string $cardId
     *
     * @return bool
     */
    public function disable($code, $cardId = null) {
        $params = array(
            'code'    => $code,
            'card_id' => $cardId,
        );

        return $this->jsonPost(self::API_UNAVAILABLE, $params);
    }

    /**
     * 生成签名
     *
     * @return string
     */
    protected function getSignature($params) {
        sort($params, SORT_STRING);
        return sha1(implode($params));
    }


}