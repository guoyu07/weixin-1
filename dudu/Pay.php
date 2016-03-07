<?php
namespace Weixin;

class Pay extends \Weixin{
    const API_UNIFIED_ORDER='https://api.mch.weixin.qq.com/pay/unifiedorder';

    public function unifiedorder($data) {
        $data=array_merge([
            'appid'=>$this->app_id,
            'mch_id'=>\Config::get('weixin.mchid'),
            'nonce_str'=>$this->getNonceStr(),
            'notify_url'=>\Config::get('weixin.notifyurl'),
            'spbill_create_ip'=>\Tool_IP::get(),
            'trade_type'=>'JSAPI'
        ],$data);
        $data['sign']=$this->MakeSign($data);
        $xml=\Tool_Xml::build($data);
        $res=$this->post(self::API_UNIFIED_ORDER,$xml,'xml');
        $res=\Tool_Xml::parse($res);
        if($res['return_code']=='SUCCESS'){
            return $res['prepay_id'];
        }else{
            return $res['return_msg'];
        }
    }

    public function notify($data=null) {
        if(!$data){
            $data=file_get_contents('php://input');
        }
        file_put_contents('/home/wwwlogs/pay/'.date('Ymd').'.log',$data.PHP_EOL,FILE_APPEND);
//        $data="<xml><appid><![CDATA[wx835d72af585de386]]></appid>
//<bank_type><![CDATA[CFT]]></bank_type>
//<cash_fee><![CDATA[1]]></cash_fee>
//<fee_type><![CDATA[CNY]]></fee_type>
//<is_subscribe><![CDATA[N]]></is_subscribe>
//<mch_id><![CDATA[1270410101]]></mch_id>
//<nonce_str><![CDATA[urn2pqi3v8qwv0mywt27rda6l9oistde]]></nonce_str>
//<openid><![CDATA[o_d4Is7_HsBzwor-gND6ZTF1n4c0]]></openid>
//<out_trade_no><![CDATA[20151014113551329]]></out_trade_no>
//<result_code><![CDATA[SUCCESS]]></result_code>
//<return_code><![CDATA[SUCCESS]]></return_code>
//<sign><![CDATA[3F52AF4ED25C40842679A140E5C2B24D]]></sign>
//<time_end><![CDATA[20151014113559]]></time_end>
//<total_fee>1</total_fee>
//<trade_type><![CDATA[JSAPI]]></trade_type>
//<transaction_id><![CDATA[1003330799201510141190922801]]></transaction_id>
//</xml>";
//        $data="<xml><appid><![CDATA[wx59c2d31528324f45]]></appid>
//<bank_type><![CDATA[CFT]]></bank_type>
//<cash_fee><![CDATA[1]]></cash_fee>
//<fee_type><![CDATA[CNY]]></fee_type>
//<is_subscribe><![CDATA[Y]]></is_subscribe>
//<mch_id><![CDATA[10061636]]></mch_id>
//<nonce_str><![CDATA[wekreml8njxhdltu1g3gjpo2weiiuetv]]></nonce_str>
//<openid><![CDATA[ot3uKjnXgI1z17S6qaC0wUQg8Fxo]]></openid>
//<out_trade_no><![CDATA[20151103155422780]]></out_trade_no>
//<result_code><![CDATA[SUCCESS]]></result_code>
//<return_code><![CDATA[SUCCESS]]></return_code>
//<sign><![CDATA[D32461D6A15A68305CB4320C7B440442]]></sign>
//<time_end><![CDATA[20151103155438]]></time_end>
//<total_fee>1</total_fee>
//<trade_type><![CDATA[NATIVE]]></trade_type>
//<transaction_id><![CDATA[1003330836201511031439852893]]></transaction_id>
//</xml>";
        $data=\Tool_Xml::parse($data);
        if(isset($data['return_code']) && $data['return_code']=='SUCCESS'){
            // todo 暂不做签名验证
            return [
                'id'=>$data['out_trade_no'],
                'is_subscribe'=>$data['is_subscribe'],
                'openid'=>$data['openid'],
                'attach'=>isset($data['attach'])?$data['attach']:"",
                'time_end'=>strtotime($data['time_end']),
                'transaction_id'=>$data['transaction_id'],
                'money'=>$data['total_fee']/100,
            ];
            if($data['sign']==$this->makeSign($data)){

            }else{
                return '签名验证失败';
            }
        }else{
            return '解析数据失败';
        }
    }

    public function jspayconfig($prepay_id,$json=true) {
        $data=[
            'appId'=>$this->app_id,
            'timeStamp'=>(string)$_SERVER['REQUEST_TIME'],
            'nonceStr'=>$this->getNonceStr(),
            'package'=>'prepay_id='.$prepay_id,
            'signType'=>'MD5',
        ];
        $data['paySign']=$this->makeSign($data);
        return $json?$this->jsonEncode($data):$data;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function toUrlParams($data)
    {
        $buff = "";
        foreach ($data as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 生成签名
     * @return string 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function makeSign($data)
    {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = $this->toUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".\Config::get('weixin.paykey');
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

}