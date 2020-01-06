<?php
namespace app\company\model;

use think\Model;

class WorkModel extends Model
{
    private $model_company_picture;

    private $model_type;

    private $model_search;

    private $model_follow;

    private $model_send;

    public function __construct($data=[])
    {
        parent::__construct($data);
        $this->model_company_picture = new CompanyPictureModel();
        $this->model_type = new TypeModel();
        $this->model_search = new SearchModel();
        $this->model_follow = new FollowModel();
        $this->model_send = new SendModel();
    }

    public function getWorkInfo(int $id):array
    {
        $data = $this->get($id);
        if(empty($data))
            return array();
        $data = $data->toArray();
        $data['tag_array'] = explode(',',$data['tag']);
        $data['base_info'] = unserialize($data['base_info']);
        $data['food_info'] = unserialize($data['food_info']);
        $data['contract_info'] = unserialize($data['contract_info']);
        $data['work_status'] = unserialize($data['work_status']);
        $data['other_info'] = unserialize($data['other_info']);
        $data['employ_info'] = unserialize($data['employ_info']);
        return $data;
    }

    public function getWorkList(Array $where,int $page,int $limit):array
    {
        $count = $this->alias('w')->join([
            ['Company c','c.id = w.company_id','LEFT']
        ])->where($where)->count();
        $data_list = $this->alias('w')->join([
                ['Company c','c.id = w.company_id','LEFT']
            ])->where($where)->field('c.short_name,w.supply,w.base_info,w.company_id,w.id,w.tag')->order('w.sort ASC')->page($page,$limit)->select()->toArray();
        $list = array_map(function($item){
            $item['salary'] = unserialize($item['base_info'])['comprehensive_salary'];
            $item['picture']= $this->model_company_picture->getCompanyPicture($item['company_id']);
            $item['tag'] = $this->model_type->getTypeNameArray($item['tag']);
            unset($item['base_info'],$item['company_id']);
            return $item;
        },$data_list);
        $data = paginate($page,$limit,$count,$list);
        return $data;
    }

    public function searchWork(string $work,int $page,int $limit,int $user){
        $where['c.name|c.short_name'] = ['like','%'.$work.'%'];
        $where['w.status'] = 1;
        $count = $this->alias('w')->join([
            ['Company c','c.id = w.company_id','LEFT']
        ])->where($where)->count();
        if(!empty($count))
            $this->model_search->addHistory($work,$user);
        $data_list = $this->alias('w')->join([
            ['Company c','c.id = w.company_id','LEFT']
        ])->where($where)->field('c.short_name,w.supply,w.base_info,w.company_id,w.id,w.tag')->page($page,$limit)->select()->toArray();
        $list = array_map(function($item){
            $item['salary'] = unserialize($item['base_info'])['comprehensive_salary'];
            $item['picture']= $this->model_company_picture->getCompanyPicture($item['company_id']);
            $item['tag'] = $this->model_type->getTypeNameArray($item['tag']);
            unset($item['base_info'],$item['company_id']);
            return $item;
        },$data_list);
        $data = paginate($page,$limit,$count,$list);
        return $data;
    }

    public function getRecommendWork(array $where):array
    {
        $data_list = $this->alias('w')->join([
            ['Company c','c.id = w.company_id','LEFT']
        ])->where($where)->field('c.short_name,w.supply,w.base_info,w.company_id,w.id,w.tag')->find();
        if(empty($data_list))
            return array();
        $list = $data_list->toArray();
        $list['salary'] = unserialize($list['base_info'])['comprehensive_salary'];
        $list['picture']= $this->model_company_picture->getCompanyPicture($list['company_id']);
        $list['tag'] = $this->model_type->getTypeNameArray($list['tag']);
        unset($list['base_info'],$list['company_id']);
        return $list;
    }


    public function getWorkDetail(int $id,int $user):array
    {
        $data_info = $this->alias('w')->join([
            ['Company c','c.id = w.company_id','LEFT']
        ])->where('w.id',$id)->field('w.company_id,w.id,w.top,w.base_info,w.food_info,w.contract_info,w.work_status,w.other_info,w.employ_info,w.status,c.introduce,c.area,c.longitude,c.latitude,c.mobile,c.short_name')->find();
        if(empty($data_info))
            return array();
        $data = $data_info->toArray();
        $data['base_info'] = unserialize($data['base_info']);
        $data['food_info'] = unserialize($data['food_info']);
        $data['contract_info'] = unserialize($data['contract_info']);
        $data['work_status'] = unserialize($data['work_status']);
        $data['other_info'] = unserialize($data['other_info']);
        $data['employ_info'] = unserialize($data['employ_info']);
        $data['company_picture'] = $this->model_company_picture->getPictureArray($data['company_id']);
        $data['is_follow'] = $this->model_follow->where(['work_id'=>$id,'account_id'=>$user])->count();
        $data['is_send'] = $this->model_send->where(['work_id'=>$id,'account_id'=>$user])->count();
        unset($data['company_id']);
        return $data;
    }

    public function getWorkNumByCompany(int $id){
        return  $this->where(['company_id'=>$id,'status'=>1])->count();
    }
}