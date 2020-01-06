<?php
namespace app\api\controller;
use app\company\model\AccountModel;
use think\Exception;
use api\model\Wxlogin\Wxlogin;
use think\Model;
use api\model\Wxlogin\GetNewAccess_token;
class LoginController extends BaseApiController
{
    private $appId;

    private $secret;

    private $model_account;

    public function __construct()
    {
        parent::__construct();
        $this->appId = "wx941b57eced98c631";
        $this->secret = "467de1e9df41ec97b50bbbeb78292c01";
        $this->model_account = new AccountModel();
    }

    /**
     * @url login
     */
    public function login(){

        $appId = $this->appId;
        $secret = $this->secret;
        $work=0;
        $filetoken=ROOT_PATH.'access_token.json';
        $file = file_get_contents($filetoken,true);
        $file = json_decode($file,true);
        if(!empty($file)){
            $work=1;
        }
        if($work==0){
            if (time() > $file['expires_in'] or !empty($file['errcode'])){
                $work=1;
            }
        }
        if($work==1){
            $data = array();
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$secret}";
            $Result=XiusoftCurl($url,'',0,true);
            /*$Result=json_decode($Result,true);
            $jsonStr =  json_encode($data);*/
            $fp = fopen($filetoken, "w");
            fwrite($fp, $Result);
            fclose($fp);
        }
        /*$scope = "snsapi_base";//这里使用静默授权
        //配置获取 openid 路径的相关参数
        $code = request()->param('code');
        $paramsArr                = array();
        $paramsArr ["appid"]      = $appId;
        $paramsArr ["secret"]     = $secret;
        $paramsArr ["code"]       = $code;
        $paramsArr ["grant_type"] = "authorization_code";
        $paramurl = http_build_query($paramsArr , '' , '&');
        $openidUrl =  "https://api.weixin.qq.com/sns/jscode2session?" . $paramurl;
        $Result=XiusoftCurl($openidUrl,'',0,true);
        var_dump($Result);die;*/
        if(request()->isPost()){
            $code = request()->param('code');
            if(empty($code))
                $this->result('',$code=0,$msg = '缺少参数',$type='json');
            $oauth2Url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appId."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
            $oauth2 = http_curl($oauth2Url);
            //access_token
            empty($oauth2['access_token']) && $this->result('',$code=0,$msg = '登录失败,access_token失效',$type='json');
            $access_token = $oauth2['access_token'];
            //openid
            empty($oauth2['openid']) && $this->result('',$code=0,$msg = '登录失败,openid未能获取',$type='json');
            $openid = $oauth2['openid'];
            //第二步:根据全局access_token和openid查询用户信息
            $get_user_info_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
            $user_info = http_curl($get_user_info_url);
            $account_info = $this->model_account->get(['openId'=>$openid]);
            if(empty($account_info)){
                $id = 0;
                try{
                    $id = $this->model_account->insertGetId(array(
                        'openId'=>$openid,
                        'sex'=>$user_info['sex'],
                        'username'=>$user_info['nickname'],
                        'avatar'=>$user_info['headimgurl'],
                        'create_time'=>time()
                    ));
                }catch(Exception $e){
                    $this->result('',$code=0,$msg = $e->getMessage(),$type='json');
                }
            }else{
                $account_info->status = 0 && $this->result('',$code=0,$msg = '用户禁止操作',$type='json');

            }

            $this->result($openid,$code=1,$msg='登陆成功',$type="json");
        }
        $this->result('',$code=0,$msg = '非法操作',$type='json');
    }
}