<?php
namespace app\company\model;

use think\Model;
class FollowModel extends Model
{
    private $model_company_picture;

    private $model_company;

    private $model_type;

    public function __construct($data=[])
    {
        parent::__construct($data);
        $this->model_company_picture = new CompanyPictureModel();
        $this->model_type = new TypeModel();
        $this->model_company = new CompanyModel();
    }

    public function getFollowList(int $page,int $limit,int $user){
        $where['f.account_id'] = $user;
        $count = $this->alias('f')->join([
            ['Work w','f.work_id = w.id','LEFT']
        ])->where($where)->count();
        $data_list = $this->alias('f')->join([
            ['Work w','f.work_id = w.id','LEFT']
        ])->where($where)->field('w.supply,w.base_info,w.company_id,w.id,w.tag,w.status')->page($page,$limit)->select()->toArray();
        $list = array_map(function($item){
            $item['short_name'] = $this->model_company->where('id',$item['company_id'])->value('short_name');
            $item['salary'] = unserialize($item['base_info'])['comprehensive_salary'];
            $item['picture']= $this->model_company_picture->getCompanyPicture($item['company_id']);
            $item['tag'] = $this->model_type->getTypeNameArray($item['tag']);
            $item['status_text'] = $item['status'] == 1?'正在招聘':'停止招聘';
            unset($item['base_info'],$item['company_id']);
            return $item;
        },$data_list);
        $data = paginate($page,$limit,$count,$list);
        return $data;
    }
}