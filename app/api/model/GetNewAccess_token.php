<?php


namespace api\model\Wxlogin;
use app\company\model\AccountModel;
use think\Model;
use think\Url;

class GetNewAccess_token extends Model
{

    function getNewAccess_token(){
        $appId = "wx941b57eced98c631";
        $secret = "467de1e9df41ec97b50bbbeb78292c01";
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$secret";
        $Result=XiusoftCurl($url,'',0,true);
        $Result=json_decode($Result,true);
        return $Result['access_token'];
    }
    function getOpenid($code){
        $appId = "wx941b57eced98c631";
        $secret = "467de1e9df41ec97b50bbbeb78292c01";
        $server='https://api.weixin.qq.com/sns/jscode2session?';
        $sGet='appid='.$appId.'&secret='. $secret.'&js_code='.$code.'&grant_type=authorization_code';
        $url=$server.$sGet;
        $Result=XiusoftCurl($url,'',0,true);
        if($Result){
            return $Result;
        }else{
           return false;
        }
    }

}