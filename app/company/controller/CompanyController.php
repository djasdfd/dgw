<?php
namespace app\company\controller;

use cmf\controller\AdminBaseController;
use app\company\model\TypeModel;
use app\company\model\CompanyModel;
use app\company\model\CompanyPictureModel;
use app\company\model\AddressModel;
use app\company\model\WorkModel;
use app\company\model\SendModel;
use app\company\model\HistoryModel;
use think\Db;
use think\Exception;

class CompanyController extends AdminBaseController
{

    private $model_type;

    private $model_company;

    private $model_company_picture;

    private $model_address;

    private $company_id; //公司id

    private $model_work;

    private $model_send;

    private $model_history;

    public function __construct()
    {
        parent::__construct();
        $this->model_type = new TypeModel();
        $this->model_company = new CompanyModel();
        $this->model_company_picture = new CompanyPictureModel();
        $this->model_address = new AddressModel();
        $this->model_work = new WorkModel();
        $this->model_send = new SendModel();
        $this->model_history = new HistoryModel();
        $this->company_id = cmf_get_current_company_id();
    }


    public function lists():string
    {
        $where = array();
        $short_name = request()->param('short_name');
        $user_status = request()->param('user_status','');
        $province =request()->param('province',-1,'intval');
        $city = request()->param('city',0,'intval');
        if(isset($short_name) && $short_name != '')
            $where['c.short_name'] = ['like','%'.$short_name.'%'];
        if(isset($user_status) && $user_status != '')
            $where['u.user_status'] = $user_status;
        if($province != -1)
            $where['c.province'] = $province;
        if(!empty($city))
            $where['c.city'] = $city;
        $data = $this->model_company->alias('c')->join([
            ['User u','c.id = u.id','LEFT'],
        ])->where($where)->field('c.id,c.short_name,c.province,c.city,c.mobile,u.user_status')->order('u.user_status DESC')->paginate(10)->each(
            function($item){
                $item->province_text = $this->model_address->getNameById($item->province);
                $item->city_text = $this->model_address->getNameById($item->city);
                $item->user_status_text = $item->user_status == 1?'正常':'禁用';
                $item->work_num = $this->model_work->getWorkNumByCompany($item->id);
                return $item;
            }
        );
        return $this->fetch('',['list'=>$data,'page'=>$data->render()]);
    }

    public function workInfo(){
        $company_id = request()->param('company_id',0,'intval');
        $data = $this->model_work->where(['status'=>1,'company_id'=>$company_id])->field('id,name,tag,type,is_recommend')->paginate(10)->each(function($item){
            $item->tag_text = implode(',',$this->model_type->getTypeNameArray($item['tag']));
            $item->type_text = $this->model_type->getTypeNameById($item['type']);
            $item->is_recommend_text = empty($item->is_recommend)?'普通':'推荐';
            $item->send_person = $this->model_send->where('work_id',$item->id)->count();
            $item->member = $this->model_history->where('work_id',$item->id)->count();
            return $item;
        });
        return $this->fetch('',['list'=>$data,'page'=>$data->render()]);
    }

    public function recommend(){
        $id = request()->param('id',0,'intval');
        try{
            $this->model_work->save(['is_recommend'=>1],['id'=>$id]);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }

    public function notRecommend(){
        $id = request()->param('id',0,'intval');
        try{
            $this->model_work->save(['is_recommend'=>0],['id'=>$id]);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }

    public function type():string
    {
        $data = $this->model_type->where(['status'=>1,'type'=>['neq',0]])->field('id,name,type,picture')->paginate(10);
        return $this->fetch('',['list'=>$data,'page'=>$data->render()]);
    }

    public function editType():string
    {
        $id = request()->param('id',0,'intval');
        $edit = empty($id)?0:1;
        if(request()->isAjax()){
            $type = request()->param('type',0,'intval');
            $name = request()->param('type_name',null,'htmlentities');
            $picture = request()->param('type_picture',null,'string');
            empty($type) && $this->error('请选择类型');
            empty($name) && $this->error('请填写名称');
            empty($picture) && $this->error('请上传图片');
            if(mb_strlen($name,'utf-8')>4)
                $this->error('名称不大于4个字');
            try{
                if($edit){
                    $this->model_type->save(['name'=>$name,'type'=>$type,'picture'=>$picture],['id'=>$id]);
                }else{
                    $this->model_type->save(['name'=>$name,'type'=>$type,'picture'=>$picture]);
                }
            }catch(Exception $e){
                $this->error($e->getMessage());
            }
            $this->success('操作成功',url('type'));
        }
        $data = $this->model_type->get($id);
        return $this->fetch('',['data'=>$data,'edit'=>$edit]);
    }


    public function tag():string
    {
        $data = $this->model_type->where(['status'=>1,'type'=>0])->field('id,name')->paginate(10);
        return $this->fetch('',['list'=>$data,'page'=>$data->render()]);
    }

    public function editTag():string
    {
        $id = request()->param('id',0,'intval');
        $edit = $id?1:0;
        if(request()->isAjax()){
            $name = request()->param('tag_name',null,'htmlentities');
            empty($name) && $this->error('请填写标签名称');
            if(mb_strlen($name,'utf-8')>10)
                $this->error('标签不大于10个字');
            try{
                if($edit){
                    $this->model_type->save(['name'=>$name],['id'=>$id]);
                }else{
                    $this->model_type->save(['name'=>$name]);
                }
            }catch(Exception $e){
                $this->error($e->getMessage());
            }
            $this->success('操作成功',url('tag'));
        }
        $data = $this->model_type->get($id);
        return $this->fetch('',['data'=>$data,'edit'=>$edit]);
    }

    public function deleteTag():string
    {
        $id = request()->param('id',0,'intval');
        empty($id) && $this->error('非法操作');
        try{
            $this->model_type->where('id',$id)->setfield('status',0);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }


    public function uploadImage(){
        $file = $this->request->file('file');
        $http_type = ($this->request->isSsl()) ? 'https://' : 'http://';
        $cdn_path = $http_type . $_SERVER['HTTP_HOST'] . DS . 'upload'.DS.'work_type';
        $upload_path = ROOT_PATH . 'public' . DS . 'upload'.DS.'work_type';
        $info = $file->validate(['size' => 1024 * 1024 * 5, 'ext' => 'jpg,png,gif,jpeg'])->move($upload_path);
        if ($info) {
            $data = $cdn_path . DS . $info->getSaveName();
            $this->success('上传成功','',$data);
        } else {
            $wrong = $file->getError();
            $this->error($wrong);
        }
    }

    public function info():string
    {
        $data = $this->model_company->get($this->company_id);
        $edit = empty($data)?0:1;
        if(request()->isAjax()){
            $name = request()->param('name',null,'htmlentities');
            $short_name = request()->param('short_name',null,'htmlentities');
            $mobile = request()->param('mobile',null,'string');
            $province = request()->param('province',0,'intval');
            $city = request()->param('city',0,'intval');
            $area = request()->param('area',null,'htmlentities');
            $longitude  = request()->param('longitude',null);
            $latitude = request()->param('latitude',null);
            $introduce  = request()->param('introduce',null,'htmlentities');
            empty($name) && $this->error('请输入公司名称');
            if(mb_strlen($name,'utf-8')>40)
                $this->error('公司名称不大于40个字');
            empty($short_name) && $this->error('请输入公司短名称');
            if(mb_strlen($short_name,'utf-8')>10)
                $this->error('公司短名称不大于10个字');
            empty($mobile) && $this->error('请输入客服电话');
            !cmf_check_mobile($mobile) && $this->error('客服电话不正确');
            if($province == -1)
                $this->error('请选择省份');
            empty($city) && $this->error('请选择城市');
            empty($area) && $this->error('请输入详细地址');
            empty($longitude) && $this->error('请输入详细地址，查询经纬度');
            empty($latitude) && $this->error('请输入详细地址，查询经纬度');
            empty($introduce) && $this->error('请输入公司简介');
            try{
                if($edit) {
                    $this->model_company->save([
                        'name' => $name,
                        'short_name' => $short_name,
                        'mobile' => $mobile,
                        'province' => $province,
                        'city' => $city,
                        'area' => $area,
                        'longitude' => $longitude,
                        'latitude' => $latitude,
                        'introduce' => $introduce,
                    ], ['id' => $this->company_id]);
                }else{
                    $this->model_company->save([
                        'name' => $name,
                        'short_name' => $short_name,
                        'mobile' => $mobile,
                        'province' => $province,
                        'city' => $city,
                        'area' => $area,
                        'longitude' => $longitude,
                        'latitude' => $latitude,
                        'introduce' => $introduce,
                        'id'=>$this->company_id
                    ]);
                }
            }catch(Exception $e){
                $this->error($e->getMessage());
            }
            $this->success('操作成功');
        }
        return $this->fetch('',['data'=>$data]);
    }

    public function picture(){
        $data = $this->model_company_picture->where('company_id',$this->company_id)->paginate(10);
        return $this->fetch('',['data'=>$data,'page'=>$data->render()]);
    }

    public function addPicture(){
        if(request()->isAjax()){
            $url = $this->request->param('url',null);
            empty($url) && $this->error('请上传图片');
            try{
                $this->model_company_picture->save([
                    'url'=>$url,
                    'company_id'=>$this->company_id
                ]);
            }catch(Exception $e){
                $this->error($e->getMessage());
            }
            $this->success('操作成功');
        }
        return $this->fetch();
    }

    public function delPicture(){
        $id = $this->request->param('id',0,'intval');
        $info = $this->model_company_picture->get(['id'=>$id]);
        try{
            $this->model_company_picture->where('id',$id)->delete();
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
        $http_type = ($this->request->isSsl())?'https://':'http://';
        $cdn = $http_type.$_SERVER['HTTP_HOST'];
        $upload = ROOT_PATH.'public';
        $url = str_replace($cdn,$upload,$info->url);
        /**
         * 删除文件
         */
        /*if(file_exists($url) && is_readable($url)){
            unlink($url);
        }*/
        @unlink($url);
        $this->success('操作成功');
    }

    public function editPictureTop(){
        $id = $this->request->param('id',0,'intval');
        $top = $this->request->param('is_top',1,'intval');
        try{
            $this->model_company_picture->where('id',$id)->setField('is_top',$top);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }

    public function uploadPicture(){
        $file = $this->request->file('file');
        $http_type = ($this->request->isSsl()) ? 'https://' : 'http://';
        $cdn_path = $http_type . $_SERVER['HTTP_HOST'] . DS . 'upload'.DS.'company'.DS.$this->company_id;
        $upload_path = ROOT_PATH . 'public' . DS . 'upload'.DS.'company'.DS.$this->company_id;
        $info = $file->validate(['size' => 1024 * 1024 * 5, 'ext' => 'jpg,png,gif,jpeg'])->move($upload_path);
        if ($info) {
            $data = $cdn_path . DS . $info->getSaveName();
            $this->success('上传成功','',$data);
        } else {
            $wrong = $file->getError();
            $this->error($wrong);
        }
    }

    /**
     * 本站用户拉黑
     * @adminMenu(
     *     'name'   => '本站用户拉黑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户拉黑',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = Db::name("user")->where(["id" => $id])->setField('user_status', 0);
            if ($result) {
                $this->success("会员拉黑成功！");
            } else {
                $this->error('会员拉黑失败,会员不存在,或者是管理员！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 本站用户启用
     * @adminMenu(
     *     'name'   => '本站用户启用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户启用',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            Db::name("user")->where(["id" => $id])->setField('user_status', 1);
            $this->success("会员启用成功！");
        } else {
            $this->error('数据传入失败！');
        }
    }


    public function member(){
        $data = $this->model_history->alias('h')->join([
            ['Account a','h.account_id = a.id','LEFT']
        ])->where(['h.company_id'=>$this->company_id,'h.end_time'=>0])->field('h.*,a.username,a.phone,a.avatar')->paginate(10);
       return $this->fetch('',['list'=>$data,'page'=>$data->render()]);
    }

    public function leaveCompany(){
        $account_id = request()->param('account_id',0,'intval');
        if(request()->isPost()){
            empty($account_id) && $this->error('操作失败');
            $like = request()->param('like',0,'intval');
            empty($like) && $this->error('请选择评价');
            $has = $this->model_history->get(['company_id'=>$this->company_id,'account_id'=>$account_id,'like'=>['neq',0]]);
            $has && $this->error('已评价');
            $score = $like == 1?15:($like == 2?5:-50);
            $this->model_history->save(['end_time'=>time(),'like'=>$like],['company_id'=>$this->company_id,'account_id'=>$account_id]);
            Db::name("account")->where(["id" => $account_id])->setInc('score',$score);
            $this->success('评价成功');
        }
        return $this->fetch('',['account_id'=>$account_id]);
    }

}