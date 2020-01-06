<?php
namespace api\model\Wxlogin;

use think\Model;
use think\Url;

class Wxlogin extends Model
{
    function GetAccess_token(){
        var_dump(11);die;
        $work=0;
        $filetoken=ROOT_PATH.'access_token.json';
        if(!file_exists($filetoken)){
            $work=1;
        }
        if($work==0){
            $file = file_get_contents($filetoken,true);
            $result = json_decode($file,true);
            if (time() > $result['expires']){
                $work=1;
            }
        }
        if($work==1){
            $data = array();
            $data['access_token'] =$this->getNewAccess_token();
            $data['expires']=time()+7000;
            $jsonStr =  json_encode($data);
            $fp = fopen($filetoken, "w");
            fwrite($fp, $jsonStr);
            fclose($fp);
            return $data['access_token'];
        }else{
            return $result['access_token'];
        }
    }
    function getOpenid($code){
        $appId = "wx941b57eced98c631";
        $secret = "467de1e9df41ec97b50bbbeb78292c01";
        $server='https://api.weixin.qq.com/sns/jscode2session?';
        $sGet='appid='.$appId.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';
        $url=$server.$sGet;
        $Result=XiusoftCurl($url,'',0,true);
        if($Result){
            return $Result;
        }else{
            return false;
        }
    }

}