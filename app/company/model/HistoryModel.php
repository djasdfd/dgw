<?php
namespace app\company\model;

use think\Model;
class HistoryModel extends Model
{
    private $model_company_picture;

    public function __construct($data=[])
    {
        parent::__construct($data);
        $this->model_company_picture = new CompanyPictureModel();
    }

    public function likeList(int $page,int $limit,int $user){
        $where['account_id'] = $user;
        $where['end_time'] = ['neq',0];
        $where['like'] = ['neq',0];
        $count = $this->where($where)->count();
        $data_list = $this->alias('h')->join('Company c','h.company_id = c.id','LEFT')->where($where)->field('h.*,c.short_name')->page($page,$limit)->order('end_time DESC')->select()->toArray();
        $list = array_map(function($item){
            $item['like_text'] = $item['like'] == 1?'好评':($item['like'] == 2?'中评':'差评');
            $item['picture']= $this->model_company_picture->getCompanyPicture($item['company_id']);
            return $item;
        },$data_list);
        $data = paginate($page,$limit,$count,$list);
        return $data;
    }

    public function JobList(int $page,int $limit,int $user){
        $where['account_id'] = $user;
        $count = $this->where($where)->count();
        $data_list = $this->alias('h')->join('Company c','h.company_id = c.id','LEFT')->where($where)->field('h.*,c.short_name')->page($page,$limit)->order('start_time DESC')->select()->toArray();
        $list = array_map(function($item){
            $item['work_time'] = date('Y-m-d',$item['start_time']).(empty($item['end_time'])?'至今':'至'.date('Y-m-d',$item['end_time']));
            $item['picture']= $this->model_company_picture->getCompanyPicture($item['company_id']);
            return $item;
        },$data_list);
        $data = paginate($page,$limit,$count,$list);
        return $data;
    }


}