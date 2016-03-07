<?php
namespace Weixin;
class Store extends \Weixin {

    protected $token=true;

    const API_CREATE = 'http://api.weixin.qq.com/cgi-bin/poi/addpoi';
    const API_GET = 'http://api.weixin.qq.com/cgi-bin/poi/getpoi';
    const API_LIST = 'http://api.weixin.qq.com/cgi-bin/poi/getpoilist';
    const API_UPDATE = 'http://api.weixin.qq.com/cgi-bin/poi/updatepoi';
    const API_DELETE = 'http://api.weixin.qq.com/cgi-bin/poi/delpoi';

    public function getlist($offset = 0, $limit = 10) {
        $params = array(
            'begin' => $offset,
            'limit' => $limit,
        );
        $data = $this->jsonPost(self::API_LIST, $params);
        return $data;
    }
}