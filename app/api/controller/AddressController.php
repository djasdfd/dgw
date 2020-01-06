<?php
namespace app\api\controller;
use app\company\model\AddressModel;
class AddressController extends BaseApiController
{
    private $model_address;

    public function __construct()
    {
        parent::__construct();
        $this->model_address = new AddressModel();
    }

    /**
     * 获取地区 (联动)
     */
    public function getRegion(){
        $pid = request()->param('pid',0,'intval');
        $this->result($this->model_address->getListByPid($pid), $code = 1, $msg = 'success', $type = 'json');
    }

    /**
     * @url city
     */
    public function getCity(){
        if(request()->isPost()){
            $this->result($this->model_address->getCity(),$code=1,$msg = 'success', $type = 'json');
        }
        $this->result('',$code=0,$msg = '非法操作',$type='json');
    }

    public function getCityId(){
        if(request()->isPost()){
            $name = request()->param('city',null,'rawurldecode');
            $this->result($this->model_address->getIdByName($name),$code=1,$msg='success',$type='json');
        }
        $this->result('',$code=0,$msg = '非法操作',$type='json');
    }


}