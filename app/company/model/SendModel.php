<?php

namespace app\company\model;



use think\Model;

class SendModel extends Model

{

    private $model_company_picture;



    private $model_type;



    private $model_company;



    public function __construct($data=[])

    {

        parent::__construct($data);

        $this->model_company_picture = new CompanyPictureModel();

        $this->model_type = new TypeModel();

        $this->model_company = new CompanyModel();

    }



    public function sendList(int $page,int $limit,int $user){

        $where['account_id'] = $user;

        $count = $this->where($where)->count();

        $data_list = $this->alias('s')->join([

            ['Work w','s.work_id = w.id','LEFT']

        ])->where($where)->field('s.create_time,s.step,s.id,w.supply,w.base_info,w.company_id,s.work_id,w.tag')->order('s.create_time DESC')->page($page,$limit)->select()->toArray();

        $list = array_map(function($item){

            $item['step_text'] = $this->getStepInfo($item['step']);

            $item['salary'] = unserialize($item['base_info'])['comprehensive_salary'];

            $item['picture']= $this->model_company_picture->getCompanyPicture($item['company_id']);

            $item['tag'] = $this->model_type->getTypeNameArray($item['tag']);

            $item['short_name'] = $this->model_company->where('id',$item['company_id'])->value('short_name');

            $item['create_time']  = date('Y-m-d',$item['create_time']);

            unset($item['base_info'],$item['company_id']);

            return $item;

        },$data_list);

        $data = paginate($page,$limit,$count,$list);

        return $data;

    }



    public function sendInfo($id){

        $data = $this->alias('s')->join([

            ['Work w','s.work_id = w.id','LEFT']

        ])->field('s.step,s.id,s.remark,w.supply,w.base_info,w.company_id,w.tag')->where('s.id',$id)->find()->toArray();

        $data['step_text'] = $this->getStepInfo($data['step']);

        $data['salary'] = unserialize($data['base_info'])['comprehensive_salary'];

        $data['picture']= $this->model_company_picture->getCompanyPicture($data['company_id']);

        $data['tag'] = $this->model_type->getTypeNameArray($data['tag']);

        $data['short_name'] = $this->model_company->where('id',$data['company_id'])->value('short_name');

        $data['longitude'] = $this->model_company->where('id',$data['company_id'])->value('longitude');

        $data['latitude'] = $this->model_company->where('id',$data['company_id'])->value('latitude');

        $data['area'] = $this->model_company->where('id',$data['company_id'])->value('area');

        $data['mobile'] = $this->model_company->where('id',$data['company_id'])->value('mobile');

        return $data;

    }



    public function getStepInfo($step){



        switch($step){

            case 1:{

                $data = '已报名';

            }break;

            case 2:{

                $data = '未通过';

            }break;

            case 3:{

                $data = '未处理';

            }break;

            case 4:{

                $data = '已有他选';

            }break;

            case 5:{

                $data = '确认前往';

            }break;

            default:

            break;

        }

        return empty($data)?'':$data;

    }

    public  function newSend($user){
      return $this->where(['account_id'=>$user,'step'=>3])->count();
    }



}