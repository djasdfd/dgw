<?php
namespace app\api\controller;

use think\Controller;

use plugins\alimobile_code\AlimobileCodePlugin;

class MobileController extends BaseApiController{

    public function __construct()
    {
        parent::__construct();
    }

    public function sendMessage(){

        $mobile =request()->param('mobile');
        !cmf_check_mobile($mobile) &&   $this->result('',$code=0,$msg = '手机号码不正确',$type='json');
        $code = cmf_get_verification_code($mobile);
        empty($code) && $this->result('',$code=0,$msg = '验证码超过限制',$type='json');
        cmf_verification_code_log($mobile,$code);
        $info = (new AlimobileCodePlugin())->sendMobileVerificationCode(['mobile'=>$mobile,'code'=>$code,'scene'=>'SMS_172209602']);
        empty($info) && $this->result('',$code=0,$msg='系统错误',$type="json");
        switch($info->Code){
            case 'OK':{
                $this->result('',$code=1,$msg='发送成功',$type="json");
            }break;
            case 'isv.MOBILE_COUNT_OVER_LIMIT':{
                $this->result('',$code=0,$msg='手机验证码超过限制',$type="json");
            }break;
            default:{
                $this->result('',$code=0,$msg='短信系统错误',$type="json");
            }
        }


    }

    public function checkMessage(){
        $mobile = request()->param('mobile');
        $code = request()->param('code');
        $msg = cmf_check_verification_code($mobile, $code);
        empty($msg) && $this->result('', $code = 1, '验证通过', $type = "json");
        $this->result('', $code = 1, $msg, $type = "json");
    }

}
