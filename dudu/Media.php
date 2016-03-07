<?php
namespace Weixin;
class Media extends \Weixin {


    protected $token = true;

    const API_TEMPORARY_UPLOAD = 'http://file.api.weixin.qq.com/cgi-bin/media/upload';
    const API_FOREVER_UPLOAD = 'https://api.weixin.qq.com/cgi-bin/material/add_material';
    const API_TEMPORARY_GET = 'https://api.weixin.qq.com/cgi-bin/media/get';
    const API_FOREVER_GET = 'https://api.weixin.qq.com/cgi-bin/material/get_material';
    const API_FOREVER_NEWS_UPLOAD = 'https://api.weixin.qq.com/cgi-bin/material/add_news';
    const API_FOREVER_NEWS_UPDATE = 'https://api.weixin.qq.com/cgi-bin/material/update_news';
    const API_FOREVER_DELETE = 'https://api.weixin.qq.com/cgi-bin/material/del_material';
    const API_FOREVER_COUNT = 'https://api.weixin.qq.com/cgi-bin/material/get_materialcount';
    const API_FOREVER_LIST = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material';

    public $forever = false;

    /**
     * 下载媒体文件
     *
     * @param string $mediaId
     *
     * @return mixed
     */
    public function download($mediaId, $n = 0) {
        $params = array('media_id' => $mediaId);

        if ($this->forever) {
            $contents = $this->jsonPost(self::API_FOREVER_GET, $params, null);
        } else {
            $contents = $this->get(self::API_TEMPORARY_GET, $params, null);
        }
        if ($contents{0} == '{') {
            $res = json_decode($contents, true);
            if (in_array($res['errcode'], [40001, 42001]) && $n == 0) {
                $this->getToken(1);
                return $this->download($mediaId, 1);
            }
            return json_decode($contents, true);
        } else {
            $ext = \Tool_File::getStreamExt($contents);
            return ['ext' => $ext, 'content' => $contents];
        }
    }


}