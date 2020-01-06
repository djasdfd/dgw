<?php
namespace app\company\controller;
use cmf\controller\AdminBaseController;
use app\company\model\ConfigModel;
use app\company\model\IdeaModel;
use app\company\model\JoinModel;
use think\Exception;

class PlatformController extends AdminBaseController
{

    private $model_config;

    private $model_idea;

    private $model_join;

    public function __construct()
    {
        parent::__construct();
        $this->model_config = new ConfigModel();
        $this->model_idea = new IdeaModel();
        $this->model_join = new JoinModel();
    }

    public function mobile(){
        if(request()->isAjax()){
            $id = request()->param('id',0,'intval');
            $content = request()->param('content');
            empty($content) && $this->error('请输入客服电话');
            !cmf_check_mobile($content) && $this->error('客服电话不正确');
            try{
                $this->model_config->save(['content'=>$content],['id'=>$id]);
            }catch(Exception $e){
                $this->error($e->getMessage());
            }
            $this->success('操作成功');
        }
        $data = $this->model_config->get(1);
        return $this->fetch('',['data'=>$data]);
    }

    public function info(){
        if(request()->isAjax()){
            $id = request()->param('id',0,'intval');
            $content = request()->param('content');
            empty($content) && $this->error('请填写内容');
            try{
                $this->model_config->save(['content'=>$content],['id'=>$id]);
            }catch(Exception $e){
                $this->error($e->getMessage());
            }
            $this->success('操作成功');
        }
        $data = $this->model_config->get(2);
        return $this->fetch('',['data'=>$data]);
    }

    public function idea(){
        $data = $this->model_idea->alias('i')->join([
            ['Account a','i.uid = a.id','LEFT']
        ])->where(['i.status'=>['neq',0]])->field('i.*,a.username')->order('i.status ASC')->paginate(10);
        return $this->fetch('',['list'=>$data,'page'=>$data->render()]);
    }

    public function deleteIdea(){
        $id = request()->param('id',0,'intval');
        $status = request()->param('status',2,'intval');
        try{
            $this->model_idea->save(['status'=>$status],['id'=>$id]);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }

    public function join(){
        $data = $this->model_join->where(['status'=>['neq',0]])->order('status ASC')->paginate(10);
        return $this->fetch('',['list'=>$data,'page'=>$data->render()]);
    }

    public function deleteJoin(){
        $id = request()->param('id',0,'intval');
        $status =request()->param('status',2,'intval');
        try{
            $this->model_join->save(['status'=>$status],['id'=>$id]);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }
}