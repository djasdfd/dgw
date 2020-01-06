<?php
namespace app\api\controller;
use app\company\model\WorkModel;
use app\company\model\CompanyModel;
use app\company\model\CompanyPictureModel;
use app\company\model\TypeModel;
use app\company\model\ConfigModel;


class WorkController extends BaseApiController
{
    private $model_work;

    private $model_company;

    private $model_company_picture;

    private $model_type;

    private $model_config;

    public function __construct()
    {
        parent::__construct();
        $this->model_work = new WorkModel();
        $this->model_company = new CompanyModel();
        $this->model_company_picture = new CompanyPictureModel();
        $this->model_type = new TypeModel();
        $this->model_config = new ConfigModel();
    }

    /**
     * @url workType
     */
    public function getWorkType(){
        if(request()->isPost()){
            $type = request()->param('auth',1,'intval');
            $this->result($this->model_type->getType($type),$code=1,$msg='success',$type='json');
        }
        $this->result('',$code=0,$msg = '非法操作',$type='json');
    }

    /**
     * @url workList
     */
    public function getWork(){
        if(request()->isPost()){
            $where['w.status'] = 1;
            $city = request()->param('city',0,'intval');
            !empty($city) && $where['c.city'] = $city;
            $top = request()->param('top',1,'intval');
            !empty($top) && $where['w.top'] = $top;
            $type = request()->param('type',0,'intval');
            !empty($type) && $where['w.type'] = $type;
            $page = request()->param('page',1,'intval');
            $limit = request()->param('limit',6,'intval');
            $this->result($this->model_work->getWorkList($where,$page,$limit),$code=1,$msg = 'success',$type='json');
        }
        $this->result('',$code=0,$msg = '非法操作',$type='json');
    }

    /**
     * @url recommendWork
     */
    public function recommendWork(){
        if(request()->isPost()){
            $where['is_recommend'] = 1;
            $where['w.status'] = 1;
            $city = request()->param('city',0,'intval');
            !empty($city) && $where['c.city'] = $city;
            $top = request()->param('top',1,'intval');
            !empty($top) && $where['w.top'] = $top;
            $type = request()->param('type',0,'intval');
            !empty($type) && $where['w.type'] = $type;
            $this->result($this->model_work->getRecommendWork($where),$code=1,$msg = 'success',$type='json');
        }
        $this->result('',$code=0,$msg = '非法操作',$type='json');
    }

    public function getWorkInfo(){
        if(request()->isPost()){
            $id = request()->param('id',0,'intval');
            empty($id) && $this->result('',$code=0,$msg = '工作参数为空',$type='json');
            $uid = $this->checkLogin();
            $this->result($this->model_work->getWorkDetail($id,$uid),$code=1,$msg = 'success',$type='json');
        }
        $this->result('',$code=0,$msg = '非法操作',$type='json');
    }

    /**
     * @url customerMobile
     */
    public function getPlatformMobile(){
        if(request()->isPost()){
            $this->result($this->model_config->where('id',1)->value('content'),$code=1,$msg = 'success',$type='json');
        }
        $this->result('',$code=0,$msg = '非法操作',$type='json');
    }

    /**
     * @url platformInfo
     */
    public function getPlatformInfo(){
        if(request()->isPost()){
            $this->result(html_entity_decode($this->model_config->where('id',2)->value('content')),$code=1,$msg = 'success',$type='json');
        }
        $this->result('',$code=0,$msg = '非法操作',$type='json');
    }
}