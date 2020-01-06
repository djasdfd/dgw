<?php

namespace app\api\controller;

use app\company\model\AccountModel;

use app\company\model\IdeaModel;

use app\company\model\JoinModel;

use app\company\model\FollowModel;

use app\company\model\SendModel;

use app\company\model\WorkModel;

use app\company\model\SearchModel;

use app\company\model\TimeModel;

use app\company\model\HistoryModel;

use think\Exception;

use think\Db;



class AccountController extends BaseApiController

{

    private $uid;

    private $id_card;

    private $model_account;

    private $model_idea;

    private $model_join;

    private $model_follow;

    private $model_send;

    private $model_work;

    private $model_search;

    private $model_history;

    private $model_time;

    private $model_salary;



    public function __construct()

    {

        parent::__construct();

        $this->uid = $this->checkLogin();

        $this->id_card = $this->getIdCard();

        $this->model_account = new AccountModel();

        $this->model_idea = new IdeaModel();

        $this->model_join = new JoinModel();

        $this->model_follow = new FollowModel();

        $this->model_send = new SendModel();

        $this->model_work = new WorkModel();

        $this->model_search = new SearchModel();

        $this->model_time = new TimeModel();

        $this->model_history = new HistoryModel();

        $this->model_salary = Db::name('Salary');
    }




    /**

     * @url userInfo

     */

    public function getUserInfo(){

        if(request()->isPost()){

            $this->result($this->model_account->getUserDetail($this->uid),$code=1,$msg = 'success', $type = 'json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url editUser

     */

    public function editUserInfo(){

        if(request()->isPost()){

            $username = request()->param('username');

           /* if(empty($username))

                $this->result('',$code=0,$msg = '请输入昵称',$type='json');*/

            mb_strlen($username,'utf8')>10 && $this->result('',$code=0,$msg = '昵称不能超过10个字',$type='json');

            $sex = request()->param('sex',1,'intval');

           /* empty($sex) && $this->result('',$code=0,$msg = '请选择性别',$type='json');*/

            $age = request()->param('age',18,'intval');

            if($age<13 && $age>60)

                $this->result('',$code=0,$msg = '年龄不符合',$type='json');

            $phone = request()->param('phone',null);

            !cmf_check_mobile($phone) && $this->result('',$code=0,$msg='电话号码不正确',$type='json');

            $school = request()->param('school','无','htmlentities');

            /*empty($school) && $this->result('',$code=0,$msg = '请填写院校',$type='json');*/

            mb_strlen($school,'utf8')>20 && $this->result('',$code=0,$msg='院校不超过20个字',$type='json');

            $type =request()->param('type',1,'intval');

            $type = ($type == 1)?1:2;

            $id_card = request()->param('id_card');

            empty($id_card)  && $this->result('',$code=0,$msg='请填写身份证',$type='json');

            !checkIdCard($id_card) && $this->result('',$code=0,$msg='身份证不正确',$type='json');

            try{

                $this->model_account->save(['username'=>$username,'sex'=>$sex,'age'=>$age,'phone'=>$phone,'school'=>$school,'type'=>$type,'id_card'=>$id_card],['id'=>$this->uid]);

            }catch(Exception $e){

                $this->result('',$code=0,$msg = $e->getMessage(),$type='json');

            }



            $this->result('',$code=1,$msg = '操作成功',$type='json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url changeAvatar

     */

    public function uploadAvatar(){

        if(request()->isPost()){

            $file = request()->file('image',null);

            if(empty($file))

                $this->result('',$code=0,$msg = '请上传图片',$type='json');

            $http_type = (request()->isSsl())?'https://':'http://';

            $cdn_path = $http_type.$_SERVER['HTTP_HOST'].DS.'upload';

            $upload_path = ROOT_PATH.'public'.DS.'upload';

            $final_upload_path = $upload_path.DS.'account'.DS.$this->uid;

            $final_url_root = $cdn_path.DS.'account'.DS.$this->uid;

            $info = $file->validate(['size'=>1024*1024*3,'ext'=>'jpg,jpeg,bmp,png,gif'])->move($final_upload_path);

            $return = [];

            if($info){

                $return = [

                    'ext' => $info->getExtension(),

                    'url' => $final_url_root.DS.$info->getSaveName(),

                    'size' => $info->getSize(),

                ];

            } else {

                $this->result('',$code=0,$msg = $file->getError(),$type='json');

            }

            try{

                $this->model_account->save(['avatar'=>$return['url']],['id'=>$this->uid]);

            }catch(Exception $e){

                $this->result('',$code=0,$msg = $e->getMessage(),$type='json');

            }

            $this->result($return,$code=1,$msg = '上传成功',$type='json');



        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url idea

     */

    public function addUserIdea(){

        if(request()->isPost()){

            $content = request()->param('content','','htmlentities');

            empty($content) && $this->result('',$code=0,$msg = '缺少参数',$type='json');

            mb_strlen($content,'utf8')>200 &&  $this->result('',$code=0,$msg = '内容大于200个字符',$type='json');

            try{

                $this->model_idea->save([

                    'uid'=>$this->uid,

                    'create_time'=>time(),

                    'content'=>$content,

                ]);

            }catch(Exception $e){

                $this->result('',$code=0,$msg = $e->getMessage(),$type='json');

            }

            $this->result('',$code=1,$msg = '反馈成功',$type='json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url joinUs

     */

    public function joinUs(){

        if(request()->isPost()) {

            $name = request()->param('name','','htmlentities');

            empty($name) &&  $this->result('',$code=0,$msg = '请填写姓名',$type='json');

            mb_strlen($name,'utf8')>10  &&  $this->result('',$code=0,$msg = '姓名不能大于10个字符',$type='json');

            $mobile = request()->param('mobile',null);

            !cmf_check_mobile($mobile) && $this->result('',$code=0,$msg = '联系方式不正确',$type='json');

            $auth = request()->param('auth',null,'htmlentities');

            empty($auth) && $this->result('',$code=0,$msg = '请填写姓名',$type='json');

            mb_strlen($auth,'utf8')>30 && $this->result('',$code=0,$msg = '身份不能大于30个字符',$type='json');

            $content = request()->param('content','','htmlentities');

            empty($content) && $this->result('',$code=0,$msg = '请添加留言',$type='json');

            try{

                $this->model_join->save([

                    'name'=>$name,

                    'mobile'=>$mobile,

                    'auth'=>$auth,

                    'content'=>$content

                ]);

            }catch(Exception $e){

                $this->result('',$code=0,$msg = $e->getMessage(),$type='json');

            }

            $this->result('',$code=1,$msg = '操作成功',$type='json');



        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url follow

     */

    public function followWork(){

        if(request()->isPost()){

            $account_id = $this->uid;

            $work_id = request()->param('id',0,'intval');

            empty($work_id) && $this->result('',$code=0,$msg = '工作参数为空',$type='json');

            $follow = $this->model_follow->get(['account_id'=>$account_id,'work_id'=>$work_id]);

            try{

                if(empty($follow)){

                    $this->model_follow->save(['account_id'=>$account_id,'work_id'=>$work_id]);

                }else{

                    $this->model_follow->where(['account_id'=>$account_id,'work_id'=>$work_id])->delete();

                }

            }catch(Exception $e){

                $this->result('',$code=0,$msg = $e->getMessage(),$type='json');

            }

            $this->result('',$code=1,$msg='操作成功',$type='json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url followList

     */

    public function getFollowList(){

        if(request()->isPost()){

            $page = request()->param('page',1,'intval');

            $limit = request()->param('limit',6,'intval');

            $this->result($this->model_follow->getFollowList($page,$limit,$this->uid),$code=1,$msg='',$type='json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url send

     */

    public function sendWork(){

        if(request()->isPost()){

            $account_id = $this->uid;

            $work_id = request()->param('id',0,'intval');

            empty($work_id) && $this->result('',$code=0,$msg = '工作参数为空',$type='json');

            $follow = $this->model_send->get(['account_id'=>$account_id,'work_id'=>$work_id]);

            $follow && $this->result('',$code=0,$msg = '已报名',$type='json');

            $top = request()->param('top',1,'intval');
            $auth = $this->model_account->where('id',$this->uid)->value('type');
            if($top!=$auth && $top == 1)

                $this->result('',$code=0,$msg = '您的身份为社会工，请进入社会工通道找工作。如有错误，请去我的->身份里修改。',$type='json');
            if($top!=$auth && $top == 2)
                $this->result('',$code=0,$msg = '您的身份为学生工，请进入学生工通道找工作。如有错误，请去我的->身份里修改。',$type='json');
            $user = $this->model_account->get($this->uid);
            if(empty($user->phone) || empty($user->id_card))
                $this->result('',$code=2,$msg = '请完善个人信息(电话，身份证)',$type='json');

            try{

                $this->model_send->save([

                    'account_id'=>$account_id,

                    'work_id'=>$work_id,

                    'create_time'=>time()

                ]);

            }catch(Exception $e){

                $this->result('',$code=0,$msg = $e->getMessage(),$type='json');

            }

            $this->result('',$code=1,$msg='报名成功',$type='json');



        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url sendList

     */

    public function sendWorkList(){

        if(request()->isPost()){

            $page = request()->param('page',1,'intval');

            $limit = request()->param('limit',6,'intval');

            $this->result($this->model_send->sendList($page,$limit,$this->uid),$code=1,'',$type='json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }


    public function getNewMessage(){
        if(request()->isPost()){
            $this->result($this->model_send->newSend($this->uid),$code=1,'',$type='json');
        }
        $this->result('',$code=0,$msg = '非法操作',$type='json');
    }


    /**

     * @url sendInfo

     */

    public function sendWorkInfo(){

        if(request()->isPost()){

            $id = request()->param('id',0,'intval');

            $this->result($this->model_send->sendInfo($id),$code=1,'',$type='json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url search

     */

    public function searchWork(){

        if(request()->isPost()){

            $word = request()->param('word',null,'htmlentities');

            empty($word) && $this->result('',$code=0,$msg='请输入搜索内容',$type='json');

            if(mb_strlen($word,'utf8')>20)

                $this->result('',$code=0,$msg='内容不能超过20个字',$type='json');

            $page = request()->param('page',1,'intval');

            $limit = request()->param('limit',6,'intval');

            $this->result($this->model_work->searchWork($word,$page,$limit,$this->uid),$code=1,$msg='',$type='json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url searchHistory

     */

    public function searchHistory(){

        if(request()->isPost()){

            $list =  $this->model_search->where('account_id',$this->uid)->field('content,id')->limit(10)->select();

            $this->result($list,$code=1,$msg='',$type='json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url clearSearch

     */

    public function clearSearch(){

        if(request()->isPost()){

            $where['account_id'] = $this->uid;

            $id = request()->param('id',0,'intval');

            if(!empty($id))

                $where['id'] = $id;

            try{

                $this->model_search->where($where)->delete();

            }catch(Exception $e){

                $this->result('',$code=0,$msg = '非法操作',$type='json');

            }

            $this->result('',$code=1,$msg='操作成功',$type='json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }





    /**

     * @url clock

     */

    public function getClock(){

        if(request()->isPost()){

            $id = request()->param('id',0,'intval');
            $year = request()->param('year');
            $month = request()->param('month');
            $day = request()->param('day');

            $duration = request()->param('duration',0,'intval');

            if($duration >= 24 || $duration <= 0)

                $this->result('',$code=0,$msg = '请输入正确的工时',$type='json');
            if(empty($id)){
                $time = time();
                if (empty($year)){
                $year = (int) date('Y',$time); //年
                $month = (int) date('m',$time); //月
                $day = (int) date('d',$time); //日
                }
                $week = (int) date('w',$time);
                $is_week = 0;
                if($week == 0 || $week == 6)
                    $is_week = 1;
                $has = $this->model_time->get(['account_id'=>$this->uid,'year'=>$year,'month'=>$month,'day'=>$day,'is_week'=>$is_week]);
                if(!empty($has))
                    $this->result('',$code=0,$msg = '今天已计时',$type='json');
            }
            try{
                if(empty($id)){
                    $this->model_time->save(['account_id'=>$this->uid,'create_time'=>$time,'year'=>$year,'month'=>$month,'day'=>$day,'duration'=>$duration]);
                }else{
                    $this->model_time->save(['duration'=>$duration],['id'=>$id]);
                }
            }catch(Exception $e){

                $this->result('',$code=0,$e->getMessage(),$type='json');

            }

            $this->result('',$code=1,'计时成功',$type='json');



        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url clockList

     */

    public function getClockList(){

        if(request()->isPost()){

            $year = request()->param('year',2019,'intval');

            $month = request()->param('month',6,'intval');

            $this->result($this->model_time->clockList($year,$month,$this->uid),$code=1,'计时成功',$type='json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url likeList

     */

    public function getLikeList(){

        if(request()->isPost()){

            $page = request()->param('page',1,'intval');

            $limit = request()->param('limit',6,'intval');

            $this->result($this->model_history->likeList($page,$limit,$this->uid),$code=1,'',$type='json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url job

     */

    public function getJobInfo(){

        if(request()->isPost()){

            $page = request()->param('page',1,'intval');

            $limit = request()->param('limit',6,'intval');

            $this->result($this->model_history->JobList($page,$limit,$this->uid),$code=1,'',$type='json');

        }

        $this->result('',$code=0,$msg = '非法操作',$type='json');

    }



    /**

     * @url dealSend

     */

    public function dealAllSend(){

        if(request()->isPost()){

            $id = request()->param('id',0,'intval');

            $step = request()->param('step',4,'intval');

            $where = array();

            if($id)

                $where['id'] = $id;

            $this->model_send->save(['step'=>$step],[

                'account_id'=>$this->uid,

                'step'=>3

            ]+$where);

            $this->result('',$code=1,'操作成功',$type='json');

        }else{

            $this->result('',$code=0,$msg = '非法操作',$type='json');

        }

    }


    public function getSalaryList(){
        if(empty($this->id_card)){
            $data = array();
        }else{
            $data = $this->model_salary->where('id_card',$this->id_card)->field('salary_id,month,year,salary,work_time')->select();
        }

        $this->result($data,$code=1,'',$type='json');
    }

    public function getSalary(){
        $salary_id = request()->param('salary_id',0,'intval');
        $data = $this->model_salary->where('salary_id',$salary_id)->field('id,name,come_time,base_attendance,really_attendance,base_salary,really_work_salary,day_work_time,day_work_salary,week_work_time,week_work_salary,other_add,salary,work_time,year,month')->find();
        $this->result($data,$code=1,'',$type='json');

    }





}