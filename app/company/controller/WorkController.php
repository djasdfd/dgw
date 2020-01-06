<?php
namespace app\company\controller;

use cmf\controller\AdminBaseController;
use app\company\model\WorkModel;
use app\company\model\TypeModel;
use app\company\model\SendModel;
use app\company\model\HistoryModel;
use think\Exception;

class WorkController extends AdminBaseController
{
    private $model_work;

    private $model_type;

    private $model_send;

    private $company_id;

    private $model_history;

    public function __construct()
    {
        parent::__construct();
        $this->model_work = new WorkModel();
        $this->model_type = new Typemodel();
        $this->model_send = new SendModel();
        $this->model_history = new HistoryModel();
        $this->company_id = cmf_get_current_company_id();
    }

    public function work(){
        $where['company_id'] = $this->company_id;
        $name = request()->param('work_name');
        $type = request()->param('work_type',0,'intval');
        $status = request()->param('status',0,'intval');
        if(isset($name) && $name != '')
            $where['name'] = ['like','%'.$name.'%'];
        if(!empty($type))
            $where['type'] = $type;
        if(!empty($status))
            $where['status'] =$status;
        $data = $this->model_work->where($where)->field('id,name,tag,type,status')->order('status ASC')->paginate(10)->each(function($item){
            $item->tag_text = implode(',',$this->model_type->getTypeNameArray($item['tag']));
            $item->type_text = $this->model_type->getTypeNameById($item['type']);
            $item->status_text = ($item->status == 1)?'招聘中':'停止招聘';
            return $item;
        });
        $type_array = $this->model_type->where([
            'type'=>['neq',0]
        ])->field('id,name')->select();
        return $this->fetch('',['list'=>$data,'page'=>$data->render(),'types'=>$type_array]);
    }

    public function workSort(){
        $name = request()->param('work_name');
        $type = request()->param('work_type',0,'intval');
        $where['w.status'] = 1;
        if(isset($name) && $name != '')
            $where['w.name|c.short_name'] = ['like','%'.$name.'%'];
        if(!empty($type))
            $where['w.type'] = $type;
        $data = $this->model_work
            ->alias('w')
            ->join([
                ['Company c','w.company_id = c.id','LEFT'],
            ])
            ->where($where)->field('w.id,w.name,w.type,w.sort,c.short_name')->order('sort ASC')->paginate(10)->each(function($item){
            $item->type_text = $this->model_type->getTypeNameById($item['type']);
            return $item;
        });
        $type_array = $this->model_type->where([
            'type'=>['neq',0]
        ])->field('id,name')->select();
        return $this->fetch('',['list'=>$data,'page'=>$data->render(),'types'=>$type_array]);
    }


    public function changeWorkSort(){
        $work_id = request()->param('work_id',0,'intval');
        $sort = request()->param('sort',50,'intval');
        $this->model_work->where('id',$work_id)->update(['sort'=>$sort]);
        $this->success('成功');
    }

    public function editWork(){
        $id = request()->param('id',0,'intval');
        $edit = empty($id)?0:1;
        if(request()->isAjax()){
            $name = request()->param('name',null);
            $top = request()->param('top',0,'intval');
            $type = request()->param('type',0,'intval');
            $tags = empty($_POST['tag'])?array():$_POST['tag'];
            $supply = request()->param('supply',null);
            $comprehensive_salary = request()->param('base_info.comprehensive_salary',null);
            $pay_day = request()->param('base_info.pay_day',null);
            $base_salary = request()->param('base_info.base_salary',null);
            $salary_structure = request()->param('base_info.salary_structure',null);
            $food = request()->param('food_info.food',null);
            $live = request()->param('food_info.live',null);
            $traffic = request()->param('food_info.traffic',null);
            $contract_explain = request()->param('contract_info.contract_explain',null);
            $wage_payment = request()->param('contract_info.wage_payment',null);
            $insurance_explain = request()->param('contract_info.insurance_explain',null);
            $work_content = request()->param('work_status.work_content',null);
            $work_explain = request()->param('work_status.work_explain',null);
            $work_environment = request()->param('work_status.work_environment',null);
            $id_card = request()->param('employ_info.id_card',null);
            $age = request()->param('employ_info.age',null);
            $english_word = request()->param('employ_info.english_word',null);
            $simply_math = request()->param('employ_info.simply_math',null);
            $face_check = request()->param('employ_info.face_check',null);
            $clean_clothes = request()->param('employ_info.clean_clothes',null);
            $foreign_matter = request()->param('employ_info.foreign_matter',null);
            $check_explain = request()->param('other_info.check_explain',null);
            $id_card_copy = request()->param('other_info.id_card_copy',null);
            $graduation_copy = request()->param('other_info.graduation_copy',null);
            $photo = request()->param('other_info.photo',null);
            empty($name) && $this->error('请填写职位名称');
            if(mb_strlen($name,'utf8')>30)
                $this->error('职位名称不大于30个字');
            if($top == -1)
                $this->error('请选择职位类型');
            empty($type) && $this->error('请选择职位类型');
            empty($tags) && $this->error('请选择标签');
            empty($supply) && $this->error('请填写职位补贴');
            if(mb_strlen($supply,'utf8')>10)
                $this->error('职位补贴不超过10个字');
            empty($comprehensive_salary) && $this->error('请填写综合薪资');
            empty($pay_day) && $this->error('请填写发薪日');
            empty($base_salary) && $this->error('请填写底薪');
            empty($salary_structure) && $this->error('请填写薪资结构');
            empty($food) && $this->error('请填写伙食');
            empty($live) && $this->error('请填写住宿');
            empty($traffic) && $this->error('请填写交通');
            empty($contract_explain) && $this->error('请填写合同说明');
            empty($wage_payment) && $this->error('请填写工资发放');
            empty($insurance_explain) && $this->error('请填写保险说明');
            empty($work_content) && $this->error('请填写工作内容');
            empty($work_explain) && $this->error('请填写工时说明');
            empty($work_environment) && $this->error('请填写工作环境');
            empty($id_card) && $this->error('请填写身份证说明');
            empty($age) && $this->error('请填写年龄要求');
            empty($english_word) && $this->error('请填写英语字母要求');
            empty($simply_math) && $this->error('请填写简单数学');
            empty($face_check) && $this->error('请填写人脸识别');
            empty($clean_clothes) && $this->error('请填写是否需要无尘服');
            empty($foreign_matter) && $this->error('请填写是否检查体内异物');
            empty($check_explain) && $this->error('请填写体检说明');
            empty($id_card_copy) && $this->error('请填写身份证复印件');
            empty($graduation_copy) && $this->error('请填写毕业证复印件');
            empty($photo) && $this->error('请填写照片');
            $data = [
                'name'=>$name,
                'top'=>$top,
                'type'=>$type,
                'tag'=>implode(',',$tags),
                'supply'=>$supply,
                'company_id'=>$this->company_id,
                'base_info'=>serialize($_POST['base_info']),
                'food_info'=>serialize($_POST['food_info']),
                'contract_info'=>serialize($_POST['contract_info']),
                'work_status'=>serialize($_POST['work_status']),
                'other_info'=>serialize($_POST['other_info']),
                'employ_info'=>serialize($_POST['employ_info'])
            ];
            try{
                if($edit){
                    $this->model_work->save($data,['id'=>$id]);
                }else {
                    $this->model_work->save($data);
                }
            }catch(Exception $e){
                $this->error($e->getMessage());
            }
            $this->success('操作成功');
        }
        $tags = $this->model_type->where(['type'=>0,'status'=>1])->field('id,name')->select();
        $data = $this->model_work->getWorkInfo($id);
        return $this->fetch('',['edit'=>$edit,'tag'=>$tags,'data'=>$data]);
    }


    public function getType($type = -1){
        $list = $this->model_type->where(['type'=>$type,'status'=>1])->field('id,name')->select();
        $this->success('成功','',$list);
    }

    public function changeWorkStatus(){
        $id = request()->param('id',0,'intval');
        $status = request()->param('status',1,'intval');
        try{
            $this->model_work->save(['status'=>$status],['id'=>$id]);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }

    public function copyWork(){
        $id = request()->param('id',0,'intval');
        $data = $this->model_work->get($id);
        empty($data) && $this->error();
        $data->name .= '(复制)';
        $data->status = 2;
        $data->is_recommend = 0;
        unset($data->id);
        $data = $data->toArray();
        try{
            $this->model_work->save($data);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }

    public function sendWorkPerson(){
        $work_id = request()->param('work_id',0,'intval');
        $step = request()->param('step',1,'intval');
        $data = $this->model_send->alias('s')->join([
            ['Account a','s.account_id = a.id','LEFT']
        ])->where([
            's.work_id'=>$work_id,
            's.step'=>$step,
        ])->field('s.*,a.sex,a.type,a.avatar,a.username,a.phone')->paginate(10)->each(function($item) use($work_id){
            $item->sex_text = $item->sex == 1?'男':'女';
            $item->type_text = $item->type == 1?'学生工':'社会工';
            $item->member = $this->model_history->where([
                'account_id'=>$item->account_id,
                'company_id'=>$this->company_id,
                'end_time'=>0,
            ])->count();
            return $item;
        });
        return $this->fetch('',['list'=>$data,'page'=>$data->render(),'work_id'=>$work_id]);
    }

    public function dealStep(){
        $id =request()->param('id');
        $id_array =  explode(',',$id);
        $step = request()->param('step',0,'intval');
        $remark = request()->param('remark');
        if(empty($remark))
            $this->error('请填写留言');
        mb_strlen($remark,'utf-8')>200 && $this->error('留言不超过200个字符');
        try{
            $this->model_send->save(['step'=>$step,'remark'=>$remark],['id'=>['in',$id_array]]);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }

    public function becomeMember(){
        $account_id = request()->param('account_id',0,'intval');
        $work_id = request()->param('work_id',0,'intval');
        try{
            $this->model_history->save([
                'account_id'=>$account_id,
                'company_id'=>$this->company_id,
                'work_id'=>$work_id,
                'start_time'=>time(),
            ]);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }



}