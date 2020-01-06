<?php
namespace app\api\controller;

use think\Controller;

class BaseApiController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->allowAll();
    }

    /**
     *  Header 允许跨域发访问并携带Cookie
     */
    protected function allowAll(){
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:GET,PUT,DELETE,POST,OPTIONS');
        header('Access-Control-Allow-Headers:x-requested-with,content-type,openid');
        header('Access-Control-Expose-Headers:*');
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
            exit;
        }

    }

    protected function checkLogin(){
        $openid = $this->request->header('openid');
        if(empty($openid))
            $this->result('',$code=-1,$msg = '未登录',$type='json');
        return (new \app\company\model\AccountModel())->where('openid',$openid)->value('id');
        
    }


    protected function getIdCard(){
        $openid = $this->request->header('openid');
        return (new \app\company\model\AccountModel())->where('openid',$openid)->value('id_card');
    }

}