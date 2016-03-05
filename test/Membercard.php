<?php
namespace Weixin;
class Membercard extends Card {

    const API_MEMBER_CARD_ACTIVE = 'https://api.weixin.qq.com/card/membercard/activate';
    const API_MEMBER_CARD_UPDATE = 'https://api.weixin.qq.com/card/membercard/updateuser';
    const API_MEMBER_CARD_LIST = 'https://api.weixin.qq.com/card/user/getcardlist';
    const API_MEMBER_CARD_QRCODE='https://api.weixin.qq.com/card/qrcode/create';

    /**
     * 获取用户会员卡列表
     * get_card_list
     *
     * @param $cardid
     * @param $openid
     * @return mixed
     */
    public function get_user_card($openid, $cardid = '') {
        $params = ['openid' => $openid,];
        if ($cardid) $params['card_id'] = $cardid;
        $result = $this->jsonPost(self::API_MEMBER_CARD_LIST, $params);
        return $result;
    }

    /**
     * 激活/绑定会员卡
     *
     * @param string $cardId
     * @param array  $data
     *
     * @return bool
     */
    public function active($cardId, array $data) {

        $params = array_merge([
            'card_id' => $cardId,
            'init_bonus' => 0,
            'init_balance' => 0
        ], $data);

        return $this->jsonPost(self::API_MEMBER_CARD_ACTIVE, $params);
    }

    /**
     * 修改卡券
     *
     * @param string $cardId
     * @param array  $data
     *
     * @return bool
     */
    public function update($cardId, array $data = array()) {
        $data['card_id'] = $cardId;
        return $this->jsonPost(self::API_MEMBER_CARD_UPDATE, $data);
    }

    public function qrcode($cardid) {
        $params=[
            'action_name'=>'QR_CARD',
            'expire_seconds'=>86400,
            'action_info'=>['card'=>[
                'card_id'=>$cardid,
            ]],
        ];
        $result = $this->jsonPost(self::API_MEMBER_CARD_QRCODE, $params);
        var_dump($result);
    }
}